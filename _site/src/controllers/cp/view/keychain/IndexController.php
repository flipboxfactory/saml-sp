<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/18/18
 * Time: 10:00 PM
 */

namespace flipbox\saml\sp\controllers\cp\view\keychain;


use craft\base\Plugin;
use flipbox\keychain\controllers\cp\view\AbstractGeneralController;
use flipbox\saml\sp\Saml;

class IndexController extends AbstractGeneralController
{
    /**
     * @return Plugin
     */
    protected function getPlugin(): Plugin
    {
        return Saml::getInstance();
    }

}