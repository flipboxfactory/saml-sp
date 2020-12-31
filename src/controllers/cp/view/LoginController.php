<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers\cp\view;

use flipbox\saml\sp\Saml;

class LoginController extends GeneralController
{
    const TEMPLATE_INDEX = DIRECTORY_SEPARATOR . '_cp';
    const LOGIN_TEMPLATE = 'saml-sp/_cp/login';
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
     * Support UI changes in 3.4
     * @return string
     */
    private function getLoginTemplate()
    {
        $useNewTemplate = version_compare(
            \Craft::$app->getVersion(),
            '3.4',
            '>='
        );

        return $useNewTemplate ? self::LOGIN_TEMPLATE_34 : self::LOGIN_TEMPLATE;
    }
}
