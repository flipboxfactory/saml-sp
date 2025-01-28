<?php

namespace flipbox\saml\sp\migrations;

use flipbox\saml\sp\records\ProviderRecord;

/**
 * mm190516_200148_attribute_typo migration.
 */
class m190516_200148_attribute_typo extends \flipbox\saml\core\migrations\m190516_200148_attribute_typo
{
    protected static function getProviderRecord(): string
    {
        return ProviderRecord::class;
    }
}
