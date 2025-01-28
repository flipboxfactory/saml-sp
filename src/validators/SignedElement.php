<?php


namespace flipbox\saml\sp\validators;

use flipbox\saml\core\AbstractPlugin;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\SignedElement as SamlSignedElement;

class SignedElement
{
    /**
     * @var XMLSecurityKey[]
     */
    private $xmlSecurityKeyStore;
    /**
     * @var bool
     */
    private $requireSignature;
    /**
     * @var string
     */
    private $elementName;

    /**
     * SignedElement constructor.
     * @param XMLSecurityKey[] $xmlSecurityKeyStore
     */
    public function __construct(array $xmlSecurityKeyStore, $requireSignature = true, $elementName = "")
    {
        $this->xmlSecurityKeyStore = $xmlSecurityKeyStore;
        $this->requireSignature = $requireSignature;
        $this->elementName = $elementName;
    }


    public function validate(SamlSignedElement $signedElement, $result)
    {
        /** @var \Exception $error */
        $errors = [];
        foreach ($this->xmlSecurityKeyStore as $key) {
            try {
                // returns false when the signature
                $isValid = $signedElement->validate($key);
                if ($isValid !== false) {
                    // return on success ... no need to continue
                    \Craft::info("Signature valid and verified.", AbstractPlugin::SAML_CORE_HANDLE);
                    return $result;
                } else {
                    if ($this->requireSignature) {
                        throw new \Exception("Signature required but not found: $this->elementName");
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e;
                // this is a warning due to there possibly being another key that can be used to verify the signature
                \Craft::warning($e->getMessage(), AbstractPlugin::SAML_CORE_HANDLE);
            }
        }

        if (!empty($errors)) {
            throw $errors[0];
        }

        return $result;
    }
}
