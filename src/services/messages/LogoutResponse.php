<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 8:30 PM
 */

namespace flipbox\saml\sp\services\messages;

use flipbox\saml\core\services\messages\AbstractLogoutResponse;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class LogoutResponse extends AbstractLogoutResponse
{
    use SamlPluginEnsured;
}
