<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 2/24/18
 * Time: 8:48 PM
 */

namespace flipbox\saml\sp\services\bindings;

use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\bindings\AbstractFactory;
use flipbox\saml\sp\Saml;

class Factory extends AbstractFactory
{
    public static function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}
