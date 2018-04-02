<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/18/18
 * Time: 9:53 PM
 */

namespace flipbox\saml\sp\controllers\cp\view\keychain;


use craft\base\Plugin;
use flipbox\keychain\controllers\cp\view\AbstractEditController;
use flipbox\saml\sp\Saml;

class EditController extends AbstractEditController
{
    protected function getPlugin(): Plugin
    {
        return Saml::getInstance();
    }

}