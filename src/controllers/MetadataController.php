<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\sp\controllers;


use flipbox\saml\core\controllers\AbstractMetadataController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\Saml;

class MetadataController extends AbstractMetadataController
{
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}