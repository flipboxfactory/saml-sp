<?php

namespace flipbox\saml\sp\tests\unit\misc;

use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class UserHelperTest extends Unit
{
    use ConsoleFixtures;

    public function testActiveUserIsLeftAlone(): void
    {
        $user = $this->saveUser(['active' => true]);

        $this->assertTrue(UserHelper::isUserActive($user));
        $this->assertFalse(UserHelper::isUserPending($user));
        $this->assertFalse(UserHelper::isUserSuspended($user));
        $this->assertFalse(UserHelper::isUserArchived($user));
        $this->assertFalse(UserHelper::isUserLocked($user));

        UserHelper::enableUser($user);

        $this->assertSame(User::STATUS_ACTIVE, $this->reloadUser($user)->getStatus());
    }

    public function testPendingUserIsActivated(): void
    {
        $user = $this->saveUser(['pending' => true]);
        $this->assertTrue(UserHelper::isUserPending($user));

        UserHelper::enableUser($user);

        $reloaded = $this->reloadUser($user);
        $this->assertSame(User::STATUS_ACTIVE, $reloaded->getStatus());
        $this->assertFalse((bool)$reloaded->pending);
    }

    public function testInactiveUserIsActivated(): void
    {
        $user = $this->saveUser();
        $this->assertSame(User::STATUS_INACTIVE, $user->getStatus());
        $this->assertFalse(UserHelper::isUserActive($user));

        UserHelper::enableUser($user);

        $this->assertSame(User::STATUS_ACTIVE, $this->reloadUser($user)->getStatus());
    }

    public function testSuspendedUserIsUnsuspended(): void
    {
        $user = $this->saveUser(['active' => true]);
        \Craft::$app->getUsers()->suspendUser($user);
        $user = $this->reloadUser($user);
        $this->assertTrue(UserHelper::isUserSuspended($user));

        UserHelper::enableUser($user);

        $reloaded = $this->reloadUser($user);
        $this->assertFalse((bool)$reloaded->suspended);
        $this->assertSame(User::STATUS_ACTIVE, $reloaded->getStatus());
    }

    public function testDisabledUserIsReEnabled(): void
    {
        $user = $this->saveUser(['active' => true, 'enabled' => false]);
        $this->assertFalse(UserHelper::isUserActive($user));

        UserHelper::enableUser($user);

        // enableUser() only changes the element in memory; callers save it
        $this->assertTrue($user->enabled);
        $this->assertTrue(UserHelper::isUserActive($user));
    }

    public function testArchivedUserIsRestored(): void
    {
        $user = $this->saveUser(['active' => true]);
        $user->archived = true;
        $this->assertTrue(UserHelper::isUserArchived($user));

        UserHelper::enableUser($user);

        $this->assertFalse($user->archived);
        $this->assertTrue(UserHelper::isUserActive($user));
    }
}
