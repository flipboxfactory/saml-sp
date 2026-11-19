<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\sp\controllers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\saml\core\controllers\messages\AbstractController;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\sp\events\RelayState;
use flipbox\saml\sp\helpers\SafeRedirect;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\traits\SamlPluginEnsured;
use flipbox\saml\sp\validators\InResponseTo;
use flipbox\saml\sp\validators\Response as ResponseValidator;
use SAML2\AuthnRequest;
use SAML2\Response as SamlResponse;
use yii\base\Event;
use yii\web\HttpException;

class LoginController extends AbstractController
{
    use SamlPluginEnsured;

    /**
     * Happens before the RelayState is used to redirect the user to where they were
     * initially trying to go.
     */
    public const EVENT_BEFORE_RELAYSTATE_REDIRECT = 'eventBeforeRelayStateRedirect';
    /**
     * Happens after the RelayState is created and before the AuthNRequest
     * is sent off to the IdP. Use this event if you want to modify the
     * RelyState before it's sent to the IdP.
     */
    public const EVENT_AFTER_RELAYSTATE_CREATION = 'eventBeforeRelayStateCreation';

    /**
     * Marks a login request already redirected to the ACS host, so it isn't redirected again.
     */
    public const ACS_HOST_PARAM = 'samlAcsHost';

    protected array|int|bool $allowAnonymous = [
        'actionIndex',
        'actionRequest',
    ];

