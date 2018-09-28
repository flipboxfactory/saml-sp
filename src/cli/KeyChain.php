<?php

namespace flipbox\saml\sp\cli;

use flipbox\keychain\cli\AbstractOpenSSL;
use flipbox\saml\sp\Saml;

/**
 * Class KeyChain
 *
 * @package flipbox\saml\sp\cli
 */
class KeyChain extends AbstractOpenSSL
{

    /**
     * @var bool $force
     * Force save the metadata. If one already exists, it'll be overwritten.
     */
    public $force;

    protected function getPlugin()
    {
        return Saml::getInstance();
    }
}
