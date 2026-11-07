<?php

namespace flipbox\saml\sp\tests\unit\coverage\services;

use Codeception\Test\Unit;
use craft\elements\User as UserElement;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\services\ProviderIdentity;

class ProviderIdentityServiceTest extends Unit
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

    public function testExistingIdentityIsReusedAndBoundToUser(): void
    {
        $idp = $this->idp();
        $this->module->getProvider()->save($idp);
        $sp = $this->sp();
        $user = $this->savedUser('identity-owner@example.com');
        $existing = new ProviderIdentityRecord([
            'providerId' => $idp->id,
            'nameId' => $this->responseFactory->getEmail(),
            'userId' => $user->id,
            'enabled' => false,
            'lastLoginDate' => new \DateTime('-1 day'),
        ]);
        $this->module->getProviderIdentity()->save($existing);
        $response = $this->signedResponse($idp, $sp);

        $identity = (new ProviderIdentity())->getByUserAndResponse($user, $response, $sp, $idp);

        $this->assertSame($existing->id, $identity->id);
        $this->assertSame($user->id, $identity->userId);
        $this->assertTrue((bool)$identity->enabled);
        $this->assertSame($response->getAssertions()[0]->getSessionIndex(), $identity->sessionId);
    }

    public function testNewIdentityIsCreatedForUnknownNameId(): void
    {
        $idp = $this->idp();
        $this->module->getProvider()->save($idp);
        $sp = $this->sp();
        $user = new UserElement(['id' => 999999]);

        $identity = (new ProviderIdentity())->getByUserAndResponse($user, $this->signedResponse($idp, $sp), $sp, $idp);

        $this->assertTrue($identity->getIsNewRecord());
        $this->assertSame($this->responseFactory->getEmail(), $identity->nameId);
        $this->assertSame($idp->id, $identity->providerId);
        $this->assertSame(999999, $identity->userId);
    }

    public function testNameIdOverrideAttributeIsUsedWhenNameIdIsMissing(): void
    {
        $idp = $this->idp();
        $idp->nameIdOverride = 'uid';
        $this->module->getProvider()->save($idp);
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setNameId(null);
        $response->getAssertions()[0]->setAttributes(['uid' => ['user-123']]);

        $identity = (new ProviderIdentity())->getByUserAndResponse(new UserElement(), $response, $sp, $idp);

        $this->assertSame('user-123', $identity->nameId);
    }

    public function testMissingNameIdWithoutOverrideFailsClosed(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setNameId(null);

        $identity = null;
        try {
            $identity = (new ProviderIdentity())->getByUserAndResponse(new UserElement(), $response, $sp, $idp);
        } catch (\Throwable) {
            // rejected, as it must be
        }

        $this->assertNull($identity, 'An identity was produced for an assertion without a NameID.');
    }
}
