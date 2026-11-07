<?php

namespace flipbox\saml\sp\tests\unit\misc;

use Codeception\Test\Unit;
use craft\helpers\UrlHelper;
use flipbox\saml\sp\twig\Extension;
use Twig\TwigFunction;
use yii\base\InvalidArgumentException;

class TwigExtensionTest extends Unit
{
    use ConsoleFixtures;

    public function testRegistersLogoutUrlFunction(): void
    {
        $functions = (new Extension())->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
        $this->assertSame('samlSpLogoutUrl', $functions[0]->getName());
    }

    public function testLogoutUrlForKnownProvider(): void
    {
        $provider = $this->saveIdpProvider($this);

        $url = Extension::getLogoutUrl($provider->entityId);

        $this->assertSame(UrlHelper::siteUrl($provider->getLogoutRequestPath()), $url);
        $this->assertStringContainsString($provider->uid, $url);
    }

    public function testLogoutUrlRendersInTwig(): void
    {
        $provider = $this->saveIdpProvider($this);
        $view = \Craft::$app->getView();
        $view->registerTwigExtension(new Extension());

        $html = $view->renderString('{{ samlSpLogoutUrl(entityId) }}', ['entityId' => $provider->entityId]);

        $this->assertSame(UrlHelper::siteUrl($provider->getLogoutRequestPath()), html_entity_decode($html));
    }

    public function testLogoutUrlForUnknownProviderThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Provider 'https://unknown.example.com' not found");

        Extension::getLogoutUrl('https://unknown.example.com');
    }
}
