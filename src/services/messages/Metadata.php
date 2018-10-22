<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:48 AM
 */

namespace flipbox\saml\sp\services\messages;

use flipbox\saml\core\services\messages\AbstractMetadata;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class Metadata extends AbstractMetadata implements MetadataServiceInterface
{
    use SamlPluginEnsured;

    /**
     * Deprecated Constants
     */
    /**
     * @deprecated
     */
    const LOGIN_LOCATION = 'sso/login';
    /**
     * @deprecated
     */
    const LOGOUT_RESPONSE_LOCATION = 'sso/logout';
    /**
     * @deprecated
     */
    const LOGOUT_REQUEST_LOCATION = 'sso/logout/request';
    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }
}
