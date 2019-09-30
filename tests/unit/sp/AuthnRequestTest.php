<?php


namespace tests\unit\sp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use SAML2\AuthnRequest;
use SAML2\Utils;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class AuthnRequestTest extends Unit
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


    public function testAuthnRequestCreateWithoutSignature()
    {
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->metadataFactory->createTheirProvider($this->module);
        $sp = $this->metadataFactory->createMyProvider($this->module);

        $authnRequest = $this->module->getAuthnRequest()->create(
            $sp,
            $idp
        );

        $this->assertInstanceOf(
            AuthnRequest::class,
            $authnRequest
        );

        $sig = Utils::validateElement($authnRequest->toSignedXML());

        $this->assertFalse($sig);

        $this->assertNull(
            $authnRequest->getSignatureKey()
        );

    }

    public function testAuthnRequestCreateWithSignature()
    {
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->metadataFactory->createTheirProviderWithSigningKey($this->module);
        $sp = $this->metadataFactory->createMyProviderWithKey($this->module);

        $authnRequest = $this->module->getAuthnRequest()->create(
            $sp,
            $idp
        );

        $this->assertInstanceOf(
            AuthnRequest::class,
            $authnRequest
        );

        $this->assertNotNull(
            $authnRequest->getSignatureKey()
        );

        // Validate Signature
        $signingKey = $sp->signingXMLSecurityKey();

        $sig = Utils::validateElement($authnRequest->toSignedXML());

        $authnRequest->addValidator(
            [
                Utils::class,
                'validateSignature',
            ],
            $sig
        );

        $this->assertTrue(
            $authnRequest->validate($signingKey)
        );
    }
}