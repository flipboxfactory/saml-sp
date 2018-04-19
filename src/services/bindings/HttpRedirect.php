<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\sp\services\bindings;

use flipbox\saml\core\exceptions\InvalidIssuer;
use flipbox\saml\core\models\ProviderInterface;
use flipbox\saml\sp\Saml;
use flipbox\saml\core\services\traits\Security;
use LightSaml\Model\Assertion\Issuer;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use LightSaml\Credential\X509Certificate;
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
        );
        if (! $provider) {
            throw new InvalidIssuer(
                sprintf("Invalid issuer: %s", $issuer->getValue())
            );
        }
        return $provider;
    }
}
