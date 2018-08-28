<?php

namespace flipbox\organizations\tests\models;

use Codeception\Test\Unit;
use flipbox\organizations\models\Settings;
use flipbox\organizations\models\SiteSettings;

class SettingsTest extends Unit
{
    /**
     * Set a state as a string and result in an array
     */
    public function testSiteSettingsClass()
    {
        $settings = new Settings();

        $this->assertEquals(
            $settings::siteSettingsClass(),
            SiteSettings::class
        );
    }
}
