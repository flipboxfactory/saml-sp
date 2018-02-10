<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 11:59 PM
 */

namespace flipbox\saml\sp\cli;


use flipbox\saml\sp\Saml;
use yii\console\Controller;
use flipbox\keychain\keypair\traits\OpenSSL as OpenSSLTrait;
use flipbox\keychain\keypair\traits\OpenSSLCli as OpenSSLCliTrait;

class KeyChain extends Controller
{

    use OpenSSLTrait, OpenSSLCliTrait;
    /**
     * @var bool $force
     * Force save the metadata. If one already exists, it'll be overwritten.
     */
    public $force;

    protected function getPlugin()
    {
        return Saml::getInstance();
    }

}