<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:23 AM
 */

namespace flipbox\saml\sp\services;


use craft\base\Component;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\traits\Security;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class AuthnRequest
 * @package flipbox\saml\sp\services
 */
class AuthnRequest extends Component
{

    use Security;

    const REQUEST_SESSION_KEY = 'authnrequest.requestId';

    public function getKey(): XMLSecurityKey
    {

        return \LightSaml\Credential\KeyHelper::createPrivateKey(
            Saml::getInstance()->getSettings()->keyPath,
            '',
            true
        );
    }

    public function getCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(
            Saml::getInstance()->getSettings()->certPath
        );
    }

    public function create(string $entityId = null)
    {
        if (! $entityId) {

            /**
             * @var \flipbox\saml\sp\models\Provider $provider
             */
            if (! $provider = Saml::getInstance()->getProvider()->findDefaultProvider()) {
                return null;
            }

        }else{
            $provider = Saml::getInstance()->getProvider()->findByString($entityId);
        }

        $location = $provider->getMetadata()->getFirstIdpSsoDescriptor()->getFirstSingleSignOnService()->getLocation();

        /**
         * @var $samlSettings Settings
         */
        $samlSettings = Saml::getInstance()->getSettings();
        $authnRequest = new \LightSaml\Model\Protocol\AuthnRequest();

        $authnRequest->setAssertionConsumerServiceURL(
            Metadata::getLoginLocation()
        )->setProtocolBinding(
            $provider->getMetadata()->getFirstIdpSsoDescriptor()->getFirstSingleSignOnService()->getBinding()
        )->setID(Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setDestination($location)
            ->setIssuer(new Issuer($samlSettings->getEntityId()));

        //set signed assertions
        if ($samlSettings->signAssertions) {
            $this->signMessage($authnRequest);
        }

        return $authnRequest;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     */
    public function saveToSession(\LightSaml\Model\Protocol\AuthnRequest $authnRequest)
    {
        \Craft::$app->getSession()->set(static::REQUEST_SESSION_KEY , $authnRequest->getID());
    }

    /**
     * Requires $this->saveToSession called before request is sent
     * @param \LightSaml\Model\Protocol\Response $response
     * @return bool
     */
    public function isResponseValidWithSession(\LightSaml\Model\Protocol\Response $response)
    {
        return $response->getInResponseTo() === \Craft::$app->getSession()->get(static::REQUEST_SESSION_KEY);
    }
}