<?php


namespace tests\unit\sp;


use Codeception\Test\Unit;
use craft\events\RegisterUrlRulesEvent;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\containers\Saml2Container;
use flipbox\saml\core\services\Metadata;
use flipbox\saml\core\services\Session;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Login;
use flipbox\saml\sp\services\login\User;
use flipbox\saml\sp\services\login\UserGroups;
use flipbox\saml\sp\services\messages\AuthnRequest;
use flipbox\saml\sp\services\Provider;
use flipbox\saml\sp\services\ProviderIdentity;
use flipbox\saml\sp\traits\SamlPluginEnsured;
use SAML2\Utils;

class PluginTest extends Unit
{
    /**
     * @var Saml
     */
    private $module;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml('saml-sp');
    }

    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Saml::class,
            $this->module
        );

        $this->assertInstanceOf(
            AbstractPlugin::class,
            $this->module
        );

    }

    public function testPluginType()
    {
        $this->assertEquals(Settings::SP, $this->module->getMyType());
    }

    public function testPluginComponents()
    {
        $this->assertInstanceOf(AuthnRequest::class, $this->module->getAuthnRequest());
        $this->assertInstanceOf(Login::class, $this->module->getLogin());
        $this->assertInstanceOf(Provider::class, $this->module->getProvider());
        $this->assertInstanceOf(ProviderIdentity::class, $this->module->getProviderIdentity());
        $this->assertInstanceOf(Metadata::class, $this->module->getMetadata());
        $this->assertInstanceOf(Session::class, $this->module->getSession());
        $this->assertInstanceOf(User::class, $this->module->getUser());
        $this->assertInstanceOf(UserGroups::class, $this->module->getUserGroups());
    }

    public function testCpRules()
    {
        $plugin = $this->module;
        $plugin::onRegisterCpUrlRules(
            new RegisterUrlRulesEvent()
        );
    }

    public function testEnsurePlugin()
    {
        $mock = $this->getMockForTrait(SamlPluginEnsured::class);
        $samlPlugin = $mock->getPlugin();
        $this->assertEquals($this->module, $samlPlugin);

        $mock->loadContainer();

        $this->assertInstanceOf(
            Saml2Container::class,
            Utils::getContainer()
        );
    }

    public function testSaml2Container()
    {
        $container = $this->module->loadSaml2Container();

        $this->assertInstanceOf(
            Saml2Container::class,
            $container
        );

        $this->assertEquals(
            $this->module,
            $container->getPlugin()
        );

        $this->assertIsString(
            $container->generateId()
        );

        $this->assertInstanceOf(
            \Psr\Log\LoggerInterface::class,
            $container->getLogger()
        );

    }
}