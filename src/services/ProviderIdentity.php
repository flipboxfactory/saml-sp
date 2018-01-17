<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\sp\services;


use Craft;
use craft\base\Component;
use craft\elements\User;
use flipbox\ember\models\Model;
use flipbox\ember\records\ActiveRecord;
use flipbox\ember\services\traits\AccessorByIdOrString;
use flipbox\ember\services\traits\ModelDelete;
use flipbox\ember\services\traits\ModelSave;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\models\ProviderIdentity as ProviderIdentityModel;

/**
 * Class ProviderIdentity
 * @package flipbox\saml\sp\services
 */
class ProviderIdentity extends Component
{
    use AccessorByIdOrString, ModelSave, ModelDelete;

    const STRING_HANDLE = 'providerIdentity';


    public static function objectClass(): string
    {
        return ProviderIdentityModel::class;
    }

    public static function recordClass(): string
    {
        return ProviderIdentityRecord::class;
    }

    public function stringProperty(): string
    {
        return static::STRING_HANDLE;
    }

    public function getRecordByModel(Model $model): ActiveRecord
    {
        /** @var $model ProviderIdentityModel */
        return ProviderIdentityRecord::findOne([
            'providerIdentity' => $model->providerIdentity,
        ]);

    }

    public function modelToRecord(Model $model, bool $mirrorScenario = true): ActiveRecord
    {

        /** @var $model ProviderIdentityModel */
        return new ProviderIdentityRecord([
            'isNewRecord'      => $this->isNew($model),
            'id'               => $model->id,
            'providerIdentity' => $model->providerIdentity,
            'providerId'       => $model->providerId,
            'userId'           => $model->getUserId(),
            'enabled'          => (bool)($model->enabled ?: true),
            'lastLoginDate'    => $model->lastLoginDate,
        ]);
    }

    public function isNew(Model $model): bool
    {
        return ! $model->id;
    }
}