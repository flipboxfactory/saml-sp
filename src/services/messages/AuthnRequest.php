<?php

namespace flipbox\saml\sp\services\messages;

use craft\base\Component;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Constants;
use SAML2\XML\md\EndpointType;
use SAML2\XML\saml\Issuer;
use yii\base\Event;

class AuthnRequest extends Component
{
    public const EVENT_AFTER_MESSAGE_CREATED = 'eventAfterMessageCreated';

    /**
     * @param AbstractProvider $identityProvider
     * @return \SAML2\XML\md\IndexedEndpointType|null
     * @throws InvalidMetadata
     */
    private function firstIdpSsoService(AbstractProvider $identityProvider): EndpointType
    {
        if (!($service = $identityProvider->firstIdpSsoService(Constants::BINDING_HTTP_POST))) {
            $service = $identityProvider->firstIdpSsoService();
        }

        if (!$service) {
            throw new InvalidMetadata("IdP Metadata is missing SSO Service");
        }

        return $service;
    }

    /**
     * @param AbstractProvider $serviceProvider
     * @param AbstractProvider $identityProvider
     * @return \SAML2\AuthnRequest
     * @throws \craft\errors\SiteNotFoundException
     */
    public function create(
        ProviderRecord $serviceProvider,
        AbstractProvider $identityProvider,
    ): SamlAuthnRequest {
        $idpSsoService = $this->firstIdpSsoService($identityProvider);

        $location = $idpSsoService->getLocation();

        /**
         * @var $samlSettings Settings
         */
        $samlSettings = Saml::getInstance()->getSettings();

        $authnRequest = new \SAML2\AuthnRequest();

        $authnRequest->setAssertionConsumerServiceURL(
            $serviceProvider->getLoginEndpoint()
        );

        $authnRequest->setProtocolBinding(
            $idpSsoService->getBinding()
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
            $issuer = new Issuer()
        );

        $issuer->setValue(
            $serviceProvider->getEntityId()
        );

        /**
         * @var KeyChainRecord $pair
         */
        $pair = $serviceProvider->keychain;

        if ($pair && $samlSettings->signAuthnRequest) {
            $authnRequest->setSignatureKey(
                $serviceProvider->keychainPrivateXmlSecurityKey()
            );
        }

        /**
         * Kick off event here so people can manipulate this object if needed
         */
        $event = new \flipbox\saml\sp\events\AuthnRequest();
        $event->message = $authnRequest;
        $this->trigger(static::EVENT_AFTER_MESSAGE_CREATED, $event);

        return $authnRequest;
    }
}
