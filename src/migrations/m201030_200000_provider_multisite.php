<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\core\migrations\m201030_200000_provider_multisite as AbstractMigration;
use flipbox\saml\sp\records\ProviderRecord;

/**
 */
class m201030_200000_provider_multisite extends AbstractMigration
{
    protected function providerRecordTable(): string
    {
        return ProviderRecord::tableName();
    }
}
