<?php

namespace flipbox\saml\sp\services\messages;

use flipbox\saml\core\services\messages\AbstractLogoutRequest;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class LogoutRequest extends AbstractLogoutRequest
{

    use SamlPluginEnsured;
}
