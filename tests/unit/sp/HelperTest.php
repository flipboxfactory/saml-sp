<?php


namespace tests\unit\sp;


use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\helpers\UserHelper;

class HelperTest extends Unit
{
    /** @var User */
    private $user;

    protected function _before()
    {
        parent::_before();
        $this->user = new User([
            'email' => 'test@example.com',
            'username' => 'tester',
            'firstName' => 'Test First Name',
            'lastName' => 'Test Last Name',
        ]);
    }

    public function testUserHelper()
    {
//        $this->assertTrue(
//            UserHelper::isUserActive(
//                $this->user
//            )
//        );

        $this->assertFalse(
            UserHelper::isUserArchived(
                $this->user
            )
        );
        $this->assertFalse(
            UserHelper::isUserLocked(
                $this->user
            )
        );
        $this->assertFalse(
            UserHelper::isUserSuspended(
                $this->user
            )
        );
        $this->assertFalse(
            UserHelper::isUserPending(
                $this->user
            )
        );

    }
}