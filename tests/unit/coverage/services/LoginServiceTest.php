<?php

namespace flipbox\saml\sp\tests\unit\coverage\services;

use Codeception\Test\Unit;
use craft\elements\User as UserElement;
use flipbox\saml\sp\events\UserLogin;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\services\Login;

class LoginServiceTest extends Unit
{
    use \ServiceFixtures;

    private bool $requireUa;

    protected function _before(): void
    {
        $this->setUpFixtures();
        // CLI test requests have no user agent/IP, which Craft otherwise requires for a session
        $general = \Craft::$app->getConfig()->getGeneral();
        $this->requireUa = $general->requireUserAgentAndIpForSession;
        $general->requireUserAgentAndIpForSession = false;
    }

    protected function _after(): void
    {
        \Craft::$app->getUser()->logout(false);
        \Craft::$app->getConfig()->getGeneral()->requireUserAgentAndIpForSession = $this->requireUa;
        $this->tearDownFixtures();
    }

    public function testTransformToUserFiresEventsAroundSync(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $user = new UserElement(['username' => $this->responseFactory->getEmail()]);
        $calls = [];
        $login = new Login();
        $login->on(Login::EVENT_BEFORE_RESPONSE_TO_USER, function(UserLogin $event) use (&$calls, $user, $response) {
            $this->assertSame($user, $event->user);
            $this->assertSame($response, $event->response);
            $calls[] = 'before:' . ($event->user->id ? 'saved' : 'new');
        });
        $login->on(Login::EVENT_AFTER_RESPONSE_TO_USER, function(UserLogin $event) use (&$calls) {
            $calls[] = 'after:' . ($event->user->id ? 'saved' : 'new');
        });

        $returned = $login->transformToUser($user, $response, $idp, $sp, $this->settings());

        $this->assertSame($user, $returned);
        $this->assertSame(['before:new', 'after:saved'], $calls);
        $this->assertSame($this->responseFactory->getFirstName(), $user->firstName);
    }

    public function testByIdentityLogsInAndRecordsLastLogin(): void
    {
        $identity = $this->savedIdentity('identity-login@example.com');

        (new Login())->byIdentity($identity);

        $this->assertSame((int)$identity->userId, (int)\Craft::$app->getUser()->getId());
        $stored = ProviderIdentityRecord::findOne($identity->id);
        $this->assertNotEmpty($stored->lastLoginDate);
    }

    public function testByIdentityFailsWhenIdentityCannotBeSaved(): void
    {
        $identity = $this->savedIdentity('identity-invalid@example.com');
        $identity->providerId = null;

        $this->expectException(\Exception::class);

        (new Login())->byIdentity($identity);
    }

    private function savedIdentity(string $email): ProviderIdentityRecord
    {
        $user = new UserElement(['username' => $email, 'email' => $email, 'active' => true]);
        $this->assertTrue(\Craft::$app->getElements()->saveElement($user));

        $idp = $this->idp();
        $this->module->getProvider()->save($idp);

        $identity = new ProviderIdentityRecord([
            'providerId' => $idp->id,
            'nameId' => $email,
            'userId' => $user->id,
            'enabled' => true,
            'lastLoginDate' => new \DateTime('-1 day'),
        ]);
        $this->module->getProviderIdentity()->save($identity);

        return $identity;
    }
}
