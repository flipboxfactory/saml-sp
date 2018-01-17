<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/16/18
 * Time: 8:09 PM
 */

namespace flipbox\saml\sp\events;


use yii\base\Event;

class RegisterAttributesTransformer extends Event
{
    protected $transformers =[];

    /**
     * @param $entityId
     * @param $class
     * @return $this
     */
    public function setTransformer($entityId, $class)
    {
        $this->transformers[$entityId] = $class;
        return $this;
    }

    /**
     * @param $entityId
     * @return mixed|null
     */
    public function getTransformer($entityId)
    {
        return isset($this->transformers[$entityId]) ? $this->transformers[$entityId] : null;
    }
}