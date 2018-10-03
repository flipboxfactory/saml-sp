<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\sp\services\bindings;

use flipbox\saml\core\services\bindings\AbstractHttpPost;
use flipbox\saml\sp\traits\SamlPluginEnsured;

/**
 * Class AbstractHttpPost
 *
 * @package flipbox\saml\sp\services\bindings
 */
class HttpPost extends AbstractHttpPost
{
    use SamlPluginEnsured;

    const TEMPLATE_PATH = 'saml-sp/_components/post-binding-submit.twig';

    public function getTemplatePath()
    {
        return static::TEMPLATE_PATH;
    }
}
