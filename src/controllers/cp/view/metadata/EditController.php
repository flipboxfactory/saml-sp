<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/10/18
 * Time: 8:40 PM
 */

namespace flipbox\saml\sp\controllers\cp\view\metadata;

use Craft;
use craft\web\Controller;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use craft\helpers\UrlHelper;

class EditController extends Controller
{
    const TEMPLATE_INDEX = 'saml-sp/_cp/metadata';

    public function actionIndex($providerId = null)
    {
        $variables['title'] = Craft::t(Saml::getInstance()->getUniqueId(), Saml::getInstance()->name);

        if($providerId) {
            $variables['provider'] = ProviderRecord::find()->where([
                'id' => $providerId,
            ])->one();
            $variables['title'] .= ': Edit';
            $crumb = [
                'url' => UrlHelper::cpUrl(
                    Saml::getInstance()->getUniqueId() . '/' . $providerId
                ),
                'label' => $variables['provider']->entityId,
            ];
        }else{
            $variables['provider'] = new ProviderRecord([
                'providerType' => 'idp',
            ]);
            $variables['title'] .= ': Create';
            $crumb = [
                'url' => UrlHelper::cpUrl(
                    Saml::getInstance()->getUniqueId() . '/new'
                ),
                'label' => 'New',
            ];
        }

        $variables['crumbs'] = [
            [
                'url'=> UrlHelper::cpUrl('saml-sp'),
                'label' => Craft::t(Saml::getInstance()->getUniqueId(),Saml::getInstance()->name),
            ],
            $crumb,
        ];

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseCpPath();
        $variables['baseActionPath'] = $this->getBaseCpPath();
        $variables['baseCpPath'] = $this->getBaseCpPath();


        return $this->renderTemplate(
            static::TEMPLATE_INDEX,
            $variables
        );
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return Saml::getInstance()->getUniqueId();
    }

}