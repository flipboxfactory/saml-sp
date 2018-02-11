<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:11 AM
 */

namespace flipbox\saml\sp;


use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\messages\ProviderServiceInterface;
use flipbox\saml\core\traits\SamlCore;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\services\messages\AuthnRequest;
use flipbox\saml\sp\services\messages\LogoutRequest;
use flipbox\saml\sp\services\messages\LogoutResponse;
use flipbox\saml\sp\services\messages\Metadata;
use flipbox\saml\sp\services\messages\Response;
use flipbox\saml\sp\services\bindings\HttpPost;
use flipbox\saml\sp\services\bindings\HttpRedirect;
use flipbox\saml\sp\services\Login;
use flipbox\saml\sp\services\Provider;
use flipbox\saml\sp\services\ProviderIdentity;
use flipbox\keychain\traits\ModuleTrait as KeyChainModuleTrait;

class Saml extends Plugin implements SamlPluginInterface
{

    use KeyChainModuleTrait, SamlCore;

    public function init()
    {
        parent::init();

        $this->initComponents();
        $this->initModules();

        // Switch target to console controllers
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = __NAMESPACE__ . '\cli';
            $this->controllerMap = [
                'metadata' => \flipbox\saml\sp\cli\Metadata::class,
                'keychain' => \flipbox\saml\sp\cli\KeyChain::class,
            ];
        }

    }

    /**
     *
     */
    protected function initModules()
    {
        $this->initKeyChain();
        $this->initCore();
    }

    /**
     *
     */
    public function initComponents()
    {
        $this->setComponents([
            'authnRequest'     => AuthnRequest::class,
            'httpPost'         => HttpPost::class,
            'httpRedirect'     => HttpRedirect::class,
            'login'            => Login::class,
            'logoutRequest'    => LogoutRequest::class,
            'logoutResponse'   => LogoutResponse::class,
            'provider'         => Provider::class,
            'providerIdentity' => ProviderIdentity::class,
            'metadata'         => Metadata::class,
            'Response'         => Response::class,
        ]);
    }

    /**
     * @return Settings
     */
    public function getSettings(): Model
    {
        return parent::getSettings();
    }

    /**
     * @inheritdoc
     */
    public function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Components
     */

    /**
     * @return Metadata
     */
    public function getMetadata(): MetadataServiceInterface
    {
        return $this->get('metadata');
    }

    /**
     * @return AuthnRequest
     */
    public function getAuthnRequest()
    {
        return $this->get('authnRequest');
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * @return Login
     */
    public function getLogin()
    {
        return $this->get('login');
    }

    /**
     * @return LogoutRequest
     */
    public function getLogoutRequest()
    {
        return $this->get('logoutRequest');
    }

    /**
     * @return LogoutResponse
     */
    public function getLogoutResponse()
    {
        return $this->get('logoutResponse');
    }

    /**
     * @return HttpPost
     */
    public function getHttpPost()
    {
        return $this->get('httpPost');
    }

    /**
     * @return HttpRedirect
     */
    public function getHttpRedirect()
    {
        return $this->get('httpRedirect');
    }

    /**
     * @returns Provider
     */
    public function getProvider(): ProviderServiceInterface
    {
        return $this->get('provider');
    }

    /**
     * @returns ProviderIdentity
     */
    public function getProviderIdentity()
    {
        return $this->get('providerIdentity');
    }
}