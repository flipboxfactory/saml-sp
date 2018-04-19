<?php

namespace flipbox\saml\sp\services\messages;

use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\AbstractLogoutRequest;
use flipbox\saml\sp\Saml;

class LogoutRequest extends AbstractLogoutRequest
{

    /**
     * @inheritdoc
     */
    public function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}
