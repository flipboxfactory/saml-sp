<?php

namespace flipbox\saml\sp\tests\unit\coverage\services;

use Codeception\Test\Unit;
use craft\elements\User as UserElement;
use craft\events\ElementEvent;
use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\models\FieldLayoutTab;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\ClaimTypes;
use flipbox\saml\sp\events\UserQueryCriteria;
use flipbox\saml\sp\helpers\UserHelper;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\services\login\User as UserService;
use yii\base\UserException;

class UserServiceTest extends Unit
{
    use \ServiceFixtures;
    use \ScopedEvents;

    protected function _before(): void
    {
        $this->setUpFixtures();
    }

    protected function _after(): void
    {
        $this->offScopedEvents();
        \Craft::$app->getUser()->logout(false);
        $this->tearDownFixtures();
    }

    public function testMatchesExistingUserByEmail(): void
    {
        $existing = $this->savedUser('local-account', $this->responseFactory->getEmail());
        $idp = $this->idp();
        $sp = $this->sp();

        $user = (new UserService())->getByResponse($this->signedResponse($idp, $sp), $sp, $idp, $this->settings());

        $this->assertSame($existing->id, $user->id);
    }

    public function testMatchesExistingUserByUsername(): void
    {
        $existing = $this->savedUser($this->responseFactory->getEmail(), 'other@example.com');
        $idp = $this->idp();
        $sp = $this->sp();

        $user = (new UserService())->getByResponse($this->signedResponse($idp, $sp), $sp, $idp, $this->settings());

        $this->assertSame($existing->id, $user->id);
    }

    public function testUnknownUserIsCreatedUnsavedWhenAllowed(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();

        $user = (new UserService())->getByResponse(
            $this->signedResponse($idp, $sp),
            $sp,
            $idp,
            $this->settings(['createUser' => true])
        );

        $this->assertNull($user->id);
        $this->assertSame($this->responseFactory->getEmail(), $user->username);
    }

    public function testUnknownUserIsRejectedWhenCreationDisabled(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("System doesn't have permission to create a new user.");

        (new UserService())->getByResponse(
            $this->signedResponse($idp, $sp),
            $sp,
            $idp,
            $this->settings(['createUser' => false])
        );
    }

    public function testMissingNameIdIsRejected(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setNameId(null);

        $this->expectException(InvalidMessage::class);
        $this->expectExceptionMessage('Name ID is missing.');

        (new UserService())->getByResponse($response, $sp, $idp, $this->settings(['nameIdAttributeOverride' => null]));
    }

    public function testProviderNameIdOverrideUsesAttribute(): void
    {
        $idp = $this->idp();
        $idp->nameIdOverride = 'employeeId';
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setAttributes(['employeeId' => ['emp-42']]);

        $user = (new UserService())->getByResponse($response, $sp, $idp, $this->settings(['nameIdAttributeOverride' => null]));

        $this->assertSame('emp-42', $user->username);
    }

    public function testCustomCriteriaEventReplacesDefaultLookup(): void
    {
        $target = $this->savedUser('matched-by-event', 'event@example.com');
        $this->onEvent(
            UserService::class,
            UserService::EVENT_GET_CUSTOM_USER_CRITERIA,
            function(UserQueryCriteria $event) use ($target) {
                $this->assertSame($this->responseFactory->getEmail(), $event->usernameOrEmail);
                $event->applyDefaultCriteria = false;
                $event->userQuery->id($target->id);
            }
        );
        $idp = $this->idp();
        $sp = $this->sp();

        $user = (new UserService())->getByResponse($this->signedResponse($idp, $sp), $sp, $idp, $this->settings());

        $this->assertSame($target->id, $user->id);
    }

    public function testSyncMapsAttributesSavesUserAndFiresEvent(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $events = [];
        $service = new UserService();
        $service->on(UserService::EVENT_BEFORE_USER_SAVE, function(ElementEvent $event) use (&$events) {
            $events[] = $event->isNew;
        });
        $user = new UserElement(['username' => $this->responseFactory->getEmail()]);

        $service->sync($user, $this->signedResponse($idp, $sp), $idp, $sp, $this->settings());

        $this->assertNotNull($user->id);
        $this->assertSame([true], $events);
        $this->assertSame($this->responseFactory->getEmail(), $user->email);
        $this->assertSame($this->responseFactory->getFirstName(), $user->firstName);
        $this->assertSame($this->responseFactory->getLastName(), $user->lastName);
    }

    public function testSyncThrowsWhenUserCannotBeSaved(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setAttributes([ClaimTypes::EMAIL_ADDRESS => ['not-an-email']]);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User save failed');

        (new UserService())->sync(new UserElement(['username' => 'bad-email']), $response, $idp, $sp, $this->settings());
    }

