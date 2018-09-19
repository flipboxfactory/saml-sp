<?php

namespace flipbox\saml\sp\tests\models;

use Codeception\Test\Unit;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;

class SettingsTest extends Unit
{
    public function testVariables()
    {
        $settings = new Settings();

        $this->assertInternalType('bool', $settings->enableCpLoginButtons);
        $this->assertInternalType('bool', $settings->enableUsers);
        $this->assertInternalType('bool', $settings->signAuthnRequest);
        $this->assertInternalType('bool', $settings->wantsSignedAssertions);
        $this->assertInternalType('bool', $settings->mergeLocalUsers);
        $this->assertInternalType('bool', $settings->createUser);
        $this->assertInternalType('bool', $settings->autoCreateGroups);
        $this->assertInternalType('bool', $settings->syncGroups);

        $this->assertInternalType('array', $settings->groupAttributeNames);
        $this->assertInternalType('array', $settings->responseAttributeMap);

        $this->assertInternalType('string', $settings->relayStateOverrideParam);
    }
}
