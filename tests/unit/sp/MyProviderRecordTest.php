<?php


namespace tests\unit\sp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\IndexedEndpointType;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class MyProviderRecordTest extends Unit
{

    /**
     * @var Saml
     */
    private $module;

    /**
     * @var Metadata
     */
    private $metadataFactory;
    /**
     * @var SamlPlugin
     */
    private $pluginHelper;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml('saml-sp');

        $scenario = new Scenario($this);

        $this->metadataFactory = new Metadata($this->module, $scenario);
        $this->pluginHelper = new SamlPlugin($this->module, $scenario);
    }

    public function testProviderType()
    {
        $recordClass = $this->module->getProviderRecordClass();
        $this->assertInstanceOf(
            AbstractProvider::class,
            new $recordClass
        );
    }

    public function testProviderMetadataWithKeyPair()
    {
        $this->pluginHelper->installIfNeeded();

        $this->assertInstanceOf(
            EntityDescriptor::class,
            $metadata = $this->metadataFactory->createMyEntityDescriptorWithKey()
        );
    }

    public function testProviderMetadata()
    {
        $this->pluginHelper->installIfNeeded();

        $this->assertInstanceOf(
            EntityDescriptor::class,
            $metadata = $this->metadataFactory->createMyEntityDescriptor()
        );

        $provider = new ProviderRecord();
        $provider->setMetadataModel($metadata);

        $provider->providerType = $provider::TYPE_SP;

        $this->assertInstanceOf(
            EntityDescriptor::class,
            $provider->getMetadataModel()
        );

        $this->assertGreaterThan(
            0,
            count($provider->spSsoDescriptors())
        );

        foreach ($provider->spSsoDescriptors() as $descriptor) {
            $this->assertGreaterThan(
                0,
                count(
                    $descriptor->getAssertionConsumerService()
                )
            );

            /** @var IndexedEndpointType $endpoint */
            $endpoint = $descriptor->getAssertionConsumerService()[0];

            $this->assertInstanceOf(
                IndexedEndpointType::class,
                $endpoint
            );

            $this->assertEquals(
                Constants::BINDING_HTTP_POST,
                $endpoint->getBinding()
            );
        }

        $this->assertNull(
            $provider->getLoginPath()
        );

        $this->assertNull(
            $provider->getLogoutPath()
        );

        $this->assertNull(
            $provider->getLoginRequestPath()
        );

        $this->assertNull(
            $provider->getLogoutRequestPath()
        );

    }

    public function testOwnProvider(){

        $this->pluginHelper->installIfNeeded();
        $metadata = $this->metadataFactory->createMyEntityDescriptorWithKey();
        $provider = new ProviderRecord();
        $provider->setMetadataModel($metadata);
        $provider->entityId = 'localhost';
        $provider->save();

        Saml::getInstance()->getSettings()->setEntityId('localhost');

        $own = Saml::getInstance()->getProvider()->findOwn();

        $this->assertSame(
            $own->getEntityId(),
            $provider->getEntityId()
        );
    }

    public function testEntityDescriptorTrait()
    {
        $this->pluginHelper->installIfNeeded();
        $metadata = $this->metadataFactory->createMyEntityDescriptorWithKey();

        $provider = new ProviderRecord();
        $provider->setMetadataModel($metadata);

        $this->assertGreaterThan(
            0,
            count($provider->spSsoDescriptors())
        );

        $this->assertStringContainsString(
            '<?xml',
            $provider->toXmlString()
        );

        // to string
        $this->assertStringContainsString(
            '<?xml',
            (string)$provider
        );

        $endpoint = $provider->firstSpAcsService(Constants::BINDING_HTTP_POST);

        $this->assertEquals(
            $endpoint->getBinding(),
            Constants::BINDING_HTTP_POST
        );

        $endpoint = $provider->firstSpSloService(Constants::BINDING_HTTP_POST);

        $this->assertEquals(
            $endpoint->getBinding(),
            Constants::BINDING_HTTP_POST
        );

        $xmlSecurityKey = $provider->signingXMLSecurityKey();

        $this->assertInstanceOf(
            XMLSecurityKey::class,
            $xmlSecurityKey
        );

    }

}