<?php

namespace flipbox\saml\sp\tests\unit\coverage\services;

use Codeception\Test\Unit;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\sp\events\AuthnRequest as AuthnRequestEvent;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\services\messages\AuthnRequest;
use SAML2\Constants;
use SAML2\XML\md\IDPSSODescriptor;

class AuthnRequestServiceTest extends Unit
{
    use \ServiceFixtures;

    protected function _before(): void
    {
        $this->setUpFixtures();
    }

    protected function _after(): void
    {
        $this->tearDownFixtures();
    }

    public function testPrefersPostBindingAndAddressesSpAndIdp(): void
    {
        $sp = $this->sp();
        $idp = $this->idp();
        $service = new AuthnRequest();
        $events = [];
        $service->on(AuthnRequest::EVENT_AFTER_MESSAGE_CREATED, function(AuthnRequestEvent $event) use (&$events) {
            $events[] = $event->message;
        });

        $request = $service->create($sp, $idp);

        $this->assertSame(Constants::BINDING_HTTP_POST, $request->getProtocolBinding());
        $this->assertSame($idp->firstIdpSsoService(Constants::BINDING_HTTP_POST)->getLocation(), $request->getDestination());
        $this->assertSame($sp->getEntityId(), $request->getIssuer()->getValue());
        $this->assertSame($sp->getLoginEndpoint(), $request->getAssertionConsumerServiceURL());
        $this->assertMatchesRegularExpression('/^_[0-9a-f]+$/', $request->getId());
        $this->assertSame([$request], $events);
    }

    public function testFallsBackToFirstSsoServiceWithoutPostBinding(): void
    {
        $idp = $this->idpWithSsoServices([Constants::BINDING_HTTP_REDIRECT]);

        $request = (new AuthnRequest())->create($this->sp(), $idp);

        $this->assertSame(Constants::BINDING_HTTP_REDIRECT, $request->getProtocolBinding());
    }

    public function testIdpWithoutSsoServiceIsRejected(): void
    {
        $idp = $this->idpWithSsoServices([]);

        $this->expectException(InvalidMetadata::class);
        $this->expectExceptionMessage('IdP Metadata is missing SSO Service');

        (new AuthnRequest())->create($this->sp(), $idp);
    }

    public function testSignedWhenEnabledAndSpHasKey(): void
    {
        $this->settings(['signAuthnRequest' => true]);

        $request = (new AuthnRequest())->create($this->sp(), $this->idp());

        $this->assertNotNull($request->getSignatureKey());
    }

    public function testNotSignedWhenDisabled(): void
    {
        $this->settings(['signAuthnRequest' => false]);

        $request = (new AuthnRequest())->create($this->sp(), $this->idp());

        $this->assertNull($request->getSignatureKey());
    }

    /**
     * @param string[] $bindings SSO bindings to keep from the fixture IdP metadata
     */
    private function idpWithSsoServices(array $bindings): ProviderRecord
    {
        $descriptor = $this->metadataFactory->createIdpEntityDescriptorWithKeyDescriptors();
        foreach ($descriptor->getRoleDescriptor() as $role) {
            if ($role instanceof IDPSSODescriptor) {
                $role->setSingleSignOnService(array_values(array_filter(
                    $role->getSingleSignOnService(),
                    fn($service) => in_array($service->getBinding(), $bindings, true)
                )));
            }
        }

        return $this->metadataFactory->createIdpProviderWithEntityDescriptor($this->module, $descriptor);
    }
}
