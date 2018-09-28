<?php

namespace flipbox\saml\sp\controllers;

use flipbox\saml\core\controllers\AbstractMetadataController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

class MetadataController extends AbstractMetadataController
{
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
