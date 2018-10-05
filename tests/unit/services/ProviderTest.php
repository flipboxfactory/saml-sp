<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Provider;

class ProviderTest extends Unit
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

    public function testConstants()
    {
        $this->assertEquals(ProviderRecord::TABLE_ALIAS, 'saml_sp_providers');
    }

    public function testGetRecordClass()
    {
        $this->assertEquals(ProviderRecord::class, $this->module->getProviderRecordClass());
    }
}