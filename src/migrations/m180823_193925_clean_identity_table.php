<?php

namespace flipbox\saml\sp\migrations;

use craft\db\Migration;
use flipbox\saml\sp\records\ProviderIdentityRecord;

/**
 * m180823_193925_clean_identity_table migration.
 */
class m180823_193925_clean_identity_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        /**
         * We have to do this due to the name id be saved wrongly.
         */
        ProviderIdentityRecord::deleteAll();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180823_193925_clean_identity_table cannot be reverted.\n";
        return false;
    }
}
