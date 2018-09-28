<?php

namespace flipbox\saml\sp\services\bindings;

use flipbox\saml\core\exceptions\InvalidIssuer;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Issuer;
use flipbox\saml\core\services\bindings\AbstractHttpRedirect;

class HttpRedirect extends AbstractHttpRedirect
{

    /**
     * @inheritdoc
     */
    public function getProviderByIssuer(Issuer $issuer): ProviderInterface
    {
        $provider = Saml::getInstance()->getProvider()->findByIssuer(
            $issuer
        )->one();
        if (! $provider) {
            throw new InvalidIssuer(
                sprintf("Invalid issuer: %s", $issuer->getValue())
            );
        }
        return $provider;
    }
}
