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
use flipbox\saml\core\services\bindings\AbstractHttpPost;
use flipbox\saml\core\services\traits\Security;
use flipbox\saml\sp\models\Provider;
use flipbox\saml\sp\Saml;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Assertion\Issuer;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class AbstractHttpPost
 * @package flipbox\saml\sp\services\bindings
 */
class HttpPost extends AbstractHttpPost
{
    use Security;

    const TEMPLATE_PATH = 'saml-sp/_components/post-binding-submit.twig';

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

    public function getTemplatePath()
    {
        return static::TEMPLATE_PATH;
    }
}