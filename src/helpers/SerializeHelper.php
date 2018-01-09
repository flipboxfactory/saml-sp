<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 12:25 PM
 */

namespace flipbox\saml\sp\helpers;

use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\AbstractSamlModel;

class SerializeHelper
{

    /**
     * @param AbstractSamlModel $model
     * @return string
     */
    public static function toXml(AbstractSamlModel $model)
    {
        $context = new SerializationContext(new \DOMDocument('1.0', 'UTF-8'));
        $model->serialize($context->getDocument(), $context);
        return $context->getDocument()->saveXML();
    }
}