<?php

namespace Step\Unit\Common;

use Codeception\Scenario;
use flipbox\keychain\KeyChain;
use flipbox\keychain\keypair\OpenSSL;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Provider;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;

class Metadata extends \UnitTester
{
    /**
     * @var Saml
     */
    private $module;

    public function __construct(AbstractPlugin $module, Scenario $scenario)
    {
        $this->module = $module;
        parent::__construct($scenario);
    }

    private function makeKeyChain()
    {
        $keychain = new KeyChain('keychain');
        $openssl = new OpenSSL(
            $keychain->getSettings()->opensslDefaults
        );

        $keypair = $openssl->create();

        $keypair->isEncrypted = false;

        return $keypair;
    }

    public function idpPrivateKey($type = XMLSecurityKey::RSA_SHA256)
    {

        $key = new XMLSecurityKey(
            $type,
            [
                'type' => 'private',
            ]
        );


        $contents = file_get_contents(codecept_data_dir() . '/keypairs/saml-idp.pem');
        $key->loadKey(
            $contents
        );

        return $key;
    }

    public function spPrivateKey($type = XMLSecurityKey::RSA_SHA256)
    {

        $key = new XMLSecurityKey(
            $type,
            [
                'type' => 'private',
            ]
        );

        $key->loadKey(
            codecept_data_dir() . '/keypairs/saml-sp.pem',
            true
        );

        return $key;
    }

    /**
     * @return EntityDescriptor
     * @throws \Exception
     */
    public function createSpEntityDescriptor()
    {
        $doc = DOMDocumentFactory::fromFile(
            codecept_data_dir() . '/xml/sp-metadata-without-keys.xml'
        );
        return new EntityDescriptor($doc->documentElement);
    }

    /**
     * @return EntityDescriptor
     * @throws \Exception
     */
    public function createSpEntityDescriptorWithKeyDescriptors()
    {
        $doc = DOMDocumentFactory::fromFile(
            codecept_data_dir() . '/xml/sp-metadata-with-keys.xml'
        );
        return new EntityDescriptor($doc->documentElement);
    }

    /**
     * @return EntityDescriptor
     * @throws \Exception
     */
    public function createIdpEntityDescriptor()
    {
        $doc = DOMDocumentFactory::fromFile(
            codecept_data_dir() . '/xml/idp-metadata-without-keys.xml'
        );
        return new EntityDescriptor($doc->documentElement);
    }

    /**
     * @return EntityDescriptor
     * @throws \Exception
     */
    public function createIdpEntityDescriptorWithKeyDescriptors()
    {
        $doc = DOMDocumentFactory::fromFile(
            codecept_data_dir() . '/xml/idp-metadata-with-keys.xml'
        );
        return new EntityDescriptor($doc->documentElement);
    }

    /**
     * Define custom actions here
     * @throws \Exception
     */
    public function createMyEntityDescriptor(
        KeyChainRecord $withKey = null,
        $entityId = null
    )
    {
        $I = $this;

        $settings = $this->module->getSettings();

        $doc = DOMDocumentFactory::fromFile(
            codecept_data_dir() . '/xml/sp-metadata-without-keys.xml'
        );

        $metadata =  new EntityDescriptor($doc->documentElement);

        if($withKey) {
            foreach($metadata->getRoleDescriptor() as $descriptor){
                $this->module->getMetadata()->updateDescriptorCertificates(
                    $descriptor,
                    $withKey
                );
            }
        }

        if($entityId) {
            $metadata->setEntityID($entityId);
            $settings->setEntityId($entityId);
        }else{
            $settings->setEntityId($metadata->getEntityID());
        }

        $I->assertEquals(
            $settings->getEntityId(),
            $metadata->getEntityID()
        );

        return $metadata;
    }

    public function createMyEntityDescriptorWithKey($entityId = null)
    {
        $keypair = $this->makeKeyChain();
        return $this->createMyEntityDescriptor($keypair, $entityId);
    }

