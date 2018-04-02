<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/10/18
 * Time: 8:40 PM
 */

namespace flipbox\saml\sp\controllers\cp\view\metadata;

use flipbox\saml\core\controllers\cp\view\metadata\AbstractEditController;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

class EditController extends AbstractEditController
{

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }


    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}