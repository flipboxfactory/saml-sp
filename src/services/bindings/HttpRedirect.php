<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\sp\services\bindings;

use flipbox\saml\core\exceptions\InvalidIssuer;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\services\bindings\AbstractHttpRedirect;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Issuer;

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
