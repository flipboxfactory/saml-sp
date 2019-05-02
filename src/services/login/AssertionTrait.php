<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\sp\Saml;
use SAML2\Assertion as SamlAssertion;
use SAML2\Assertion;
use SAML2\EncryptedAssertion;
use SAML2\Response as SamlResponse;

trait AssertionTrait
{
    protected $isAssertionDecrypted = false;

    /**
     * @param SamlResponse $response
     * @return SamlAssertion
     * @throws InvalidMessage
     */
    public function getFirstAssertion(SamlResponse $response)
    {

        $ownProvider = Saml::getInstance()->getProvider()->findOwn();

        // grab the first one
        $assertion = $response->getAssertions()[0];

        // decrypt if needed
        if ($ownProvider->keychain && $assertion instanceof EncryptedAssertion && $this->isAssertionDecrypted === false) {
            $assertion = $assertion->getAssertion(
                $ownProvider->keychain
            );

            // only do this once
            $this->isAssertionDecrypted = true;
        }


        if (! isset($assertion)) {
            throw new InvalidMessage("Invalid message. No assertions found in response.");
        }

        return $assertion;
    }

}

