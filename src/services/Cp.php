<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services;


use flipbox\saml\core\migrations\AbstractAlterEnvironments;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\AbstractCp;
use flipbox\saml\sp\migrations\AlterEnvironments;
use flipbox\saml\sp\Saml;

/**
 * Class Cp
 * @package flipbox\saml\sp\services
 */
class Cp extends AbstractCp
{
    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    /**
     * @inheritdoc
     */
    protected function createNewMigration(): AbstractAlterEnvironments
    {
        return new AlterEnvironments();
    }

}