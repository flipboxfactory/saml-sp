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
use flipbox\saml\sp\models\Provider;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\SamlConstants;
use flipbox\saml\sp\Saml;

class Metadata extends Component
{

    use \flipbox\saml\core\services\traits\Metadata;

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
     * @var array
     */
    protected $supportedBindings = [
        SamlConstants::BINDING_SAML2_HTTP_REDIRECT,
        SamlConstants::BINDING_SAML2_HTTP_POST,
    ];

    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }

    /**
     * @return Provider
     * @throws InvalidMetadata
     * @throws \Exception
     */
    public function create()
    {

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

        $keyPair = (new OpenSSL(Saml::getInstance()->getSettings()->defaultOpenSSLValues))->create();
        $keyPair->save();

        $this->setEncrypt($spRedirectDescriptor, $keyPair);
        $this->setEncrypt($spPostDescriptor, $keyPair);
        $this->setSign($spRedirectDescriptor,$keyPair);
        $this->setSign($spPostDescriptor, $keyPair);

        $provider = new Provider([
            'metadata' => $entityDescriptor,
            'localKeyId' => $keyPair->id,
        ]);

        if (! Saml::getInstance()->getProvider()->save($provider)) {
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

        $spDescriptor->addNameIDFormat(
            SamlConstants::NAME_ID_FORMAT_EMAIL
        );
        $spDescriptor->addNameIDFormat(
            SamlConstants::NAME_ID_FORMAT_X509_SUBJECT_NAME
        );

        $acs = new AssertionConsumerService();
        $acs->setBinding($binding)
            ->setLocation(static::getLoginLocation());
        $spDescriptor->addAssertionConsumerService($acs);

//        $this->setEncrypt($spDescriptor);
//        $this->setSign($spDescriptor);

        return $spDescriptor;

    }

    /**
     * @param SpSsoDescriptor $spSsoDescriptor
     * @param KeyChainRecord $keyChainRecord
     */
    public function setSign(SpSsoDescriptor $spSsoDescriptor, KeyChainRecord $keyChainRecord)
    {
        if (Saml::getInstance()->getSettings()->signAssertions) {

            $spSsoDescriptor->addKeyDescriptor(
                $keyDescriptor = (new KeyDescriptor())
                    ->setUse(KeyDescriptor::USE_SIGNING)
                    ->setCertificate((new X509Certificate())->loadPem($keyChainRecord->certificate))
            );
        }

    }

    /**
     * @param SpSsoDescriptor $spSsoDescriptor
     * @param KeyChainRecord $keyChainRecord
     */
    public function setEncrypt(SpSsoDescriptor $spSsoDescriptor, KeyChainRecord $keyChainRecord)
    {

        if (Saml::getInstance()->getSettings()->encryptAssertions) {
            $spSsoDescriptor->addKeyDescriptor(
                $keyDescriptor = (new KeyDescriptor())
                    ->setUse(KeyDescriptor::USE_ENCRYPTION)
                    ->setCertificate((new X509Certificate())->loadPem($keyChainRecord->certificate))
            );

        }
    }

}