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

    public array|bool|int $allowAnonymous = [
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
        return self::LOGIN_TEMPLATE;
    }
}
