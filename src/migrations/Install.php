<?php

namespace flipbox\saml\sp\migrations;

use craft\db\Migration;
use craft\records\User as UserRecord;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

        // Delete tables
        $this->dropTableIfExists(ProviderIdentityRecord::tableName());
        $this->dropTableIfExists(ProviderRecord::tableName());

        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {

        $this->createTable(ProviderRecord::tableName(), [
            'id' => $this->primaryKey(),
            'entityId' => $this->string()->notNull(),
            'metadata' => $this->text(),
//            'handlerClass' => $this->string(),
            'default' => $this->boolean(),
            'enabled' => $this->boolean()->defaultValue(true)->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(ProviderIdentityRecord::tableName(), [
            'id' => $this->primaryKey(),
            'providerId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'providerIdentity' => $this->string()->notNull(),
            'enabled' => $this->boolean()->defaultValue(true)->notNull(),
            'lastLoginDate' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {

        // TokensRecord
        $this->createIndex(
            $this->db->getIndexName(ProviderRecord::tableName(), 'entityId', true, true),
            ProviderRecord::tableName(),
            'entityId',
            true
        );
        $this->createIndex(
            $this->db->getIndexName(ProviderIdentityRecord::tableName(), 'providerIdentity', false, true),
            ProviderIdentityRecord::tableName(),
            'providerIdentity',
            false
        );
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {

        // TokensRecord
        $this->addForeignKey(
            $this->db->getForeignKeyName(ProviderIdentityRecord::tableName(), 'userId'),
            ProviderIdentityRecord::tableName(),
            'userId',
            UserRecord::tableName(),
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(ProviderIdentityRecord::tableName(), 'providerId'),
            ProviderIdentityRecord::tableName(),
            'providerId',
            ProviderRecord::tableName(),
            'id',
            'CASCADE'
        );
    }
}
