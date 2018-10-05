<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\messages\Metadata;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\SamlConstants;

class MetadataTest extends Unit
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

    public function testAutoCreate()
    {
        $entityDescriptor = $this->module->getMetadata()->create();
        $this->assertInstanceOf(
            EntityDescriptor::class,
            $entityDescriptor
        );
    }

    public function testCreateWithDefaultEntityId()
    {
        $entityDescriptor = $this->module->getMetadata()->create();
        $this->assertEquals(
            $entityDescriptor->getEntityID(),
            $this->module->getSettings()->getEntityId()
        );
    }

    public function testCreateWithCustomEntityId()
    {
        $entityId = 'http://my-entity-id';

        $entityDescriptor = $this->module->getMetadata()->create(null, $entityId);
        $this->assertEquals(
            $entityDescriptor->getEntityID(),
            $entityId
        );
    }

    public function testSupportedBindings()
    {
        /** @var Metadata $metadata */
        $metadata = $this->module->getMetadata();
        $this->assertInternalType('array', $metadata->getSupportedBindings());
        $this->assertContains(SamlConstants::BINDING_SAML2_HTTP_POST, $metadata->getSupportedBindings());
    }
}