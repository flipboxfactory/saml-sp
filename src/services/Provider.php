<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\sp\services;


use craft\base\Component;
use flipbox\ember\models\Model;
use flipbox\ember\services\traits\AccessorByIdOrString;
use flipbox\ember\services\traits\ModelDelete;
use flipbox\ember\services\traits\ModelSave;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\models\Provider as ProviderModel;
use LightSaml\Model\Assertion\Issuer;
use yii\db\ActiveRecord;


class Provider extends Component
{

    use AccessorByIdOrString, ModelSave, ModelDelete;

    const HANDLE = 'entityId';


    public static function objectClass(): string
    {
        return ProviderModel::class;
    }

    public static function recordClass(): string
    {
        return ProviderRecord::class;
    }

    public function stringProperty(): string
    {
        return static::HANDLE;
    }

    public function getRecordByModel(Model $model): ActiveRecord
    {
        /** @var $model ProviderModel */
        return ProviderRecord::findOne([
           'entityId' => $model->getEntityId(),
        ]);

    }

    public function modelToRecord(Model $model, bool $mirrorScenario = true): ActiveRecord
    {
//        if (!$record = $this->findRecordByObject($model)) {
//            $record = $this->createRecord();
//        }
//
//        if ($mirrorScenario === true) {
//            $record->setScenario($model->getScenario());
//        }
//
//        // Populate the record attributes
//        $this->transferToRecord($model, $record);
//        return $record;

        /** @var $model ProviderModel */
        return new ProviderRecord([
            'id' => $model->getId(),
            'isNewRecord' => $this->isNew($model),
            'entityId' => $model->getEntityId(),
            'metadata' => SerializeHelper::toXml($model->getMetadata()),
            'localKeyId' => $model->localKeyId,
            'enabled'  => (bool)$model->enabled,
            'default'  => (bool)$model->default,
        ]);
    }

    public function isNew(Model $model): bool
    {
        return ! $model->id;
    }

    public function findDefaultProvider()
    {
        return $this->findByCondition([
            'default' => true,
        ]);
    }

    public function findByIssuer(Issuer $issuer)
    {
        return $this->findByString($issuer->getValue());
    }
}