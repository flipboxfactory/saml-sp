<?php

namespace flipbox\saml\sp\migrations;

use Craft;
use craft\db\Migration;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

/**
 * m180812_200148_add_provider_label migration.
 */
class m180812_200148_add_label_and_mapping extends \flipbox\saml\core\migrations\m180812_200148_add_label_and_mapping
{
    public function init()
    {
        parent::init();
    }

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
    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }

}
