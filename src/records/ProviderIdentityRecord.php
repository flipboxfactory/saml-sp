<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;


use flipbox\ember\records\ActiveRecord;

/**
 * Class ProviderIdentityRecord
 * @package flipbox\saml\sp\records
 * @property int $providerId
 * @property int $userId
 * @property string $providerIdentity
 * @property bool $enabled
 * @property \DateTime $lastLoginDate
 * @property \DateTime $dateCreated
 * @property \DateTime $dateUpdated
 */
class ProviderIdentityRecord extends ActiveRecord
{

    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%saml_sp_provider_identity}}';
    }

}