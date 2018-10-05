<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;

class ProviderIdentityTest extends Unit
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
        $this->assertEquals(ProviderIdentityRecord::TABLE_ALIAS, 'saml_sp_provider_identity');
    }

    public function testGetRecordClass()
    {
        $this->assertEquals(ProviderIdentityRecord::class, $this->module->getProviderIdentityRecordClass());
    }
}