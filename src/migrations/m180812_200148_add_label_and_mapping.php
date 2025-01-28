<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\traits\SamlPluginEnsured;

/**
 * m180812_200148_add_provider_label migration.
 */
class m180812_200148_add_label_and_mapping extends \flipbox\saml\core\migrations\m180812_200148_add_label_and_mapping
{
    use SamlPluginEnsured;

    public function init(): void
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }
}
