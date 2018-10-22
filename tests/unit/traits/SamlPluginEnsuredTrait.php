<?php

namespace flipbox\saml\sp\tests\traits;

use Codeception\Test\Unit;
use flipbox\saml\sp\traits\SamlPluginEnsured;
use flipbox\saml\sp\Saml;

class SamlPluginEnsuredTrait extends Unit
{
    use SamlPluginEnsured;

    public function testGetSamlPlugin()
    {
        $this->assertEquals(Saml::getInstance(), $this->getSamlPlugin());
    }

}