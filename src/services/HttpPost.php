<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\sp\services;


use craft\base\Component;
use craft\web\Request;
use flipbox\saml\sp\exceptions\InvalidIssuer;
use flipbox\saml\sp\exceptions\InvalidMetadata;
use flipbox\saml\sp\exceptions\InvalidSignature;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\traits\Security;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Protocol\SamlMessage;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use LightSaml\Credential\X509Certificate;

/**
 * Class HttpPost
 * @package flipbox\saml\sp\services
 */
class HttpPost extends AbstractHttpBinding
{

    use Security;

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

    /**
     * @param Request $request
     * @return \LightSaml\Model\Protocol\AuthnRequest|\LightSaml\Model\Protocol\LogoutRequest|\LightSaml\Model\Protocol\LogoutResponse|\LightSaml\Model\Protocol\Response|SamlMessage
     */
    public function receive(Request $request, $validateSender=true)
    {

        $post = $request->getBodyParams();
        if (array_key_exists('SAMLRequest', $post)) {
            $msg = $post['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $post)) {
            $msg = $post['SAMLResponse'];
        } else {
            throw new LightSamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }

        $msg = base64_decode($msg);

        $context = new MessageContext();
        $deserializationContext = $context->getDeserializationContext();
        $message = SamlMessage::fromXML($msg, $deserializationContext);

        if($validateSender) {
            $this->validSender($message);
        }

        if(!$this->validSignature($message)){
            throw new InvalidSignature("Invalid request", 400);
        }

        if (array_key_exists('RelayState', $post)) {
            $message->setRelayState($post['RelayState']);
        }

        return $message;
    }

}