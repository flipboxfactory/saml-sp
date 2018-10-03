<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\sp\controllers;

use flipbox\saml\core\controllers\AbstractMetadataController;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class MetadataController extends AbstractMetadataController
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
