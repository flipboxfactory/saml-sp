<?php

namespace flipbox\saml\sp\migrations;

use craft\db\Migration;
use craft\records\User as UserRecord;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\keychain\traits\MigrateKeyChain;
use flipbox\saml\core\migrations\AbstractInstall;
use flipbox\saml\core\records\ProviderIdentityInterface;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use yii\base\Module;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends AbstractInstall
{

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
    protected function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }

    /**
     * @inheritdoc
     */
    protected function getIdentityTableName()
    {
        return ProviderIdentityRecord::tableName();
    }

}
