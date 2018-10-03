<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers;

use flipbox\saml\core\controllers\AbstractSettingsController;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\traits\SamlPluginEnsured;

/**
 * Class SettingsController
 * @package flipbox\saml\sp\controllers\cp\view
 */
class SettingsController extends AbstractSettingsController
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     */
    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
