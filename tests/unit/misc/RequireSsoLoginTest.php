<?php

namespace flipbox\saml\sp\tests\unit\misc;

use Codeception\Test\Unit;
use craft\elements\User;
use craft\events\LoginFailureEvent;
use craft\web\View;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\login\User as UserService;

/**
 * `requireSsoLogin`: Craft's native password/passkey sign-in is rejected,
 * SAML sign-in still works.
 */
class RequireSsoLoginTest extends Unit
{
    use ConsoleFixtures;

    private const PASSWORD = 'correct-horse-battery-staple';

    private bool $originalRequireSso;
    private bool $originalButtons;

    protected function _before(): void
    {
        $settings = Saml::getInstance()->getSettings();
        $this->originalRequireSso = (bool)$settings->requireSsoLogin;
        $this->originalButtons = (bool)$settings->enableCpLoginButtons;
    }

    protected function _after(): void
    {
        $settings = Saml::getInstance()->getSettings();
        $settings->requireSsoLogin = $this->originalRequireSso;
        $settings->enableCpLoginButtons = $this->originalButtons;
    }

    public function testDisabledByDefault(): void
    {
        $this->assertFalse((new \flipbox\saml\sp\models\Settings())->requireSsoLogin);
    }

    public function testPasswordLoginWorksWhenSettingIsOff(): void
    {
        Saml::getInstance()->getSettings()->requireSsoLogin = false;
        $user = $this->userWithPassword();

        $this->assertTrue($user->authenticate(self::PASSWORD));
        $this->assertNull($user->authError);
    }

    public function testCorrectPasswordIsRejectedWhenSsoIsRequired(): void
    {
        Saml::getInstance()->getSettings()->requireSsoLogin = true;
        $user = $this->userWithPassword();

        $this->assertFalse($user->authenticate(self::PASSWORD));
        $this->assertSame(Saml::AUTH_SSO_REQUIRED, $user->authError);
    }

    public function testAdminPasswordIsRejectedWhenSsoIsRequired(): void
    {
        Saml::getInstance()->getSettings()->requireSsoLogin = true;
        $user = $this->userWithPassword(['admin' => true]);

        $this->assertFalse($user->authenticate(self::PASSWORD));
        $this->assertSame(Saml::AUTH_SSO_REQUIRED, $user->authError);
    }

    public function testRejectedAttemptsDoNotLockTheAccount(): void
    {
        Saml::getInstance()->getSettings()->requireSsoLogin = true;
        $user = $this->userWithPassword();

        $this->assertFalse($user->authenticate('wrong-password'));
        $this->assertSame(Saml::AUTH_SSO_REQUIRED, $user->authError);
        $this->assertEmpty($this->reloadUser($user)->invalidLoginCount);
    }

    public function testPasskeyLoginIsRejectedWhenSsoIsRequired(): void
    {
        Saml::getInstance()->getSettings()->requireSsoLogin = true;
        $user = $this->userWithPassword();

        $this->assertFalse($user->authenticateWithPasskey('{}', '{"id":"not-a-real-credential"}'));
        $this->assertSame(Saml::AUTH_SSO_REQUIRED, $user->authError);
    }

    public function testLoginFailureMessageExplainsSsoIsRequired(): void
    {
        $event = new LoginFailureEvent(['authError' => Saml::AUTH_SSO_REQUIRED, 'message' => 'Invalid username or password.']);
        Saml::onLoginFailure($event);
        $this->assertSame('Sign in with single sign-on (SSO).', $event->message);
    }

    public function testOtherLoginFailureMessagesAreUntouched(): void
    {
        $event = new LoginFailureEvent(['authError' => User::AUTH_INVALID_CREDENTIALS, 'message' => 'Invalid username or password.']);
        Saml::onLoginFailure($event);
        $this->assertSame('Invalid username or password.', $event->message);
    }

    public function testSamlLoginStillWorksWhenSsoIsRequired(): void
    {
        Saml::getInstance()->getSettings()->requireSsoLogin = true;
        $user = $this->userWithPassword();
        $identity = new ProviderIdentityRecord(['userId' => $user->id]);

        // CLI test requests have no user agent/IP, which Craft otherwise requires for a session
        $general = \Craft::$app->getConfig()->getGeneral();
        $requireUa = $general->requireUserAgentAndIpForSession;
        $general->requireUserAgentAndIpForSession = false;
        try {
            $this->assertTrue((new UserService())->login($identity));
            $this->assertSame($user->id, \Craft::$app->getUser()->getId());
        } finally {
            \Craft::$app->getUser()->logout(false);
            $general->requireUserAgentAndIpForSession = $requireUa;
        }
    }

    public function testLoginHookHidesNativeFormWhenSsoIsRequired(): void
    {
        $this->saveIdpProvider($this, ['label' => 'Corporate SSO']);
        $settings = Saml::getInstance()->getSettings();
        $settings->enableCpLoginButtons = true;
        $settings->requireSsoLogin = true;

        $html = $this->renderLoginHook();

        $this->assertStringContainsString('.login-form-container', $html);
        $this->assertStringContainsString('Via Corporate SSO', $html);
    }

    public function testLoginHookKeepsNativeFormWhenSettingIsOff(): void
    {
        $this->saveIdpProvider($this, ['label' => 'Corporate SSO']);
        $settings = Saml::getInstance()->getSettings();
        $settings->enableCpLoginButtons = true;
        $settings->requireSsoLogin = false;

        $this->assertStringNotContainsString('.login-form-container', $this->renderLoginHook());
    }

    public function testLoginHookKeepsNativeFormWhenThereAreNoButtons(): void
    {
        $settings = Saml::getInstance()->getSettings();
        $settings->enableCpLoginButtons = false;
        $settings->requireSsoLogin = true;

        $this->assertStringNotContainsString('.login-form-container', $this->renderLoginHook());
    }

    private function userWithPassword(array $attributes = []): User
    {
        $user = $this->saveUser(array_merge([
            'active' => true,
            'password' => \Craft::$app->getSecurity()->hashPassword(self::PASSWORD),
        ], $attributes));

        // fresh copy so authError/invalid-login state starts clean
        $user->password = \Craft::$app->getSecurity()->hashPassword(self::PASSWORD);
        $user->authError = null;

        return $user;
    }

    private function renderLoginHook(): string
    {
        $view = \Craft::$app->getView();
        $mode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        try {
            $context = [];
            return $view->invokeHook('cp.login.alternative-login-methods', $context);
        } finally {
            $view->setTemplateMode($mode);
        }
    }
}
