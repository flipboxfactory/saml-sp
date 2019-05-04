<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\sp\controllers;

use craft\web\Request;
use flipbox\saml\core\controllers\messages\AbstractLogoutController;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\Saml;

/**
 * Class LogoutController
 *
 * @package flipbox\saml\sp\controllers
 * TODO
 */
class LogoutController extends AbstractLogoutController
{

    /**
     * @param null $uid
     * @return bool|ProviderInterface
     */
    protected function getRemoteProvider($uid = null)
    {
        $condition = [];
        if ($uid) {
            $condition = [
                'uid' => $uid,
            ];
        }
        return $this->getSamlPlugin()->getProvider()->findByIdp($condition)->one();
    }

    /**
     * @param SamlMessage $samlMessage
     * @param ProviderInterface $provider
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    protected function send(SamlMessage $samlMessage, ProviderInterface $provider)
    {
        Saml::getInstance()->getBindingFactory()->send(
            $samlMessage,
            $provider
        );
        \Craft::$app->end();
    }

    /**
     * @param Request $request
     * @return StatusResponse
     * @throws \flipbox\saml\core\exceptions\InvalidSignature
     * @throws \yii\base\InvalidConfigException
     */
    protected function receive(Request $request): StatusResponse
    {
        return Saml::getInstance()->getBindingFactory()->receive(
            $request
        );
    }
}
