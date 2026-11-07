<?php

namespace flipbox\saml\sp\tests\traits;

use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\traits\SamlPluginEnsured;

class SamlPluginEnsuredTest extends Unit
{
    use SamlPluginEnsured;

    public function testGetSamlPlugin()
    {
        Saml::setInstance(new Saml('saml_sp'));
        $this->assertInstanceOf(Saml::class, $this->getPlugin());
    }
}
