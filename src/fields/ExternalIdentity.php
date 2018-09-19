<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\fields;

use craft\base\ElementInterface;
use craft\helpers\UrlHelper;
use flipbox\saml\core\fields\AbstractExternalIdentity;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\sp\Saml;
use yii\db\Query;

class ExternalIdentity extends AbstractExternalIdentity
{

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    /**
     * @param $value
     * @param ElementInterface $element
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getStaticHtml($value, ElementInterface $element): string
    {
        if (! ($value instanceof Query)) {
            return '';
        }

        return \Craft::$app->getView()->renderTemplate(
            'saml-sp/_cp/fields/external-id',
            [
                'identities' => $value,
                'element'    => $element,
                'baseProviderUrl' => UrlHelper::cpUrl(
                    'saml-sp/metadata'
                ),
            ]
        );
    }
}
