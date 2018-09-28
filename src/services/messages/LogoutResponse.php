<?php

namespace flipbox\saml\sp\services\messages;

use craft\base\Component;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\AbstractLogoutResponse;
use flipbox\saml\core\services\messages\SamlResponseInterface;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\LogoutRequest as LogoutResponseModel;

class LogoutResponse extends AbstractLogoutResponse implements SamlResponseInterface
{
    /**
     * @return SamlPluginInterface
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}
