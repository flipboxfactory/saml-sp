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

        $this->assertIsBool($settings->enableCpLoginButtons);
        $this->assertIsBool($settings->enableUsers);
        $this->assertIsBool($settings->signAuthnRequest);
        $this->assertIsBool($settings->wantsSignedAssertions);
        $this->assertIsBool($settings->mergeLocalUsers);
        $this->assertIsBool($settings->createUser);
        $this->assertIsBool($settings->autoCreateGroups);
        $this->assertIsBool($settings->syncGroups);
        $this->assertIsArray($settings->groupAttributeNames);
        $this->assertIsArray($settings->defaultGroupAssignments);
        $this->assertIsArray($settings->responseAttributeMap);
        $this->assertIsString($settings->relayStateOverrideParam);
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

        $this->assertIsString($settings->getEntityId());
    }

    public function testEntityIdSetter()
    {
        $settings = new Settings();

        $settings->setEntityId($entityId = 'some-entity');

        $this->assertIsString($settings->getEntityId());

        $this->assertEquals($entityId, $settings->getEntityId());
    }
}
