<?php

namespace flipbox\saml\sp\tests\models;

use Codeception\Test\Unit;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;

class SettingsTest extends Unit
{
    public function testSettingTypes()
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
        $this->assertInternalType('array', $settings->defaultGroupAssignments);
        $this->assertInternalType('array', $settings->responseAttributeMap);

        $this->assertInternalType('string', $settings->relayStateOverrideParam);
    }

    public function testSettingDefaults()
    {
        $settings = new Settings();

        $this->assertTrue($settings->enableCpLoginButtons);
        $this->assertTrue($settings->enableUsers);
        $this->assertTrue($settings->signAuthnRequest);
        $this->assertTrue($settings->wantsSignedAssertions);
        $this->assertTrue($settings->mergeLocalUsers);
        $this->assertTrue($settings->createUser);
        $this->assertTrue($settings->autoCreateGroups);
        $this->assertTrue($settings->syncGroups);

        $this->assertEquals(['groups'], $settings->groupAttributeNames);
        $this->assertEquals([], $settings->defaultGroupAssignments);
        $this->assertCount(6, $settings->responseAttributeMap);

        $this->assertEquals('RelayState', $settings->relayStateOverrideParam);
    }

    public function testEntityIdGetter()
    {
        $settings = new Settings();

        $this->assertInternalType('string', $settings->getEntityId());
    }

    public function testEntityIdSetter()
    {
        $settings = new Settings();

        $settings->setEntityId($entityId = 'some-entity');

        $this->assertInternalType('string', $settings->getEntityId());

        $this->assertEquals($entityId, $settings->getEntityId());
    }
}
