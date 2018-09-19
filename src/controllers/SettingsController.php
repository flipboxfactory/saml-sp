<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers;

use flipbox\saml\core\controllers\AbstractSettingsController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

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
    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
