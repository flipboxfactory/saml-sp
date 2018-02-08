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
use craft\helpers\ArrayHelper;
use flipbox\ember\models\Model;
use flipbox\ember\records\ActiveRecord;
use flipbox\ember\services\traits\AccessorByIdOrString;
use flipbox\ember\services\traits\ModelDelete;
use flipbox\ember\services\traits\ModelSave;
use flipbox\saml\sp\records\AbstractProviderIdentityRecord;
use flipbox\saml\sp\models\ProviderIdentity as ProviderIdentityModel;
use yii\base\BaseObject;
use yii\db\ActiveRecord as Record;

/**
 * Class ProviderIdentity
 * @package flipbox\saml\sp\services
 */
class ProviderIdentity extends Component
{
    use AccessorByIdOrString, ModelSave, ModelDelete {
        AccessorByIdOrString::transferToRecord as accessorTransferToRecord;
    }

    const STRING_HANDLE = 'providerIdentity';


    public static function objectClass(): string
    {
        return ProviderIdentityModel::class;
    }

    public static function recordClass(): string
    {
        return AbstractProviderIdentityRecord::class;
    }

    public function stringProperty(): string
    {
        return static::STRING_HANDLE;
    }

    public function getRecordByModel(Model $model): ActiveRecord
    {
        /** @var $model ProviderIdentityModel */
        return AbstractProviderIdentityRecord::findOne([
            'providerIdentity' => $model->providerIdentity,
        ]);

    }

    public function transferToRecord(BaseObject $object, Record $record)
    {
        /** @var ProviderIdentityModel $object */
        /** @var AbstractProviderIdentityRecord $record */
        $this->accessorTransferToRecord($object, $record);
        $record->lastLoginDate = $object->lastLoginDate->format(\DateTime::ISO8601);
    }

    public function modelToRecord(Model $model, bool $mirrorScenario = true): ActiveRecord
    {

        if (!$record = $this->findRecordByObject($model)) {
            $record = $this->createRecord();
        }

        if ($mirrorScenario === true) {
            $record->setScenario($model->getScenario());
        }

        // Populate the record attributes
        $this->transferToRecord($model, $record);
        return $record;
    }

    public function isNew(Model $model): bool
    {
        return ! $model->id;
    }
}