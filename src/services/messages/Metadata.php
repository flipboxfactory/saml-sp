<?php

namespace flipbox\saml\sp\services\messages;

use flipbox\saml\core\services\messages\AbstractMetadata;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class Metadata extends AbstractMetadata implements MetadataServiceInterface
{
    use SamlPluginEnsured;

    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }
}
