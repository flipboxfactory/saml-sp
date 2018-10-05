<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\messages\LogoutRequest;

class LogoutRequestTest extends Unit
{
    public function testGetSamlPlugin()
    {
        Saml::setInstance(new Saml('saml_sp'));
        $this->assertInstanceOf(Saml::class, (new LogoutRequest)->getSamlPlugin());
    }
}