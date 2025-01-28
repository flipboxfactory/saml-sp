<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\traits;

use flipbox\saml\core\AbstractPlugin;
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

    public function loadContainer()
    {
        $this->getPlugin()->loadSaml2Container();
    }
}
