<?php

namespace flipbox\saml\sp\services\messages;

use craft\base\Component;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Constants;
use yii\base\Event;

class AuthnRequest extends Component
{

    const EVENT_AFTER_MESSAGE_CREATED = 'eventAfterMessageCreated';

    /**
     * @param AbstractProvider $myServiceProvider
     * @param AbstractProvider $identityProvider
     * @return \SAML2\AuthnRequest
     * @throws \craft\errors\SiteNotFoundException
     */
    public function create(
        AbstractProvider $myServiceProvider,
        AbstractProvider $identityProvider
    ): SamlAuthnRequest {

        $location = $identityProvider->firstIdpSsoService(
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

        $authnRequest->setAssertionConsumerServiceURL(
            $myServiceProvider->firstSpAcsService(Constants::BINDING_HTTP_POST)->getLocation()
        );

        $authnRequest->setAssertionConsumerServiceIndex(
            "0"
        );

        $authnRequest->setAssertionConsumerServiceURL(
            $samlSettings->getDefaultLoginEndpoint()
        );

        $authnRequest->setProtocolBinding(

            $identityProvider->firstIdpSsoService(
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
         * @var KeyChainRecord $pair
         */
        $pair = $myServiceProvider->keychain;

        if ($pair && $samlSettings->signAuthnRequest) {
            $authnRequest->setSignatureKey(
                $myServiceProvider->keychainPrivateXmlSecurityKey()
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
