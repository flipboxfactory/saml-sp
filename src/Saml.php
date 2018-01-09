<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:11 AM
 */

namespace flipbox\saml\sp;


use craft\base\Plugin;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\services\Metadata;

class Saml extends Plugin
{

    public function createSettingsModel()
    {
        return new Settings();
    }


    public static function getInstance(){
        return parent::getInstance();
    }

    /**
     * @return Metadata
     */
    public function getMetadata(){
        return $this->get('metadata');
    }
}