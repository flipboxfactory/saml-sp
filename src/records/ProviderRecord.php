<?php

namespace flipbox\saml\sp\records;

use flipbox\ember\records\traits\StateAttribute;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\Saml;

class ProviderRecord extends AbstractProvider implements ProviderInterface
{

    use StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_sp_providers';

    /**
     * @inheritdoc
     */
    public function getLoginPath()
    {
        if ($this->type !== Saml::IDP) {
            return null;
        }
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->loginRequestEndpoint,
                $this->uid,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getLogoutPath()
    {
        if ($this->type !== Saml::IDP) {
            return null;
        }
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->logoutRequestEndpoint,
                $this->uid,
            ]
        );
    }
}
