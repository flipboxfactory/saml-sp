<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:48 AM
 */

namespace flipbox\saml\sp\services\messages;


use craft\base\Component;
use craft\helpers\UrlHelper;
use flipbox\keychain\keypair\OpenSSL;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\sp\models\Provider;
use flipbox\saml\sp\records\ProviderRecord;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\SamlConstants;
use flipbox\saml\sp\Saml;
use flipbox\saml\core\services\traits\Metadata as MetadataTrait;

class Metadata extends Component implements MetadataServiceInterface
{

    use MetadataTrait;

    /**
     *
     */
    const LOGIN_LOCATION = 'saml-sp/login';
    const LOGOUT_RESPONSE_LOCATION = 'saml-sp/logout/response';
    const LOGOUT_REQUEST_LOCATION = 'saml-sp/logout/request';

    /**
     * @return string
     */
    public static function getLogoutResponseLocation()
    {
        return UrlHelper::actionUrl(static::LOGOUT_RESPONSE_LOCATION);
    }

    /**
     * @return string
     */
    public static function getLogoutRequestLocation()
    {
        return UrlHelper::actionUrl(static::LOGOUT_REQUEST_LOCATION);
    }

    /**
     * @return string
     */
    public static function getLoginLocation()
    {
        return UrlHelper::actionUrl(static::LOGIN_LOCATION);
    }

    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }

    /**
     * @param AbstractProvider $provider
     * @return bool
     */
    protected function useEncryption(AbstractProvider $provider)
    {
        return Saml::getInstance()->getSettings()->encryptAssertions;
    }

    /**
     * @param AbstractProvider $provider
     * @return bool
     */
    protected function useSigning(AbstractProvider $provider)
    {
        return Saml::getInstance()->getSettings()->signAssertions;
    }

    /**
     * @param KeyChainRecord|null $withKeyPair
     * @param bool $createKeyFromSettings
     * @return Provider
     * @throws InvalidMetadata
     * @throws \Exception
     */
    public function create(KeyChainRecord $withKeyPair = null, $createKeyFromSettings = false): ProviderInterface
    {
        if (! $withKeyPair && $createKeyFromSettings) {
            $withKeyPair = (new OpenSSL(Saml::getInstance()->getSettings()->defaultOpenSSLValues))->create();
            $withKeyPair->save();
        }


        $spRedirectDescriptor = $this->createRedirectDescriptor()
            ->addSingleLogoutService(
                (new SingleLogoutService())
                    ->setLocation(static::getLogoutRequestLocation())
                    ->setResponseLocation(static::getLogoutResponseLocation())
                    ->setBinding(SamlConstants::BINDING_SAML2_HTTP_REDIRECT)
            );
        $spPostDescriptor = $this->createPostDescriptor()
            ->addSingleLogoutService(
                (new SingleLogoutService())
                    ->setLocation(static::getLogoutRequestLocation())
                    ->setResponseLocation(static::getLogoutResponseLocation())
                    ->setBinding(SamlConstants::BINDING_SAML2_HTTP_POST)
            );

        $entityDescriptor = new EntityDescriptor(
            Saml::getInstance()->getSettings()->getEntityId(),
            [
                $spRedirectDescriptor,
                $spPostDescriptor,
            ]);

        $provider = (new ProviderRecord())
            ->loadDefaultValues();

        if ($withKeyPair) {
            if ($this->useEncryption($provider)) {
                $this->setEncrypt($spRedirectDescriptor, $withKeyPair);
                $this->setEncrypt($spPostDescriptor, $withKeyPair);
            }
            if ($this->useSigning($provider)) {
                $this->setSign($spRedirectDescriptor, $withKeyPair);
                $this->setSign($spPostDescriptor, $withKeyPair);
            }
        }

        \Craft::configure($provider, [
            'entityId' => $entityDescriptor->getEntityID(),
            'metadata' => SerializeHelper::toXml($entityDescriptor),
        ]);

        if (! $this->saveProvider($provider)) {
            throw new \Exception($provider->getFirstError());
        }

        return $provider;
    }

    /**
     * @return SpSsoDescriptor
     * @throws InvalidMetadata
     */
    public function createRedirectDescriptor()
    {
        return $this->createDescriptor(SamlConstants::BINDING_SAML2_HTTP_REDIRECT);
    }

    /**
     * @return SpSsoDescriptor
     * @throws InvalidMetadata
     */
    public function createPostDescriptor()
    {
        return $this->createDescriptor(SamlConstants::BINDING_SAML2_HTTP_POST);
    }

    /**
     * @param $binding
     * @return SpSsoDescriptor
     * @throws InvalidMetadata
     */
    public function createDescriptor($binding)
    {
        if (! in_array($binding, $this->getSupportedBindings())) {
            throw new InvalidMetadata(
                sprintf("Binding is not supported: %s", $binding)
            );
        }

        $spDescriptor = (new SpSsoDescriptor())
            ->setWantAssertionsSigned(Saml::getInstance()->getSettings()->signAssertions);

//        $spDescriptor->addNameIDFormat(
//            SamlConstants::NAME_ID_FORMAT_EMAIL
//        );
//        $spDescriptor->addNameIDFormat(
//            SamlConstants::NAME_ID_FORMAT_X509_SUBJECT_NAME
//        );

        $acs = new AssertionConsumerService();
        $acs->setBinding($binding)
            ->setLocation(static::getLoginLocation());
        $spDescriptor->addAssertionConsumerService($acs);

        return $spDescriptor;

    }

    /**
     * Utils
     */

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

}