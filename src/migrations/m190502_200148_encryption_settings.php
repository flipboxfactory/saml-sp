<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\sp\records\ProviderRecord;

/**
 * m190502_200148_encryption_settings migration.
 */
class m190502_200148_encryption_settings extends \flipbox\saml\core\migrations\m190502_200148_encryption_settings
{
    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }
}
