<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:48 AM
 */

namespace flipbox\saml\sp\services\messages;


use craft\base\Component;
use flipbox\saml\core\helpers\UrlHelper;
use flipbox\keychain\keypair\OpenSSL;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\AbstractMetadata;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\sp\models\Provider;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\transformers\MetadataTransformer;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\SamlConstants;
use flipbox\saml\sp\Saml;

class Metadata extends AbstractMetadata implements MetadataServiceInterface
{

    /**
     *
     */
    const LOGIN_LOCATION = 'saml-sp/login';
    const LOGOUT_RESPONSE_LOCATION = 'saml-sp/logout';
    const LOGOUT_REQUEST_LOCATION = 'saml-sp/logout/request';

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