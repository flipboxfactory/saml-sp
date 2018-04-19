<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;


use flipbox\ember\helpers\ModelHelper;
use flipbox\saml\core\records\AbstractProviderIdentity;

class ProviderIdentityRecord extends AbstractProviderIdentity
{

    const TABLE_ALIAS = 'saml_sp_provider_identity';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(), [
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
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getProvider()
    {
        return $this->hasOne(
            ProviderRecord::class,
            [
                'providerId' => 'id',
            ]
        );
    }
}