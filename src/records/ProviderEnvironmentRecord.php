<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;

use flipbox\ember\records\traits\StateAttribute;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\AbstractProviderEnvironment;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\Saml;

class ProviderEnvironmentRecord extends AbstractProviderEnvironment
{

    use StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_sp_provider_environments';

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}
