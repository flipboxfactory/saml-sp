<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;


use flipbox\ember\records\ActiveRecord;
use flipbox\ember\helpers\ModelHelper;
use craft\validators\DateTimeValidator;

/**
 * Class AbstractProviderIdentityRecord
 * @package flipbox\saml\sp\records
 * @property int $providerId
 * @property int $userId
 * @property string $providerIdentity
 * @property string $sessionId
 * @property bool $enabled
 * @property \DateTime $lastLoginDate
 * @property \DateTime $dateCreated
 * @property \DateTime $dateUpdated
 */
class ProviderIdentityRecord extends ActiveRecord
{

    const TABLE_ALIAS = 'saml_sp_provider_identity';


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            [
                [
                    'lastLoginDate',
                    'sessionId',
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ]);
    }

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