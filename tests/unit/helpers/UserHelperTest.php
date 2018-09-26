<?php

namespace flipbox\saml\sp\tests\helpers;

use Craft;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class UserHelperTest extends Unit
{

    private $user;

    private function newUser()
    {
        if (! $this->user) {
            $this->user = new User([
                'username'  => 'test@test.com',
                'email'     => 'test@test.com',
                'suspended' => 1,
            ]);

            Craft::$app->elements->saveElement($this->user);
        }

        return $this->user;
    }

    public function testEnablingSuspendedUser()
    {

        $user = $this->newUser();
        UserHelper::enableUser($user);

        // TODO lookup $user->id again
        $this->assertFalse($user->suspended);
    }

//    public function testEnablingLockedUser()
//    {
//        $user = new User([
//            'locked' => 1,
//        ]);
//
//        UserHelper::enableUser($user);
//
//        // TODO lookup $user->id again
//        $this->assertFalse($user->locked);
//    }
//
//    // Not enabled
//    public function testEnablingDisabledUser()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'enabled' => 0,
//            ])
//        );
//
//        UserHelper::enableUser($user);
//
//        // TODO lookup $user->id again
//        $this->assertTrue($user->enabled);
//    }
//
//    public function testEnablingArchived()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'archived' => 1,
//            ])
//        );
//
//        UserHelper::enableUser($user);
//
//        // TODO lookup $user->id again
//        $this->assertFalse($user->archived);
//    }
//
//    // Not active
//    public function testEnablingInactiveUser()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'pending' => 1,
//            ])
//        );
//
//        UserHelper::enableUser($user);
//
//        // TODO lookup $user->id again
//        $this->assertTrue($user->pending);
//    }
//
//    public function testIsUserPending()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'pending' => 1,
//            ])
//        );
//
//        $this->assertTrue(UserHelper::isUserPending($user));
//    }
//
//    public function testIsUserArchived()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'archived' => 1,
//            ])
//        );
//
//        $this->assertTrue(UserHelper::isUserArchived($user));
//    }
//
//    public function testIsUserLocked()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'locked' => 1,
//            ])
//        );
//
//        $this->assertTrue(UserHelper::isUserLocked($user));
//    }
//
//    public function testIsUserSuspended()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'suspended' => 1,
//            ])
//        );
//
//        $this->assertTrue(UserHelper::isUserSuspended($user));
//    }
//
//    public function testIsUserActive()
//    {
//        \Craft::$app->elements->saveElement(
//            $user = new User([
//                'active' => 1,
//            ])
//        );
//
//        $this->assertTrue(UserHelper::isUserActive($user));
//    }
}