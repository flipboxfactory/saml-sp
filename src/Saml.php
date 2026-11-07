<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/saml-sp/license
 * @link       https://www.flipboxfactory.com/software/saml-sp/
 */

namespace flipbox\saml\sp;

use craft\base\Model;
use craft\console\Application as ConsoleApplication;
use craft\controllers\UsersController;
use craft\elements\User as UserElement;
use craft\events\AuthenticateUserEvent;
use craft\events\LoginFailureEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\containers\Saml2Container;
use flipbox\saml\core\services\Session;
use flipbox\saml\sp\fields\ExternalIdentity;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\services\Login;
use flipbox\saml\sp\services\login\User;
use flipbox\saml\sp\services\login\UserGroups;
use flipbox\saml\sp\services\messages\AuthnRequest;
use flipbox\saml\sp\services\Provider;
use flipbox\saml\sp\services\ProviderIdentity;
use flipbox\saml\sp\twig\Extension;
use SAML2\Compat\AbstractContainer;
use yii\base\Event;

/**
 * Class Saml
 * @package flipbox\saml\sp
 */
class Saml extends AbstractPlugin
{
    /**
     * The `authError` set on a user when native sign-in is rejected because
     * `requireSsoLogin` is enabled.
     */
    public const AUTH_SSO_REQUIRED = 'ssoRequired';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->initComponents();
        $this->initEvents();

        // Switch target to console controllers
        if (\Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = __NAMESPACE__ . '\commands';
        }

        if (\Craft::$app->getRequest()->getIsSiteRequest()) {
            // Instantiate + register the twig extension
            $extension = new Extension();
            \Craft::$app->getView()->registerTwigExtension($extension);
        }
    }

    /**
     * Events
     */
    protected function initEvents(): void
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
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = ExternalIdentity::class;
            }
        );

        /**
         * Only allow SAML sign-in (requireSsoLogin)
         */
        Event::on(
            UserElement::class,
            UserElement::EVENT_BEFORE_AUTHENTICATE,
            [static::class, 'onBeforeAuthenticate']
        );
        Event::on(
            UsersController::class,
            UsersController::EVENT_LOGIN_FAILURE,
            [static::class, 'onLoginFailure']
        );

        // Show provider buttons
        \Craft::$app->getView()->hook('cp.login.alternative-login-methods', function(&$context) {
            $settings = Saml::getInstance()->getSettings();
            return \Craft::$app->getView()->renderTemplate(
                'saml-sp/_hooks/login',
                [
                    'providers' => $settings->enableCpLoginButtons ?
                        Saml::getInstance()->getProvider()->findByIdp() :
                        [],
                    'requireSsoLogin' => (bool)$settings->requireSsoLogin,
                ]
            );
        });
    }

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event): void
    {
        parent::onRegisterCpUrlRules($event);

        // Also serve the SSO/SLO endpoints on control panel requests. Headless installs and
        // installs with a dedicated CP domain (baseCpUrl without a cpTrigger) treat every request
        // on that host as a CP request, so site-only rules would 404 there. Access control is
        // unchanged: the login/logout controllers' allowAnonymous applies to CP requests too.
        static::onRegisterSiteUrlRules($event);
    }

    /**
     * Rejects Craft's native (password or passkey) authentication when
     * `requireSsoLogin` is enabled. SAML sign-in doesn't call
     * `User::authenticate()`, so it isn't affected.
     */
    public static function onBeforeAuthenticate(AuthenticateUserEvent $event): void
    {
        $plugin = static::getInstance();
        if (!$plugin || !$plugin->getSettings()->requireSsoLogin) {
            return;
        }

        /** @var UserElement $user */
        $user = $event->sender;
        $user->authError = self::AUTH_SSO_REQUIRED;
        $event->performAuthentication = false;
    }

    /**
     * Replaces Craft's generic login failure message when native sign-in was
     * rejected by `requireSsoLogin`.
     */
    public static function onLoginFailure(LoginFailureEvent $event): void
    {
        if ($event->authError === self::AUTH_SSO_REQUIRED) {
            $event->message = \Craft::t('saml-sp', 'Sign in with single sign-on (SSO).');
        }
    }

    /**
     * Components
     */
    public function initComponents()
    {
        $this->setComponents(
            [
                'authnRequest' => AuthnRequest::class,
                'login' => Login::class,
                'user' => User::class,
                'userGroups' => UserGroups::class,
                'provider' => Provider::class,
                'providerIdentity' => ProviderIdentity::class,
                'session' => Session::class,
            ]
        );
    }

    /**
     * @return Settings
     */
    public function getSettings(): ?Model
    {
        return parent::getSettings();
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
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
     * @return Provider
     */
    public function getProvider()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('provider');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return ProviderIdentity
     */
    public function getProviderIdentity()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('providerIdentity');
    }

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
     * @return Saml2Container
     */
    public function loadSaml2Container(): AbstractContainer
    {
        $container = new Saml2Container($this);

        \SAML2\Compat\ContainerSingleton::setContainer($container);

        return $container;
    }

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
