<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;

use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;

class ProviderRecord extends AbstractProvider implements ProviderInterface
{

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_sp_providers';

    /**
     * @inheritdoc
     */
    public function getLoginRequestPath()
    {
        if ($this->providerType !== Settings::IDP) {
            return null;
        }
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->getDefaultLoginRequestPath(),
                $this->uid,
            ]
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
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->getDefaultLogoutRequestPath(),
                $this->uid,
            ]
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
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->getDefaultLoginEndpoint(),
                $this->uid,
            ]
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
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->getDefaultLogoutEndpoint(),
                $this->uid,
            ]
        );
    }
}
