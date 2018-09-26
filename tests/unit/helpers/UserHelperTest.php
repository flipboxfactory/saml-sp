<?php

namespace flipbox\saml\sp\tests\helpers;

use Craft;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class UserHelperTest extends Unit
{

    public function testEnablingSuspendedUser()
    {

        $user = new User([
            'username'  => 'test+new-user1@test.com',
            'email'     => 'test+new-user1@test.com',
            'suspended' => true,
        ]);

        Craft::$app->elements->saveElement($user);
        UserHelper::enableUser($user);

        // TODO lookup $user->id again
        $this->assertFalse($user->suspended);
    }

    // Not enabled
    public function testEnablingDisabledUser()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'username'  => 'test+new-user3@test.com',
                'email'     => 'test+new-user3@test.com',
                'enabled' => 0,
            ])
        );

        UserHelper::enableUser($user);

        // TODO lookup $user->id again
        $this->assertTrue($user->enabled);
    }

    public function testEnablingArchived()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'username'  => 'test+new-user4@test.com',
                'email'     => 'test+new-user4@test.com',
                'archived' => true,
            ])
        );

        UserHelper::enableUser($user);

        // TODO lookup $user->id again
        $this->assertFalse($user->archived);
    }

}