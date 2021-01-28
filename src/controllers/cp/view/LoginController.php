<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers\cp\view;

use flipbox\saml\sp\Saml;

class LoginController extends GeneralController
{
    const TEMPLATE_INDEX = DIRECTORY_SEPARATOR . '_cp';
    // latest
    const LOGIN_TEMPLATE = 'saml-sp/_cp/login';
    // 3.3 and before
    const LOGIN_TEMPLATE_33 = 'saml-sp/_cp/login33';
    // 3.4 - 3.5.18
    const LOGIN_TEMPLATE_34 = 'saml-sp/_cp/login34';

    public $allowAnonymous = [
        'index',
    ];

    /**
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $variables = $this->getPlugin()->getEditProvider()->getBaseVariables();

        $variables['providers'] = Saml::getInstance()->getProvider()->findByIdp();
        return $this->renderTemplate(
            $this->getLoginTemplate(),
            $variables
        );
    }

    /**
     * Support UI changes in 3.4 and beyond
     * @return string
     */
    private function getLoginTemplate()
    {
        $use34Template = version_compare(
            \Craft::$app->getVersion(),
            '3.4',
            '>='
        ) === true;

        $useLatestTemplate = version_compare(
            \Craft::$app->getVersion(),
            '3.5.18',
            '>='
        ) === true;

        if ($useLatestTemplate) {
            return self::LOGIN_TEMPLATE;
        }

        return $use34Template ? self::LOGIN_TEMPLATE_34 : self::LOGIN_TEMPLATE_33;
    }
}
