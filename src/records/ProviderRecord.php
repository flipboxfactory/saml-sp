<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;


use flipbox\ember\records\ActiveRecord;
use flipbox\ember\records\traits\StateAttribute;

class ProviderRecord extends ActiveRecord
{

    use StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_sp_providers';

    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%saml_sp_providers}}';
    }
}