<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/16/18
 * Time: 9:59 AM
 */

namespace flipbox\saml\sp\services\traits;


use flipbox\saml\sp\exceptions\InvalidIssuer;
use flipbox\saml\sp\models\Provider;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\EntityDescriptors;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedAssertionReader;
use LightSaml\Model\Assertion\EncryptedAssertionWriter;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Validator\Model\Signature\SignatureValidator;
use RobRichards\XMLSecLibs\XMLSecurityKey;

trait Security
{
    /**
     * @return X509Certificate
     */
    abstract public function getCertificate(): X509Certificate;

    /**
     * @return XMLSecurityKey
     */
    abstract public function getKey(): XMLSecurityKey;

    /**
     * @param SamlMessage $message
     * @return SamlMessage
     */
    public function signMessage(SamlMessage $message)
    {
        return $message->setSignature(new \LightSaml\Model\XmlDSig\SignatureWriter(
                $this->getCertificate(),
                $this->getKey()
            )
        );

    }

    /**
     * @param Response $response
     * @return Assertion[]
     */
    public function decryptAssertions(Response $response)
    {
        $credential = new X509Credential($this->getCertificate(), $this->getKey());
        $readers = $response->getAllEncryptedAssertions();

        $decryptDeserializeContext = new \LightSaml\Model\Context\DeserializationContext();

        $assertions = [];
        foreach ($readers as $reader) {
            /** @var EncryptedAssertionReader $reader */
            $assertions = $reader->decryptMultiAssertion([$credential] , $decryptDeserializeContext);
        }

        return $assertions;

    }

    /**
     * @param Assertion $assertion
     * @return EncryptedAssertionWriter
     */
    public function encryptAssertion(Assertion $assertion)
    {
        $encryptedAssertion = new EncryptedAssertionWriter();
        $encryptedAssertion->encrypt($assertion, $this->getKey());

        return $encryptedAssertion;
    }


    /**
     * @param SamlMessage $message
     * @return bool
     * @throws InvalidIssuer
     */
    public function validSender(SamlMessage $message)
    {
        if (! $provider = Saml::getInstance()->getProvider()->findByCondition([
            'entityId' => $message->getIssuer()->getValue(),
        ])) {
            throw new InvalidIssuer("Invalid request", 400);
        }

        return true;
    }

    /**
     * @param SamlMessage $message
     * @return bool
     * @throws InvalidIssuer
     */
    public function validSignature(SamlMessage $message)
    {

        /** @var $provider Provider */
        if (! $provider = Saml::getInstance()->getProvider()->findByCondition([
            'entityId' => $message->getIssuer()->getValue(),
        ])) {
            throw new InvalidIssuer("Invalid request", 400);
        }

        /** @var \LightSaml\Model\XmlDSig\SignatureXmlReader $signatureReader */
        $signatureReader = $message->getSignature();
        $key = $provider->getMetadata()->getFirstIdpSsoDescriptor()->getFirstKeyDescriptor(KeyDescriptor::USE_SIGNING);
        try {

            if ($signatureReader->validate(
                KeyHelper::createPublicKey(
                    $key->getCertificate()
                )
            )) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }


    }
}