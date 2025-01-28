<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\core\migrations\AbstractInstall;
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
    protected function getProviderTableName(): string
    {
        return ProviderRecord::tableName();
    }

    /**
     * @inheritdoc
     */
    protected function getIdentityTableName(): string
    {
        return ProviderIdentityRecord::tableName();
    }
}
