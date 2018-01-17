<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;


use craft\db\ActiveRecord;

class ProviderRecord extends ActiveRecord
{

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