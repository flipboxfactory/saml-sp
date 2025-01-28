<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\sp\records\ProviderRecord;
use SAML2\Assertion;
use SAML2\Assertion as SamlAssertion;
use SAML2\EncryptedAssertion;
use SAML2\Response as SamlResponse;

trait AssertionTrait
{
    private $decryptedAssertions = [];

    /**
     * @param SamlResponse $response
     * @return SamlAssertion
     * @throws InvalidMessage
     */
    public function getFirstAssertion(SamlResponse $response, ProviderRecord $serviceProvider)
    {
        $assertions = $this->getAssertions($response, $serviceProvider);

        if (!count($assertions)) {
            throw new InvalidMessage("Invalid message. No assertions found in response.");
        }

        return $assertions[0];
    }

    /**
     * @param SamlResponse $response
     * @return Assertion[]
     * @throws \Exception
     */
    private function getAssertions(SamlResponse $response, ProviderRecord $ownProvider)
    {
        // is there a cache already?
        if (count($this->decryptedAssertions)) {
            return $this->decryptedAssertions;
        }

        // grab the first one
        foreach ($response->getAssertions() as $assertion) {
            if ($ownProvider->keychain &&
                $assertion instanceof EncryptedAssertion
            ) {
                $assertion = SecurityHelper::decryptAssertion(
                    $assertion,
                    $ownProvider->keychain->getDecryptedKey()
                );

                $this->decryptedAssertions[] = $assertion;
            } else {
                $this->decryptedAssertions[] = $assertion;
            }
        }

        $response->setAssertions(
            $this->decryptedAssertions
        );

        return $this->decryptedAssertions;
    }
}
