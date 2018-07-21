<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\migrations;


use flipbox\saml\core\migrations\AbstractAlterEnvironments;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderEnvironmentRecord;
use flipbox\saml\sp\Saml;

class AlterEnvironments extends AbstractAlterEnvironments
{

    /**
     * @inheritdoc
     */
    public function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    /**
     * @inheritdoc
     */
    protected static function getProviderEnvironmentRecord()
    {
        return ProviderEnvironmentRecord::class;
    }

}