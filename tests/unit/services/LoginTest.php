<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\services\Login;

class LoginTest extends Unit
{
    public function testConstants()
    {
        $this->assertEquals(Login::EVENT_BEFORE_RESPONSE_TO_USER, 'eventBeforeResponseToUser');
        $this->assertEquals(Login::EVENT_AFTER_RESPONSE_TO_USER, 'eventAfterResponseToUser');
    }

}