    public $enableCsrfValidation = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action): bool
    {
        if ($action->actionMethod === 'actionIndex' || $action->actionMethod === 'actionRequest') {
            return true;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return \yii\web\Response
     * @throws HttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \flipbox\saml\core\exceptions\InvalidMessage
     * @throws \yii\base\Exception
     * @throws \yii\base\UserException
     */
    public function actionIndex($uid = null)
    {

        /** @var SamlResponse $response */
        $response = Factory::receive();

        /** @var $identityProvider AbstractProvider */
        if (!$identityProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($response->getIssuer())
        )->andWhere([
            'enabled' => 1,
        ])->one()) {
            $this->throwIdpNotFoundWithResponse($response);
        }

        // get the plugin settings
        $settings = Saml::getInstance()->getSettings();

        $condition = [
            'enabled' => 1,
        ];

        if ($uid) {
            $condition['uid'] = $uid;
        } else {
            $condition['entityId'] = $settings->getEntityId();
        }

        /** @var $serviceProvider AbstractProvider */
        if (!$serviceProvider = Saml::getInstance()->getProvider()->findBySp($condition)->one()) {
            $this->throwSpNotFound();
        }

        $validator = new ResponseValidator(
            $identityProvider,
            $serviceProvider,
            $settings->requireResponseToBeSigned,
            $settings->requireAssertionToBeSigned,
            new InResponseTo((bool)$settings->allowIdpInitiatedLogin)
        );

        $result = $validator->validate($response);
        if (count($result->getErrors()) > 0) {
            throw new \Exception("Errors during validation: " . implode($result->getErrors()));
        }

        // Transform to User START!
        Saml::getInstance()->getLogin()->transformToUser(
            $user = Saml::getInstance()->getUser()->getByResponse(
                $response,
                $serviceProvider,
                $identityProvider,
                $settings
            ),
            $response,
            $identityProvider,
            $serviceProvider,
            $settings
        );
        // Transform to User END!

        // User Group Start
        Saml::getInstance()->getUserGroups()->sync(
            $user,
            $response,
            $serviceProvider,
            $settings
        );
        // User Group End

        // Identity START
        $identity = Saml::getInstance()->getProviderIdentity()->getByUserAndResponse(
            $user,
            $response,
            $serviceProvider,
            $identityProvider
        );

        // LOGIN
        Saml::getInstance()->getLogin()->byIdentity($identity);
        // Identity END

        // RelayState is an unsigned form field, so only follow it to a same-site URL
        $relayState = $response->getRelayState() ?: \Craft::$app->request->getParam('RelayState');
        $redirect = is_string($relayState) && $relayState !== '' ? $relayState : null;
        if ($redirect !== null && $settings->encodeRelayState) {
            $redirect = base64_decode($redirect, true) ?: null;
        }

        $safeRedirect = SafeRedirect::sameSite($redirect);
        if ($redirect !== null && $safeRedirect === null) {
            Saml::warning('Ignoring RelayState that is not a same-site URL.');
        }
        $redirect = $safeRedirect
            ?? SafeRedirect::sameSite(\Craft::$app->getUser()->getReturnUrl())
            ?? \Craft::$app->getUser()->getDefaultReturnUrl();

        Event::trigger(
            self::class,
            self::EVENT_BEFORE_RELAYSTATE_REDIRECT,
            $event = new RelayState([
                'idp' => $identityProvider,
                'sp' => $serviceProvider,
                'relayState' => $relayState,
                'redirect' => $redirect,
            ])
        );

        Craft::$app->user->removeReturnUrl();
        return $this->redirect($event->redirect);
    }

    /**
     * The IdP posts the Response to the ACS URL, and InResponseTo only accepts it from the session that sent
     * the AuthnRequest. Session cookies are per host, so a login started on another host (a separate control
     * panel domain, www vs. bare domain) comes back with a different session, or none. Start it on the ACS
     * host instead, carrying the return URL along since this host's session doesn't follow.
     *
     * Redirects once: if the request still isn't on the ACS host (e.g. a proxy hides the real host), the
     * login continues here and InResponseTo checks the Response as usual.
     */
    protected function redirectToAcsHost(AbstractProvider $sp): ?\yii\web\Response
    {
        $request = Craft::$app->getRequest();
        $acsOrigin = static::origin($sp->firstSpAcsService()?->getLocation());
        if (!$request->getIsGet() || $acsOrigin === null || static::origin($request->getHostInfo()) === $acsOrigin) {
            return null;
        }

        if ($request->getQueryParam(self::ACS_HOST_PARAM)) {
            Saml::warning(sprintf(
                'Starting the login on %s, not the ACS host %s; the Response may be rejected.',
                (string)$request->getHostInfo(),
                $acsOrigin
            ));
            return null;
        }

        $relayStateParam = Saml::getInstance()->getSettings()->relayStateOverrideParam;
        $params = $request->getQueryParams();
        $params[$relayStateParam] = Craft::$app->getUser()->getReturnUrl($request->getQueryParam($relayStateParam));
        $params[self::ACS_HOST_PARAM] = 1;

        return $this->redirect(UrlHelper::urlWithParams(
            $acsOrigin . (parse_url($request->getUrl(), PHP_URL_PATH) ?: '/'),
            $params
        ));
    }

    /**
     * @return string|null scheme://host[:port], lowercased, without a default port
     */
    private static function origin(?string $url): ?string
    {
        $parts = $url ? parse_url($url) : false;
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        $port = $parts['port'] ?? null;
        $defaultPort = ['http' => 80, 'https' => 443][$scheme] ?? null;

        return $scheme . '://' . strtolower($parts['host']) . ($port && $port !== $defaultPort ? ':' . $port : '');
    }

    /**
     * @param SamlResponse $response
     * @throws HttpException
     */
    protected function throwIdpNotFoundWithResponse(SamlResponse $response)
    {
        throw new HttpException(
            400,
            sprintf(
                'Identity Provider is not found. Possibly a configuration problem. Issuer/EntityId: %s',
                MessageHelper::getIssuer($response->getIssuer()) ?: 'IS NULL'
            )
        );
    }

    /**
     * @param SamlResponse $response
     * @throws HttpException
     */
    protected function throwIdpNotFoundWithUid($uid)
    {
        throw new HttpException(
            400,
            sprintf(
                'Identity Provider is not found with UID: %s',
                $uid
            )
        );
    }

    /**
     * @throws HttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function throwSpNotFound()
    {
        throw new HttpException(
            400,
            sprintf(
                'Service Provider is not found. Possibly a configuration problem. My Provider/Current EntityId: %s',
                Saml::getInstance()->getSettings()->getEntityId() ?: 'IS NULL'
            )
        );
    }


    /**
     * @param string|null $externalUid IdP UID
     * @param string|null $internalUid SP UID
     * @throws HttpException
     * @throws InvalidMetadata
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRequest(string $externalUid = null, string $internalUid = null)
    {
        //build uid condition
        $uidCondition = [];
        if ($externalUid) {
            $uidCondition = [
                'uid' => $externalUid,
            ];
        }

        /**
         * @var ProviderRecord $idp
         */
        if (!$idp = Saml::getInstance()->getProvider()->findByIdp(
            $uidCondition
        )->one()
        ) {
            $this->throwIdpNotFoundWithUid($externalUid);
        }

        $sp = Saml::getInstance()->getProvider()->findBySp([
                'uid' => $internalUid,
                ])->one()
                ??
                Saml::getInstance()->getProvider()->findOwn();

        if (!$sp) {
            $this->throwSpNotFound();
        }

        if ($redirect = $this->redirectToAcsHost($sp)) {
            return $redirect;
        }

        /**
         * @var $authnRequest AuthnRequest
         */
        $authnRequest = Saml::getInstance()->getAuthnRequest()->create(
            $sp,
            $idp
        );

        /**
         * Extra layer of security, save the id and check it on the return.
         */
        Saml::getInstance()->getSession()->setRequestId(
            $authnRequest->getID()
        );
        InResponseTo::remember($authnRequest->getID());


        // Grab the return URL, or use a param sent as a default
        // TODO - seems like if there's a parameter sent, that should be used as an override.
        $relayState = Craft::$app->getUser()->getReturnUrl(
        // Is RelayState set on this request?
        // You can override the param within settings
            Craft::$app->request->getParam(
                Saml::getInstance()->getSettings()->relayStateOverrideParam,
                null
            )
        );
        if (Saml::getInstance()->getSettings()->encodeRelayState) {
            $relayState = base64_encode($relayState);
        }

        Event::trigger(
            self::class,
            self::EVENT_AFTER_RELAYSTATE_CREATION,
            $event = new RelayState([
                'idp' => $idp,
                'sp' => $sp,
                'relayState' => $relayState,
            ])
        );

        $authnRequest->setRelayState(
            $event->relayState
        );

        Factory::send(
            $authnRequest,
            $idp
        );

        Craft::$app->end();
    }
}
