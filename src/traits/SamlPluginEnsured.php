<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\traits;

use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\containers\Saml2Container;
use flipbox\saml\core\EnsureSAMLPlugin;
use flipbox\saml\sp\Saml;

trait SamlPluginEnsured
{

    /**
     * @see EnsureSAMLPlugin
     * @return AbstractPlugin
     */
    public function getPlugin(): AbstractPlugin
    {
        return Saml::getInstance();
    }

    /**
     * Init the SAML2 Container with the plugin attached
     * @return Saml2Container
     */
    protected function getSaml2Container()
    {
        $container = new Saml2Container(Saml::getInstance());

        return \SAML2\Compat\ContainerSingleton::setContainer($container);
    }
}
