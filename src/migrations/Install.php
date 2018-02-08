<?php

namespace flipbox\saml\sp\migrations;

use craft\db\Migration;
use craft\records\User as UserRecord;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\keychain\traits\MigrateKeyChain;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use yii\base\Module;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends Migration
{

    use MigrateKeyChain;

    /**
     * @inheritdoc
     */
    protected function getModule(): Module
    {
        return Saml::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->safeUpKeyChain();

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

        $this->safeDownKeyChain();
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
            'id'          => $this->primaryKey(),
            'entityId'    => $this->string()->notNull(),
            'metadata'    => $this->text(),
            'localKeyId'  => $this->integer()->comment('This is our key created for this entity using flipbox\keychain\KeyChain.'),
            'default'     => $this->boolean(),
            'enabled'     => $this->boolean()->defaultValue(true)->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid()
        ]);

        $this->createTable(ProviderIdentityRecord::tableName(), [
            'id'               => $this->primaryKey(),
            'providerId'       => $this->integer()->notNull(),
            'userId'           => $this->integer()->notNull(),
            'providerIdentity' => $this->string()->notNull(),
            'sessionId'        => $this->string()->null(),
            'enabled'          => $this->boolean()->defaultValue(true)->notNull(),
            'lastLoginDate'    => $this->dateTime()->notNull(),
            'dateUpdated'      => $this->dateTime()->notNull(),
            'dateCreated'      => $this->dateTime()->notNull(),
            'uid'              => $this->uid()
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

        $this->addForeignKey(
            $this->db->getForeignKeyName(ProviderRecord::tableName(), 'localKeyId'),
            ProviderRecord::tableName(),
            'localKeyId',
            KeyChainRecord::tableName(),
            'id',
            'CASCADE'
        );

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
