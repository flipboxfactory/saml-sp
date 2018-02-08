<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\sp\controllers;


use craft\web\Controller;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;
use Craft;
use flipbox\saml\core\helpers\SerializeHelper;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use yii\web\HttpException;

class LoginController extends Controller
{

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

        $response = Saml::getInstance()->getResponse()->parseByRequest(Craft::$app->request);
        if (! Saml::getInstance()->getAuthnRequest()->isResponseValidWithSession($response)) {
            throw new HttpException(400, "Invalid request");
        }

        Saml::getInstance()->getLogin()->login($response);

        //get relay state but don't error!
        $relayState = \Craft::$app->request->getQueryParam('RelayState') ?: \Craft::$app->request->getBodyParam('RelayState');
        try{
           $redirect = base64_decode($relayState);
        }catch(\Exception $e){
           $redirect = \Craft::$app->getUser()->getReturnUrl();
        }

        return $this->redirect($redirect);
    }


    /**
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionRequest()
    {

        /**
         * @var $authnRequest AuthnRequest
         */
        $authnRequest = Saml::getInstance()->getAuthnRequest()->create();
        /**
         * Extra layer of security, save the id and check it on the return.
         */
        Saml::getInstance()->getAuthnRequest()->saveToSession($authnRequest);

        $authnRequest->setRelayState(
            SerializeHelper::toBase64(Craft::$app->getUser()->getReturnUrl())
        );

        if ($authnRequest->getProtocolBinding() === SamlConstants::BINDING_SAML2_HTTP_REDIRECT) {
            Saml::getInstance()->getHttpRedirect()->send()
            $parameters['SAMLRequest'] = SerializeHelper::base64Message(
                $authnRequest,
                true
            );
            if ($signature = $authnRequest->getSignature()) {
                $authnRequest->setSignature(null);
                $dest = SerializeHelper::redirectUrl($authnRequest->getDestination(), SerializeHelper::addSignatureToUrl($parameters, $signature));
            } else {
                $dest = SerializeHelper::redirectUrl($authnRequest->getDestination(), $parameters);
            }

            return $this->redirect($dest);
        }
        //else POST Binding
        $parameters['SAMLRequest'] = SerializeHelper::base64Message(
            $authnRequest
        );

        $parameters['destination'] = $authnRequest->getDestination();

        $view = Craft::$app->getView();
        $mode = $view->getTemplateMode();

        // Switch to admin
        $view->setTemplateMode($view::TEMPLATE_MODE_CP);

        $response = $this->renderTemplate('saml-sp/_components/post-binding-submit.twig', $parameters);

        // Revert mode
        $view->setTemplateMode($mode);


        return $response;

    }

}