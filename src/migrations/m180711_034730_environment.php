<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderEnvironmentRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

/**
 * m180711_034730_add_environment_column migration.
 */
class m180711_034730_environment extends \flipbox\saml\core\migrations\m180711_034730_environment
{
    /**
     * @inheritdoc
     */
    protected static function getProviderEnvironmentTableName()
    {
        return ProviderEnvironmentRecord::tableName();
    }

    /**
     * @inheritdoc
     */
    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

}
