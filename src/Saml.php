<?php
/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/saml-sp/license
 * @link       https://www.flipboxfactory.com/software/saml-sp/
 */

namespace flipbox\saml\sp;

use Craft;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\Session;
use flipbox\saml\sp\fields\ExternalIdentity;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\services\bindings\Factory;
use flipbox\saml\sp\services\bindings\HttpPost;
use flipbox\saml\sp\services\bindings\HttpRedirect;
use flipbox\saml\sp\services\Login;
use flipbox\saml\sp\services\login\User;
use flipbox\saml\sp\services\login\UserGroups;
use flipbox\saml\sp\services\messages\AuthnRequest;
use flipbox\saml\sp\services\messages\LogoutRequest;
use flipbox\saml\sp\services\messages\LogoutResponse;
use flipbox\saml\sp\services\messages\Response;
use flipbox\saml\sp\services\Provider;
use flipbox\saml\sp\services\ProviderIdentity;
use yii\base\Event;

/**
 * Class Saml
 * @package flipbox\saml\sp
 */
class Saml extends AbstractPlugin
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->initComponents();
        $this->initEvents();

        // Switch target to console controllers
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = __NAMESPACE__ . '\cli';
            $this->controllerMap = [
                'metadata' => \flipbox\saml\sp\cli\Metadata::class,
                'keychain' => \flipbox\saml\sp\cli\KeyChain::class,
            ];
        }
    }

    /**
     * Events
     */
    protected function initEvents()
    {
        /**
         * CP routes
         */
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules']
        );


        /**
         * Clean Frontend Endpoints
         */
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            [static::class, 'onRegisterSiteUrlRules']
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ExternalIdentity::class;
            }
        );
    }

    /**
     * Components
     */
    public function initComponents()
    {
        $this->setComponents(
            [
                'authnRequest' => AuthnRequest::class,
                'httpPost' => HttpPost::class,
                'httpRedirect' => HttpRedirect::class,
                'bindingFactory' => Factory::class,
                'login' => Login::class,
                'user' => User::class,
                'userGroups' => UserGroups::class,
                'logoutRequest' => LogoutRequest::class,
                'logoutResponse' => LogoutResponse::class,
                'provider' => Provider::class,
                'providerIdentity' => ProviderIdentity::class,
                'response' => Response::class,
                'session' => Session::class,
            ]
        );
    }

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event)
    {

        $event->rules = array_merge(
            $event->rules,
            static::getInstance()->getSettings()->enableCpLoginButtons ?
                [
                    'login' => 'saml-sp/cp/view/login',
                ] : []
        );

        parent::onRegisterCpUrlRules($event);
    }



    /**
     * @return Settings
     */
    public function getSettings(): SettingsInterface
    {
        return parent::getSettings();
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings([
            'myType' => Settings::SP,
        ]);
    }

    /**
     * Components
     */

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return AuthnRequest
     */
    public function getAuthnRequest()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('authnRequest');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return Response
     */
    public function getResponse()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('response');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return Login
     */
    public function getLogin()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('login');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return User
     */
    public function getUser()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('user');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return UserGroups
     */
    public function getUserGroups()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('userGroups');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return Session
     * @throws \yii\base\InvalidConfigException
     */
    public function getSession()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('session');
    }

    /**
     * Util Methods
     */

    /**
     * @return string
     */
    public function getProviderRecordClass()
    {
        return ProviderRecord::class;
    }

    /**
     * @return string
     */
    public function getProviderIdentityRecordClass()
    {
        return ProviderIdentityRecord::class;
    }
}
