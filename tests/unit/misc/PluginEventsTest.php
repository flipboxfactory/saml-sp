<?php

namespace flipbox\saml\sp\tests\unit\misc;

use Codeception\Test\Unit;
use craft\web\View;
use flipbox\saml\sp\fields\ExternalIdentity;
use flipbox\saml\sp\Saml;

class PluginEventsTest extends Unit
{
    use ConsoleFixtures;

    private bool $originalButtons;

    protected function _before(): void
    {
        $this->originalButtons = Saml::getInstance()->getSettings()->enableCpLoginButtons;
    }

    protected function _after(): void
    {
        Saml::getInstance()->getSettings()->enableCpLoginButtons = $this->originalButtons;
    }

    public function testExternalIdentityFieldTypeIsRegistered(): void
    {
        $this->assertContains(ExternalIdentity::class, \Craft::$app->getFields()->getAllFieldTypes());
    }

    public function testCpLoginHookRendersProviderButtons(): void
    {
        $provider = $this->saveIdpProvider($this, ['label' => 'Corporate SSO']);
        Saml::getInstance()->getSettings()->enableCpLoginButtons = true;

        $html = $this->renderLoginHook();

        $this->assertStringContainsString('Via Corporate SSO', $html);
        $this->assertStringContainsString($provider->getLoginRequestPath(), $html);
    }

    public function testCpLoginHookRendersNothingWhenButtonsDisabled(): void
    {
        $this->saveIdpProvider($this, ['label' => 'Corporate SSO']);
        Saml::getInstance()->getSettings()->enableCpLoginButtons = false;

        $this->assertStringNotContainsString('Corporate SSO', $this->renderLoginHook());
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
