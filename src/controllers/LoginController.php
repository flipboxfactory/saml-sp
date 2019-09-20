<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\sp\controllers;

use Craft;
use flipbox\saml\core\controllers\messages\AbstractController;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\core\validators\Response as ResponseValidator;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\traits\SamlPluginEnsured;
use SAML2\AuthnRequest;
use SAML2\Response as SamlResponse;
use yii\web\HttpException;

class LoginController extends AbstractController
{
    use SamlPluginEnsured;

    protected $allowAnonymous = [
        'actionIndex',
        'actionRequest',
    ];

    public $enableCsrfValidation = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
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
    public function actionIndex()
    {

        /** @var SamlResponse $response */
        $response = Factory::receive();

        if (! $identityProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($response->getIssuer())
        )->one()) {
            $this->throwIdpNotFoundWithResponse($response);
        }

        if (! $serviceProvider = Saml::getInstance()->getProvider()->findOwn()) {
            $this->throwSpNotFound();
        }

        $validator = new ResponseValidator(
            $identityProvider,
            $serviceProvider
        );

        $validator->validate($response);

        // LOGIN!
        // TODO - break this up to multiple calls and pass needed idp and sp to the methods
        Saml::getInstance()->getLogin()->login(
            $response
        );

        //get relay state but don't error!
        $relayState = $response->getRelayState() ?: \Craft::$app->request->getParam('RelayState');
        try {
            $redirect = base64_decode($relayState);
            Saml::info('RelayState: ' . $redirect);
        } catch (\Exception $e) {
            $redirect = \Craft::$app->getUser()->getReturnUrl();
        }

        Craft::$app->user->removeReturnUrl();
        return $this->redirect($redirect);
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
     * @param null $uid
     * @throws HttpException
     * @throws InvalidMetadata
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRequest($uid = null)
    {
        //build uid condition
        $uidCondition = [];
        if ($uid) {
            $uidCondition = [
                'uid' => $uid,
            ];
        }

        /**
         * @var ProviderRecord $idp
         */
        if (! $idp = Saml::getInstance()->getProvider()->findByIdp(
            $uidCondition
        )->one()
        ) {
            $this->throwIdpNotFoundWithUid($uid);
        }

        if (! $sp = Saml::getInstance()->getProvider()->findOwn()) {
            $this->throwSpNotFound();
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

        $authnRequest->setRelayState(
            SerializeHelper::toBase64(
                Craft::$app->getUser()->getReturnUrl(
                    Craft::$app->request->getParam(
                        Saml::getInstance()->getSettings()->relayStateOverrideParam,
                        null
                    )
                )
            )
        );


        Factory::send(
            $authnRequest,
            $idp
        );

        Craft::$app->end();
    }
}
