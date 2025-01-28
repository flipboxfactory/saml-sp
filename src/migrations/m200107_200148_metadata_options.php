<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\sp\records\ProviderRecord;

class m200107_200148_metadata_options extends \flipbox\saml\core\migrations\m200107_200148_metadata_options
{
    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }
}
