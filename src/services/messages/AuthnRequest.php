<?php

namespace flipbox\saml\sp\services\messages;

use craft\base\Component;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\services\messages\SamlRequestInterface;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\core\services\traits\Security;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use yii\base\Event;

class AuthnRequest extends Component implements SamlRequestInterface
{

    const EVENT_AFTER_MESSAGE_CREATED = 'eventAfterMessageCreated';

    /**
     * @inheritdoc
     */
    public function create(ProviderInterface $provider, array $config = []): AbstractRequest
    {
        $location = $provider->getMetadataModel()->getFirstIdpSsoDescriptor()->getFirstSingleSignOnService(
            /**
            * Just doing post for now
            */
            SamlConstants::BINDING_SAML2_HTTP_POST
        )->getLocation();

        /**
         * @var $samlSettings Settings
         */
        $samlSettings = Saml::getInstance()->getSettings();
        $authnRequest = new \LightSaml\Model\Protocol\AuthnRequest();

        $authnRequest->setAssertionConsumerServiceURL(
            Metadata::getLoginLocation()
        )->setProtocolBinding(
            $provider->getMetadataModel()->getFirstIdpSsoDescriptor()->
            getFirstSingleSignOnService(
                /**
                 * Just going to hard code this for now.
                 * Post binding is really the only thing most
                 * people support so we are defaulting to this.
                 */
                SamlConstants::BINDING_SAML2_HTTP_POST
            )->getBinding()
        )->setID($requestId = Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setDestination($location)
            ->setRelayState(\Craft::$app->getUser()->getReturnUrl())
            ->setIssuer(new Issuer($samlSettings->getEntityId()));

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
            SecurityHelper::signMessage($authnRequest, $pair);
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
