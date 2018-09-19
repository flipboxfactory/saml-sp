<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\services\messages\AuthnRequest;

class AuthnRequestTest extends Unit
{
    public function testConstants()
    {
        $this->assertEquals(
            AuthnRequest::EVENT_AFTER_MESSAGE_CREATED,
            'eventAfterMessageCreated'
        );
    }
}