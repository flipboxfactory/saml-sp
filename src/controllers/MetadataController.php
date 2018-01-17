<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\sp\controllers;


use craft\web\Controller;
use craft\web\Response;
use flipbox\saml\sp\helpers\SerializeHelper;
use flipbox\saml\sp\Saml;
use Craft;

class MetadataController extends Controller
{

    public function actionIndex()
    {

        $this->requireAdmin();

        $metadata = Saml::getInstance()->getMetadata()->create();


        SerializeHelper::xmlContentType();
        return SerializeHelper::toXml($metadata);
    }

}