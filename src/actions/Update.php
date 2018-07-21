<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\actions;


use flipbox\saml\core\actions\AbstractUpdate;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\Saml;

/**
 * Class Update
 * @package flipbox\saml\sp\actions
 */
class Update extends AbstractUpdate
{
    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

}