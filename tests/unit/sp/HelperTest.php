<?php


namespace tests\unit\sp;


use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class HelperTest extends Unit
{

    public function testUserHelper()
    {
        \Craft::$app->elements->saveElement(
            $user = new User([
                'email' => 'test@example.com',
                'username' => 'tester',
                'firstName' => 'Test First Name',
                'lastName' => 'Test Last Name',
            ])
        );

        UserHelper::enableUser($user);
        $this->assertTrue(
            UserHelper::isUserActive(
                $user
            )
        );
        $this->assertFalse(
            UserHelper::isUserArchived(
                $user
            )
        );
        $this->assertFalse(
            UserHelper::isUserLocked(
                $user
            )
        );
        $this->assertFalse(
            UserHelper::isUserSuspended(
                $user
            )
        );
        $this->assertFalse(
            UserHelper::isUserPending(
                $user
            )
        );

    }
}