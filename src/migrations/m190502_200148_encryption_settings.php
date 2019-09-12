<?php

namespace flipbox\saml\core\migrations;

use flipbox\saml\sp\records\ProviderRecord;

/**
 * m190502_200148_encryption_settings migration.
 */
class m180812_200148_add_label_and_mapping extends \flipbox\saml\core\migrations\m190502_200148_encryption_settings
{

    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }
}
