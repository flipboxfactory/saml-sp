<?php

namespace flipbox\saml\sp\tests\helpers;

use Craft;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class UserHelperTest extends Unit
{
    public function testIsUserPending()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'pending' => 1,
            ])
        );

        $this->assertTrue(UserHelper::isUserPending($user));
    }

    public function testIsUserArchived()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'archived' => 1,
            ])
        );

        $this->assertTrue(UserHelper::isUserArchived($user));
    }

    public function testIsUserLocked()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'locked' => 1,
            ])
        );

        $this->assertTrue(UserHelper::isUserLocked($user));
    }

    public function testIsUserSuspended()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'suspended' => 1,
            ])
        );

        $this->assertTrue(UserHelper::isUserSuspended($user));
    }

    public function testIsUserActive()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'active' => 1,
            ])
        );

        $this->assertTrue(UserHelper::isUserActive($user));
    }
}