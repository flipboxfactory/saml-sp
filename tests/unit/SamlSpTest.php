<?php

namespace flipbox\saml\sp\tests;

use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Login;

class SamlSpTest extends Unit
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

    /**
     * Test the component is set correctly
     */
    public function testLoginComponent()
    {
        $this->assertInstanceOf(
            Login::class,
            $this->module->getLogin()
        );
    }
}
