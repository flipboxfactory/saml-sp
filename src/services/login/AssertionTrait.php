<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\sp\Saml;
use SAML2\Assertion as SamlAssertion;
use SAML2\EncryptedAssertion;
use SAML2\Response as SamlResponse;

trait AssertionTrait
{
    private $firstDecryptedAssertion;

    /**
     * @param SamlResponse $response
     * @return SamlAssertion
     * @throws InvalidMessage
     */
    public function getFirstAssertion(SamlResponse $response)
    {

        /** @var AbstractProvider $ownProvider */
        $ownProvider = Saml::getInstance()->getProvider()->findOwn();

        // grab the first one
        $assertion = $response->getAssertions()[0];

        // decrypt if needed
        if ($ownProvider->keychain && $assertion instanceof EncryptedAssertion && is_null($this->firstDecryptedAssertion)) {
            $assertion = SecurityHelper::decryptAssertion($assertion, $ownProvider->keychain->getDecryptedCertificate());

            // only do this once
            $this->firstDecryptedAssertion = $assertion;
        }


        if (! isset($assertion)) {
            throw new InvalidMessage("Invalid message. No assertions found in response.");
        }

        return $this->firstDecryptedAssertion ?: $assertion;
    }

}

