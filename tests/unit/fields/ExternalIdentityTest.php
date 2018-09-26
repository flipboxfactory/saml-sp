<?php

namespace flipbox\saml\sp\tests\models;

use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\fields\ExternalIdentity;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\Saml;

class ExternalIdentityTest extends Unit
{
    public function testInvalidStaticHtmlValue()
    {
        $field = new ExternalIdentity();

        $invalidValue = [];
        $this->assertEmpty($field->getStaticHtml($invalidValue, new User()));
    }
}
