<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers\cp\view\metadata;

use flipbox\saml\core\controllers\cp\view\metadata\AbstractDefaultController;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class DefaultController extends AbstractDefaultController
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     */
    public function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
