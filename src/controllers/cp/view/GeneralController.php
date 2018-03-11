<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/9/18
 * Time: 2:48 PM
 */

namespace flipbox\saml\sp\controllers\cp\view;


use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;

class GeneralController extends Controller
{
    const TEMPLATE_INDEX = 'saml-sp/_cp';

    public function actionIndex()
    {
        $variables['crumbs'] = [
            [
                'url'=> UrlHelper::cpUrl('saml-sp'),
                'label' => 'SSO Service Provider'
            ],
        ];
        $variables['providers'] = ProviderRecord::find()->all();
        $variables['title'] = Craft::t(Saml::getInstance()->getUniqueId(), Saml::getInstance()->name);
        return $this->renderTemplate(
            static::TEMPLATE_INDEX,
            $variables
        );
    }

}