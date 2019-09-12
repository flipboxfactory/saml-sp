<?php

namespace flipbox\saml\sp\tests;

use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Login;
use flipbox\saml\sp\services\login\User;
use flipbox\saml\sp\services\login\UserGroups;
use flipbox\saml\sp\services\Provider;
use flipbox\saml\sp\services\ProviderIdentity;
use flipbox\saml\sp\services\bindings\HttpPost;
use flipbox\saml\sp\services\bindings\HttpRedirect;
use flipbox\saml\sp\services\messages\AuthnRequest;
use flipbox\saml\sp\services\messages\LogoutRequest;
use flipbox\saml\sp\services\messages\LogoutResponse;
use flipbox\saml\core\services\Metadata;
use flipbox\saml\sp\services\messages\Response;
use flipbox\saml\core\services\Session;

class SamlTest extends Unit
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

    public function testComponents()
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
}
