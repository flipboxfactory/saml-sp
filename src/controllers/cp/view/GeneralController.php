<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/9/18
 * Time: 2:48 PM
 */

namespace flipbox\saml\sp\controllers\cp\view;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use flipbox\saml\core\controllers\cp\view\AbstractGeneralController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

class GeneralController extends AbstractGeneralController
{

    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