    /**
     * @param AbstractPlugin $plugin
     * @param EntityDescriptor $descriptor
     * @param $type
     * @return AbstractProvider
     */
    public function createProviderWithEntityDescriptor(AbstractPlugin $plugin, EntityDescriptor $descriptor, $type)
    {
        $providerClass = $plugin->getProviderRecordClass();
        $provider = new $providerClass([
            'providerType' => $type,
            'mapping' => '[{"attributeName":"att1","craftProperty":"email"},{"attributeName":"att2","craftProperty":"firstName"},{"attributeName":"att3","craftProperty":"","templateOverride":"{lastName}"}]',
        ]);
        $provider->setMetadataModel($descriptor);

        $provider->encryptionMethod = XMLSecurityKey::AES256_CBC;

        return $provider;
    }

    /**
     * @param AbstractPlugin $plugin
     * @param EntityDescriptor $descriptor
     * @return AbstractProvider
     */
    public function createSpProviderWithEntityDescriptor(AbstractPlugin $plugin, EntityDescriptor $descriptor)
    {
        return $this->createProviderWithEntityDescriptor(
            $plugin,
            $descriptor,
            ProviderInterface::TYPE_SP
        );
    }

    /**
     * @param AbstractPlugin $plugin
     * @param EntityDescriptor $descriptor
     * @return AbstractProvider
     */
    public function createIdpProviderWithEntityDescriptor(AbstractPlugin $plugin, EntityDescriptor $descriptor)
    {
        return $this->createProviderWithEntityDescriptor(
            $plugin,
            $descriptor,
            ProviderInterface::TYPE_IDP
        );
    }

    /**
     * @param AbstractPlugin $plugin
     * @return AbstractProvider
     */
    public function createMyProvider(AbstractPlugin $plugin, KeyChainRecord $keypair = null)
    {
        $ed = $this->createMyEntityDescriptor($keypair);
        return $plugin->getProvider()->create(
            $ed,
            $keypair
        );
    }

    /**
     * @param AbstractPlugin $plugin
     * @return AbstractProvider
     */
    public function createMyProviderWithKey(AbstractPlugin $plugin)
    {
        $keypair = $this->makeKeyChain();
        return $this->createMyProvider($plugin, $keypair);
    }

    /**
     * @param AbstractPlugin $plugin
     * @return AbstractProvider
     * @throws \Exception
     */
    public function createTheirProvider(AbstractPlugin $plugin)
    {
        if ($plugin->getRemoteType() === SettingsInterface::SP) {
            $ed = $this->createSpEntityDescriptor();
            return $this->createSpProviderWithEntityDescriptor(
                $plugin,
                $ed
            );
        } else {
            $ed = $this->createIdpEntityDescriptor();
            return $this->createIdpProviderWithEntityDescriptor(
                $plugin,
                $ed
            );
        }
    }

    /**
     * @param AbstractPlugin $plugin
     * @return AbstractProvider
     * @throws \Exception
     */
    public function createTheirProviderWithSigningKey(AbstractPlugin $plugin)
    {
        if ($plugin->getRemoteType() === SettingsInterface::SP) {
            $ed = $this->createSpEntityDescriptorWithKeyDescriptors();
            return $this->createSpProviderWithEntityDescriptor(
                $plugin,
                $ed
            );
        } else {
            $ed = $this->createIdpEntityDescriptorWithKeyDescriptors();
            return $this->createIdpProviderWithEntityDescriptor(
                $plugin,
                $ed
            );
        }

    }

    /**
     * @param AbstractPlugin $plugin
     * @return AbstractProvider
     * @throws \Exception
     */
    public function createTheirProviderWithEncryptionKey(AbstractPlugin $plugin)
    {
        if ($plugin->getRemoteType() === SettingsInterface::SP) {
            $ed = $this->createSpEntityDescriptorWithKeyDescriptors();
            return $this->createSpProviderWithEntityDescriptor(
                $plugin,
                $ed
            );
        } else {
            $ed = $this->createIdpEntityDescriptorWithKeyDescriptors();
            return $this->createIdpProviderWithEntityDescriptor(
                $plugin,
                $ed
            );
        }
    }
}