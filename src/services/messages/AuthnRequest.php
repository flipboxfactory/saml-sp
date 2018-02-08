<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:23 AM
 */

namespace flipbox\saml\sp\services\messages;


use craft\base\Component;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;
use flipbox\saml\core\services\traits\Security;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class AuthnRequest extends Component
{

    const REQUEST_SESSION_KEY = 'authnrequest.requestId';

    /**
     * @param string|null $entityId
     * @return \LightSaml\Model\Protocol\AuthnRequest|null
     */
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
            ->setRelayState(\Craft::$app->getUser()->getReturnUrl())
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