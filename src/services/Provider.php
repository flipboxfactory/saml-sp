<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\sp\services;

use flipbox\saml\core\services\AbstractProviderService;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\traits\SamlPluginEnsured;

/**
 * Class Provider
 *
 * @package flipbox\saml\sp\services
 */
class Provider extends AbstractProviderService
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     * @deprecated
     */
    public function findOwn()
    {
        return $this->findByEntityId(Saml::getInstance()->getSettings()->getEntityId())->andWhere([
            /** By Definition this needs to be a provider that is enabled. */
            'enabled' => 1,
        ])->one();
    }
}
