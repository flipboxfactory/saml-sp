<?php

namespace flipbox\saml\sp\services\bindings;

use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\bindings\AbstractHttpPost;
use flipbox\saml\sp\Saml;

/**
 * Class AbstractHttpPost
 *
 * @package flipbox\saml\sp\services\bindings
 */
class HttpPost extends AbstractHttpPost
{
    const TEMPLATE_PATH = 'saml-sp/_components/post-binding-submit.twig';

    public function getTemplatePath()
    {
        return static::TEMPLATE_PATH;
    }

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}
