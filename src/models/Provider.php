<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:30 PM
 */

namespace flipbox\saml\sp\models;


use craft\base\Model;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\models\ModelWithId;
use flipbox\ember\traits\StateAttribute;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Metadata\EntityDescriptor;

class Provider extends ModelWithId
{

    use StateAttribute;

    public $id;

    /**
     * @var integer
     */
    public $localKeyId;

    /**
     * @var $enabled bool
     */
    public $enabled;

    /**
     * @var $default bool
     */
    public $default;

    /**
     * @var $entityId string
     */
    protected $entityId;

    /**
     * @var $metadata EntityDescriptor
     */
    protected $metadata;

    public function attributes()
    {
        return array_merge(
            [
                'entityId',
                'metadata',
                'localKeyId',
            ],
            parent::attributes()
        );
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        if (! $this->entityId) {
            if ($this->getMetadata()) {
                $this->entityId = $this->getMetadata()->getEntityID();
            }
        }

        return $this->entityId;
    }

    /**
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @param $metadata
     * @return $this
     */
    public function setMetadata($metadata)
    {
        if (is_string($metadata)) {
            $metadata = EntityDescriptor::fromXML($metadata, new DeserializationContext());
            $this->setEntityId(
                $metadata->getEntityID()
            );
        }
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return EntityDescriptor|null
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}