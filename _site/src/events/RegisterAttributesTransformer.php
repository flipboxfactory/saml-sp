<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/1/18
 * Time: 9:11 PM
 */

namespace flipbox\saml\sp\events;


use flipbox\saml\sp\transformers\AbstractResponse;
use yii\base\Event;
use yii\base\InvalidConfigException;

class RegisterAttributesTransformer extends Event
{

    protected $transformers = [];

    /**
     * @param string $entityId
     * @param string $class
     * @return $this
     * @throws InvalidConfigException
     */
    public function setTransformer(string $entityId, string $class)
    {
        if (! class_exists($class) || ! (new $class(new User) instanceof AbstractResponse)) {
            throw new InvalidConfigException(
                sprintf(
                    "Transformer must be class of instanceof %s. %s was given and incorrect.",
                    AbstractResponse::class,
                    $class
                )
            );
        }
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