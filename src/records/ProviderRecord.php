<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;

use flipbox\saml\core\helpers\UrlHelper;
use flipbox\saml\core\models\AbstractSettings;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;

class ProviderRecord extends AbstractProvider implements ProviderInterface
{
    /**
     * The table alias
     */
    public const TABLE_ALIAS = 'saml_sp_providers';

    /**
     * @return AbstractSettings
     */
    protected function getDefaultSettings(): AbstractSettings
    {
        return Saml::getInstance()->getSettings();
    }

    /**
     * @inheritdoc
     */
    public function getLoginRequestPath()
    {
        if ($this->providerType !== Settings::IDP) {
            return null;
        }
        return UrlHelper::buildEndpointUrl(
            Saml::getInstance()->getSettings(),
            UrlHelper::LOGIN_REQUEST_ENDPOINT,
            $this,
            false
        );
    }

    /**
     * @inheritdoc
     */
    public function getLogoutRequestPath()
    {
        if ($this->providerType !== Settings::IDP) {
            return null;
        }
        return UrlHelper::buildEndpointUrl(
            Saml::getInstance()->getSettings(),
            UrlHelper::LOGOUT_REQUEST_ENDPOINT,
            $this,
            false
        );
    }
    /**
     * @inheritdoc
     */
    public function getLoginPath()
    {
        if ($this->providerType !== Settings::IDP) {
            return null;
        }
        return UrlHelper::buildEndpointUrl(
            Saml::getInstance()->getSettings(),
            UrlHelper::LOGIN_ENDPOINT,
            $this,
            false
        );
    }

    /**
     * @inheritdoc
     */
    public function getLogoutPath()
    {
        if ($this->providerType !== Settings::IDP) {
            return null;
        }
        return UrlHelper::buildEndpointUrl(
            Saml::getInstance()->getSettings(),
            UrlHelper::LOGOUT_ENDPOINT,
            $this,
            false
        );
    }
}
