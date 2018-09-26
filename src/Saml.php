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
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\web\UrlManager;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\sp\fields\ExternalIdentity;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\services\Cp;
use flipbox\saml\sp\services\messages\AuthnRequest;
use flipbox\saml\sp\services\messages\LogoutRequest;
use flipbox\saml\sp\services\messages\LogoutResponse;
use flipbox\saml\sp\services\messages\Metadata;
use flipbox\saml\sp\services\messages\Response;
use flipbox\saml\sp\services\bindings\HttpPost;
use flipbox\saml\sp\services\bindings\HttpRedirect;
use flipbox\saml\sp\services\Login;
use flipbox\saml\sp\services\Provider;
use flipbox\saml\sp\services\ProviderIdentity;
use flipbox\saml\core\services\Session;
use yii\base\Event;

/**
 * Class Saml
 * @package flipbox\saml\sp
 */
class Saml extends AbstractPlugin implements SamlPluginInterface
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
                'authnRequest'     => AuthnRequest::class,
                'httpPost'         => HttpPost::class,
                'httpRedirect'     => HttpRedirect::class,
                'login'            => Login::class,
                'logoutRequest'    => LogoutRequest::class,
                'logoutResponse'   => LogoutResponse::class,
                'provider'         => Provider::class,
                'providerIdentity' => ProviderIdentity::class,
                'metadata'         => Metadata::class,
                'response'         => Response::class,
                'session'          => Session::class,
                'cp'               => Cp::class,
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
            [
                'saml-sp/'                          => 'saml-sp/cp/view/general/setup',
                'saml-sp/settings'                  => 'saml-sp/cp/view/general/settings',

                /**
                 * Keychain
                 */
                'saml-sp/keychain'                  => 'saml-sp/cp/view/keychain/index',
                'saml-sp/keychain/new'              => 'saml-sp/cp/view/keychain/edit',
                'saml-sp/keychain/new-openssl'      => 'saml-sp/cp/view/keychain/edit/openssl',
                'saml-sp/keychain/<keypairId:\d+>'  => 'saml-sp/cp/view/keychain/edit',

                /**
                 * Metadata
                 */
                'saml-sp/metadata'                  => 'saml-sp/cp/view/metadata/default',
                'saml-sp/metadata/new'              => 'saml-sp/cp/view/metadata/edit',
                'saml-sp/metadata/new-idp'          => 'saml-sp/cp/view/metadata/edit/new-idp',
                'saml-sp/metadata/new-sp'           => 'saml-sp/cp/view/metadata/edit/new-sp',
                'saml-sp/metadata/my-provider'      => 'saml-sp/cp/view/metadata/edit/my-provider',
                'saml-sp/metadata/<providerId:\d+>' => 'saml-sp/cp/view/metadata/edit',
            ],
            static::getInstance()->getSettings()->enableCpLoginButtons ?
                [
                    'login' => 'saml-sp/cp/view/login',
                ] : []
        );
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {

        Craft::$app->getResponse()->redirect(
            UrlHelper::cpUrl('saml-sp/settings')
        );

        Craft::$app->end();
    }

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterSiteUrlRules(RegisterUrlRulesEvent $event)
    {
        $event->rules = array_merge(
            $event->rules,
            [
                /**
                 * LOGIN
                 */
                'POST,GET /sso/login'  => 'saml-sp/login',
                sprintf(
                    'GET %s',
                    (string)static::getInstance()->getSettings()->loginRequestEndpoint
                )                      => 'saml-sp/login/request',
                sprintf(
                    'GET %s/<uid:[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}>',
                    (string)static::getInstance()->getSettings()->loginRequestEndpoint
                )                      => 'saml-sp/login/request',
                /**
                 * LOGOUT
                 */
                'POST,GET /sso/logout' => 'saml-sp/logout',
                sprintf(
                    'GET %s',
                    (string)static::getInstance()->getSettings()->logoutRequestEndpoint
                )                      => 'saml-sp/logout/request',
            ]
        );
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
    public function createSettingsModel()
    {
        return new Settings();
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

    public function getMyType()
    {
        return static::SP;
    }
}
