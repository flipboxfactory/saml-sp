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
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\services\bindings\Factory;
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
        if ($action->actionMethod === 'actionIndex') {
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

        if (Saml::getInstance()->getSession()->getRequestId() !== $response->getInResponseTo()) {
            throw new HttpException(400, "Invalid request");
        }

        // Validate
        // Move to service
        /**
         * Really don't know how we'd get here but just shutting things down now.
         * If you fail login at the idp I'd hope they'd just make you continue to try on their end
         * but just incase.
         *
         * In this case, you may want to have a good custom 403 error page to reach out to someone
         * to figure out why the person is having issues logging in.
         */
        if (! $response->getStatus() || ! $response->isSuccess()) {
            throw new HttpException(403, "Login failed!");
        }

        // Login
        Saml::getInstance()->getLogin()->login($response);

        //get relay state but don't error!
        $relayState = \Craft::$app->request->getParam('RelayState');
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
     * @throws InvalidMetadata
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
            throw new InvalidMetadata('IDP Metadata Not found!');
        }

        /**
         * @var $authnRequest AuthnRequest
         */
        $authnRequest = Saml::getInstance()->getAuthnRequest()->create($idp);

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
