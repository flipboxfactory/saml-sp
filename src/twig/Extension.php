<?php

namespace flipbox\saml\sp\twig;

use flipbox\saml\core\helpers\UrlHelper;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use yii\base\InvalidArgumentException;

class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            // First argument is the function name; second is a callable:
            new TwigFunction('samlSpLogoutUrl', [Extension::class, 'getLogoutUrl']),
        ];
    }

    /**
     * @param string $idpEntityId
     * @return string
     */
    public static function getLogoutUrl(string $idpEntityId): string
    {
        $providerQuery = Saml::getInstance()->getProvider()->findByEntityId($idpEntityId);

        $provider = $providerQuery->one();
        if (!($provider instanceof ProviderRecord)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Provider '%s' not found or not a saml provider. Check the provider configuration.",
                    $idpEntityId
                )
            );
        }

        return UrlHelper::siteUrl($provider->getLogoutRequestPath());
    }
}
