<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\services\ProviderIdentity;

class ProviderIdentityTest extends Unit
{
    public function testGetRecordClass()
    {
        $this->assertEquals(ProviderIdentity::class, (new ProviderIdentity)->getRecordClass());
    }
}