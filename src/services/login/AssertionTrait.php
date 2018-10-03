<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response as SamlResponse;

trait AssertionTrait
{
    protected $isAssertionDecrypted = false;
    /**
     * @param SamlResponse $response
     * @return Assertion
     * @throws InvalidMessage
     */
    public function getFirstAssertion(\LightSaml\Model\Protocol\Response $response)
    {

        $ownProvider = Saml::getInstance()->getProvider()->findOwn();

        if ($ownProvider->keychain &&
            $response->getFirstEncryptedAssertion() &&
            $this->isAssertionDecrypted === false
        ) {
            Saml::getInstance()->getResponse()->decryptAssertions(
                $response,
                $ownProvider->keychain
            );
            /**
             * try to only do this once
             */
            $this->isAssertionDecrypted = true;
        }

        $assertions = $response->getAllAssertions();

        if (! isset($assertions[0])) {
            throw new InvalidMessage("Invalid message. No assertions found in response.");
        }
        /**
         * Just grab the first one for now.
         */
        return $assertions[0];
    }
}
