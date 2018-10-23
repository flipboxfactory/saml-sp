<?php

namespace flipbox\saml\sp\tests\helpers;

use Craft;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class UserHelperTest extends Unit
{
    private $suspendedUser;
    private $lockedUser;
    private $disabledUser;
    private $archivedUser;
    private $activeUser;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
/*    protected function _before()
    {
        $this->suspendedUser = $this->createSuspendedUser();
        $this->lockedUser = $this->createLockedUser();
        $this->disabledUser = $this->createDisabledUser();
        $this->archivedUser = $this->createArchivedUser();
        $this->activeUser = $this->createActiveUser();
    }

    public function testEnablingSuspendedUser()
    {
        UserHelper::enableUser($this->suspendedUser);

        $this->assertEquals(0, $this->suspendedUser->suspended);
        $this->assertEquals(0, $this->suspendedUser->enabled);
    }

    public function testEnablingLockedUser()
    {
        UserHelper::enableUser($this->lockedUser);

        $this->assertEquals(0, $this->lockedUser->locked);
        $this->assertEquals(1, $this->lockedUser->enabled);
    }

    public function testEnablingDisabledUser()
    {
        UserHelper::enableUser($this->disabledUser);

        $this->assertEquals(1, $this->disabledUser->enabled);
    }

    public function testEnablingArchivedUser()
    {
        UserHelper::enableUser($this->archivedUser);

        $this->assertEquals(1, $this->archivedUser->archived);
        $this->assertEquals(1, $this->archivedUser->enabled);
    }

    public function testEnablingActiveUser()
    {
        UserHelper::enableUser($this->activeUser);

        $this->assertEquals(1, $this->activeUser->active);
        $this->assertEquals(1, $this->activeUser->enabled);
    }

    public function testIsUserSuspended()
    {
        $this->assertEquals(1, $this->suspendedUser->suspended);
        $this->assertEquals(0, $this->lockedUser->suspended);
    }

    public function testIsUserLocked()
    {
        $this->assertEquals(1, $this->lockedUser->locked);
        $this->assertEquals(0, $this->activeUser->locked);
    }

    public function testIsUserPending()
    {
        $this->assertEquals(1, $this->disabledUser->pending);
        $this->assertEquals(0, $this->lockedUser->pending);
    }

    public function testIsUserArchived()
    {
        $this->assertEquals(1, $this->archivedUser->archived);
        $this->assertEquals(0, $this->lockedUser->archived);
    }

    public function testIsUserActive()
    {
        $this->assertEquals(1, $this->activeUser->active);
        $this->assertEquals(0, $this->lockedUser->active);
    }


    private function fakeEmail()
    {
        return 'test+' . random_int(1, 10000000) . '@test.com';
    }

    private function createSuspendedUser()
    {
        $user = $this->createUser();
        Craft::$app->getUsers()->suspendUser($user);

        return $user;
    }

    private function createLockedUser()
    {
        return $this->createUser('locked', 1);
    }

    private function createDisabledUser()
    {
        return $this->createUser('pending', 1);
    }

    private function createArchivedUser()
    {
        return $this->createUser('archived', 1);
    }

    private function createActiveUser()
    {
        return $this->createUser('active', 1);
    }

    private function createUser($status, $value)
    {
        $fakeEmail = $this->fakeEmail();

        \Craft::$app->elements->saveElement(
            $user = new User([
                'username' => $fakeEmail,
                'email' => $fakeEmail,
                $status => $value
            ])
        );

        return $user;
    }
}*/