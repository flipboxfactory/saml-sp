<?php

namespace flipbox\saml\sp\services;

use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\AbstractProviderService;
use flipbox\saml\core\services\ProviderServiceInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

/**
 * Class Provider
 *
 * @package flipbox\saml\sp\services
 */
class Provider extends AbstractProviderService implements ProviderServiceInterface
{
    /**
     * @inheritdoc
     */
    public function getRecordClass()
    {
        return ProviderRecord::class;
    }

    /**
     * @inheritdoc
     */
    public function findOwn()
    {
        return $this->findByEntityId(Saml::getInstance()->getSettings()->getEntityId())->one();
    }

    /**
     * @return SamlPluginInterface
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}