    public function testInactiveUserIsDeniedWhenEnablingIsOff(): void
    {
        $user = $this->savedUser('suspended@example.com');
        \Craft::$app->getUsers()->suspendUser($user);
        $idp = $this->idp();
        $sp = $this->sp();

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User access denied.');

        (new UserService())->sync($user, $this->signedResponse($idp, $sp), $idp, $sp, $this->settings(['enableUsers' => false]));
    }

    public function testInactiveUserIsReEnabledWhenAllowed(): void
    {
        $user = $this->savedUser('suspended2@example.com');
        \Craft::$app->getUsers()->suspendUser($user);
        $this->assertFalse(UserHelper::isUserActive($user));
        $idp = $this->idp();
        $sp = $this->sp();

        (new UserService())->sync($user, $this->signedResponse($idp, $sp), $idp, $sp, $this->settings(['enableUsers' => true]));

        $reloaded = \Craft::$app->getUsers()->getUserById($user->id);
        $this->assertTrue(UserHelper::isUserActive($reloaded));
    }

    public function testAssertionWithoutAttributesFallsBackToUsernameAsEmail(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setAttributes([]);
        $user = new UserElement(['username' => 'fallback@example.com']);

        (new UserService())->sync($user, $response, $idp, $sp, $this->settings());

        $this->assertSame('fallback@example.com', $user->email);
    }

    public function testSettingsMapWithCallableIsUsedWhenProviderHasNoMapping(): void
    {
        $idp = $this->idp();
        $idp->setMapping([]);
        $sp = $this->sp();
        $received = null;
        $settings = $this->settings([
            'responseAttributeMap' => [
                ClaimTypes::EMAIL_ADDRESS => 'email',
                'groups' => function(UserElement $user, array $attribute) use (&$received) {
                    $received = $attribute;
                    $user->firstName = 'From callable';
                },
            ],
        ]);
        $user = new UserElement(['username' => 'callable@example.com']);

        (new UserService())->sync($user, $this->signedResponse($idp, $sp), $idp, $sp, $settings);

        $this->assertSame($this->responseFactory->getEmail(), $user->email);
        $this->assertSame('From callable', $user->firstName);
        // callables get every value, not just the first
        $this->assertSame(['groups' => $this->responseFactory->getGroups()], $received);
    }

    public function testAttributeMappedToCustomField(): void
    {
        $field = new PlainText(['name' => 'Department', 'handle' => 'department']);
        $this->assertTrue(\Craft::$app->getFields()->saveField($field));
        $layout = \Craft::$app->getFields()->getLayoutByType(UserElement::class);
        $tab = new FieldLayoutTab(['layout' => $layout, 'name' => 'Content']);
        $tab->setElements([new CustomField($field)]);
        $layout->setTabs([$tab]);
        $this->assertTrue(\Craft::$app->getUsers()->saveLayout($layout));

        $idp = $this->idp();
        $idp->setMapping([['attributeName' => 'dept', 'craftProperty' => 'department']]);
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setAttributes(['dept' => ['Security', 'Ignored']]);
        $user = new UserElement(['username' => 'field@example.com', 'email' => 'field@example.com']);

        (new UserService())->sync($user, $response, $idp, $sp, $this->settings());

        $this->assertSame('Security', $user->getFieldValue('department'));
    }

    public function testLoginActivatesPendingUserAndStartsSession(): void
    {
        $user = new UserElement(['username' => 'pending@example.com', 'email' => 'pending@example.com', 'pending' => true]);
        $this->assertTrue(\Craft::$app->getElements()->saveElement($user));
        $this->assertSame(UserElement::STATUS_PENDING, $user->getStatus());
        $identity = new ProviderIdentityRecord(['userId' => $user->id]);

        // CLI test requests have no user agent/IP, which Craft otherwise requires for a session
        $general = \Craft::$app->getConfig()->getGeneral();
        $requireUa = $general->requireUserAgentAndIpForSession;
        $general->requireUserAgentAndIpForSession = false;
        try {
            $this->assertTrue((new UserService())->login($identity));
        } finally {
            $general->requireUserAgentAndIpForSession = $requireUa;
        }

        $this->assertSame($user->id, \Craft::$app->getUser()->getId());
        // Craft's ActiveRecord stores dates as UTC DB strings
        $this->assertEqualsWithDelta(time(), (new \DateTime($identity->lastLoginDate, new \DateTimeZone('UTC')))->getTimestamp(), 60);
        $this->assertSame(
            UserElement::STATUS_ACTIVE,
            \Craft::$app->getUsers()->getUserById($user->id)->getStatus()
        );
    }
}
