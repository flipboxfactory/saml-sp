<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 3/18/18
 * Time: 9:53 PM
 */

namespace flipbox\saml\sp\controllers\cp\view\keychain;

use craft\base\Plugin;
use flipbox\keychain\controllers\cp\view\AbstractEditController;
use flipbox\saml\sp\Saml;

class EditController extends AbstractEditController
{
    protected function getPlugin(): Plugin
    {
        return Saml::getInstance();
    }

    /**
     * @param array $variables
     * @return array
     */
    protected function beforeRender(array $variables = [])
    {
        $request = \Craft::$app->request;

        $key = null;
        $path = implode(
            '/',
            [
                $request->getSegment(2),
                $request->getSegment(3),
                $request->getSegment(4),
            ]
        );

        if (preg_match('#keychain/#', $path)) {
            $key = 'saml.keychain';
        }

        $variables['selectedSubnavItem'] = $key;
        return parent::beforeRender($variables);
    }
}
