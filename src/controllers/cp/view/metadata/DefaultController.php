<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers\cp\view\metadata;


use flipbox\saml\core\controllers\cp\view\metadata\AbstractDefaultController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

class DefaultController extends AbstractDefaultController
{
    /**
     * @inheritdoc
     */
    public function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}