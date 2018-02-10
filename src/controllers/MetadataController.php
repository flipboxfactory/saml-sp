<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\sp\controllers;


use craft\web\Controller;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\sp\models\Provider;
use flipbox\saml\sp\Saml;

class MetadataController extends Controller
{

    public function actionIndex()
    {

        $this->requireAdmin();

        /** @var Provider $provider */
        $provider = Saml::getInstance()->getProvider()->findByEntityId(
            Saml::getInstance()->getSettings()->getEntityId()
        );

        if($provider) {
            $metadata = $provider->getMetadata();
        }else{
            $metadata = Saml::getInstance()->getMetadata()->create()->getMetadata();
        }


        SerializeHelper::xmlContentType();
        return SerializeHelper::toXml($metadata);
    }

}