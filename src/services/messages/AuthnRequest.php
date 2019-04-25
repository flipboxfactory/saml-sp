<?php

namespace flipbox\saml\sp\services\messages;

use craft\base\Component;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\services\messages\SamlRequestInterface;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\HTTPRedirect;
use yii\base\Event;

class AuthnRequest extends Component
{

    const EVENT_AFTER_MESSAGE_CREATED = 'eventAfterMessageCreated';

    /**
     * @inheritdoc
     */
    public function create(AbstractProvider $provider)
    {

        $location = $provider->getFirstIdpSsoService(
        /**
         * @todo support http redirect
         */
            Constants::BINDING_HTTP_POST
        )->getLocation();

        /**
         * @var $samlSettings Settings
         */
        $samlSettings = Saml::getInstance()->getSettings();

        $authnRequest = new \SAML2\AuthnRequest();

        $authnRequest->setAssertionConsumerServiceIndex(1);

        $authnRequest->setAssertionConsumerServiceURL(
            $samlSettings->getDefaultLoginEndpoint()
        );

        $authnRequest->setProtocolBinding(

            $provider->getFirstIdpSsoService(
            /**
             * @todo support http redirect
             */
                Constants::BINDING_HTTP_POST
            )->getBinding()
        );

        $authnRequest->setId($requestId = MessageHelper::generateId());

        $authnRequest->setIssueInstant(
            (new \DateTime())->getTimestamp()
        );

        $authnRequest->setDestination(
            $location
        );

        $authnRequest->setRelayState(
            \Craft::$app->getUser()->getReturnUrl()
        );

        $authnRequest->setIssuer(
            Saml::getInstance()->getSettings()->getEntityId()
        );

        /**
         * @var ProviderRecord $thisSp
         */
        $thisSp = Saml::getInstance()->getProvider()->findByEntityId(
            Saml::getInstance()->getSettings()->getEntityId()
        )->one();

        /**
         * @var KeyChainRecord $pair
         */
        $pair = $thisSp->keychain;

        if ($pair && $samlSettings->signAuthnRequest) {

            $authnRequest->setSignatureKey(
                $thisSp->getPrivateXmlSecurityKey()
            );
        }

        /**
         * Kick off event here so people can manipulate this object if needed
         */
        $event = new Event();
        $event->data = $authnRequest;
        $this->trigger(static::EVENT_AFTER_MESSAGE_CREATED, $event);

        return $authnRequest;
    }
}
