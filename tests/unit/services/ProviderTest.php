<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Provider;

class ProviderTest extends Unit
{
    public function testGetRecordClass()
    {
        $this->assertEquals(ProviderRecord::class, Saml::getInstance()->getProviderRecordClass());
    }
}