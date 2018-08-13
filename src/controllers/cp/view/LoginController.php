<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\controllers\cp\view;


use flipbox\saml\sp\controllers\cp\view\GeneralController;
use flipbox\saml\sp\Saml;

class LoginController extends GeneralController
{
    const TEMPLATE_INDEX = DIRECTORY_SEPARATOR . '_cp';

    public $allowAnonymous = [
        'index',
    ];
    public function actionIndex()
    {
        $variables = $this->getBaseVariables();

        $variables['providers'] = Saml::getInstance()->getProvider()->findByIdp();
        return $this->renderTemplate(
            'saml-sp/_cp/login',
//            $this->getTemplateIndex() . static::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . '',
            $variables
        );
    }

}