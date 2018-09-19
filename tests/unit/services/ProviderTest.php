<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\services\Provider;

class ProviderTest extends Unit
{
    public function testGetRecordClass()
    {
        $this->assertEquals(Provider::class, (new Provider)->getRecordClass());
    }
}