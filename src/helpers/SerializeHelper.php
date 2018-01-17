<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 12:25 PM
 */

namespace flipbox\saml\sp\helpers;

use craft\web\Response;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Protocol\SamlMessage;

class SerializeHelper
{
    public static function base64Message(SamlMessage $message, $deflate = false)
    {

        $xml = static::toXml($message);
        if($deflate){
//            $xml = gzdeflate($xml);
        }

        return base64_encode($xml);

    }

    public static function toBase64($parameter)
    {
        return base64_encode($parameter);
    }

    /**
     * @param AbstractSamlModel $model
     * @return string
     */
    public static function toXml(AbstractSamlModel $message)
    {
        $context = new SerializationContext(new \DOMDocument('1.0', 'UTF-8'));
        $message->serialize($context->getDocument(), $context);
        return $context->getDocument()->saveXML();
    }

    public static function xmlContentType()
    {
        \Craft::$app->getResponse()->format = Response::FORMAT_RAW;
        \Craft::$app->getResponse()->getHeaders()->add('Content-Type', 'text/xml');
    }
}