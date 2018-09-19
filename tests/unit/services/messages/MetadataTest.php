<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\services\messages\Metadata;

class MetadataTest extends Unit
{
    public function testConstants()
    {
        $this->assertEquals(Metadata::LOGIN_LOCATION, 'sso/login');
        $this->assertEquals(Metadata::LOGOUT_RESPONSE_LOCATION, 'sso/logout');
        $this->assertEquals(Metadata::LOGOUT_REQUEST_LOCATION, 'sso/logout/request');
    }
}