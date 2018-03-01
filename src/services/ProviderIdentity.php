<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\sp\services;


use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\AbstractProviderIdentityService;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;

/**
 * Class ProviderIdentity
 * @package flipbox\saml\sp\services
 */
class ProviderIdentity extends AbstractProviderIdentityService
{

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getRecordClass()
    {
        return ProviderIdentityRecord::class;
    }

}