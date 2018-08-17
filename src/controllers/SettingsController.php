<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers;

use flipbox\saml\core\controllers\AbstractSettingsController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\actions\Update;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use craft\helpers\ArrayHelper;

/**
 * Class SettingsController
 * @package flipbox\saml\sp\controllers\cp\view
 */
class SettingsController extends AbstractSettingsController
{
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
    protected function getUpdateClass()
    {
        return Update::class;
    }

    /**
     * @inheritdoc
     */
    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
