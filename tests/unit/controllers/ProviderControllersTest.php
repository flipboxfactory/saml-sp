<?php

namespace flipbox\saml\sp\tests\unit\controllers;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use craft\web\Request;
use flipbox\keychain\KeyChain;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\sp\controllers\cp\view\keychain\EditController as KeychainEditController;
use flipbox\saml\sp\controllers\cp\view\keychain\IndexController as KeychainIndexController;
use flipbox\saml\sp\controllers\cp\view\metadata\DefaultController;
use flipbox\saml\sp\controllers\cp\view\metadata\EditController;
use flipbox\saml\sp\controllers\LogoutController;
use flipbox\saml\sp\controllers\MetadataController;
use flipbox\saml\sp\controllers\SettingsController;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;
use yii\web\ForbiddenHttpException;

class ProviderControllersTest extends Unit
{
    private Saml $module;

    private Metadata $metadata;

    protected function _before(): void
    {
        $this->module = new Saml('saml-sp');
        $scenario = new Scenario($this);
        (new SamlPlugin($this->module, $scenario))->installIfNeeded();
        $this->module->loadSaml2Container();
        $this->metadata = new Metadata($this->module, $scenario);
    }

    protected function _after(): void
    {
        \Craft::$app->getUser()->setIdentity(null);
        $this->setSegments(null);
    }

    public function testControllersManageSpProviderRecords(): void
    {
        $this->assertSame(ProviderRecord::class, $this->call(new SettingsController('settings', $this->module), 'getProviderRecord'));
        $this->assertSame(ProviderRecord::class, $this->call(new MetadataController('metadata', $this->module), 'getProviderRecord'));
        $this->assertSame(ProviderRecord::class, (new DefaultController('default', $this->module))->getProviderRecord());
        $this->assertSame(ProviderRecord::class, $this->call(new EditController('edit', $this->module), 'getProviderRecord'));
    }

    public function testLogoutOnlyResolvesIdentityProviders(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $controller = new LogoutController('logout', $this->module);

        $this->assertSame($idp->id, $this->call($controller, 'getRemoteProvider')->id);
        $this->assertSame($idp->id, $this->call($controller, 'getRemoteProvider', $idp->uid)->id);
        $this->assertNull(
            $this->call($controller, 'getRemoteProvider', $sp->uid),
            'A service provider must never be used as the remote (IdP) side of a logout'
        );
    }

    public function testMetadataDownloadRequiresAnAdmin(): void
    {
        $this->saveProviders();
        \Craft::$app->getUser()->setIdentity(new User(['admin' => false]));

        $this->expectException(ForbiddenHttpException::class);
        (new MetadataController('metadata', $this->module))->actionIndex();
    }

    public function testMetadataDownloadReturnsOwnServiceProviderMetadata(): void
    {
        [, $sp] = $this->saveProviders();
        \Craft::$app->getUser()->setIdentity(new User(['admin' => true]));

        $xml = (new MetadataController('metadata', $this->module))->actionIndex();

        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xml));
        $this->assertSame('EntityDescriptor', $document->documentElement->localName);
        $this->assertSame($sp->getEntityId(), $document->documentElement->getAttribute('entityID'));
    }

    public function testMetadataDownloadFailsWhenThisServerIsNotConfigured(): void
    {
        \Craft::$app->getUser()->setIdentity(new User(['admin' => true]));
        $this->module->getSettings()->setEntityId('https://not-configured.example.com');

        $this->expectException(InvalidMetadata::class);
        (new MetadataController('metadata', $this->module))->actionIndex();
    }

    /**
     * @dataProvider keychainNavProvider
     */
    public function testKeychainViewsHighlightTheKeychainSubnav(string $controllerClass, array $segments, ?string $expected): void
    {
        $this->setSegments($segments);
        $controller = new $controllerClass('keychain', $this->module);

        $variables = $this->call($controller, 'beforeRender', ['title' => 'Keys']);

        $this->assertSame($expected, $variables['selectedSubnavItem']);
        $this->assertSame('Keys', $variables['title']);
        $this->assertSame($this->module, $this->call($controller, 'getPlugin'));
    }

    public function keychainNavProvider(): array
    {
        return [
            'index' => [KeychainIndexController::class, ['saml-sp', 'keychain'], 'saml.keychain'],
            'index elsewhere' => [KeychainIndexController::class, ['saml-sp', 'settings'], null],
            'edit' => [KeychainEditController::class, ['saml-sp', 'keychain', '12'], 'saml.keychain'],
            'edit new' => [KeychainEditController::class, ['saml-sp', 'keychain', 'new-openssl'], 'saml.keychain'],
            'edit elsewhere' => [KeychainEditController::class, ['saml-sp', 'metadata', '12'], null],
        ];
    }

    /**
     * @return ProviderRecord[] [idp, sp]
     */
    private function saveProviders(): array
    {
        $idp = $this->metadata->createTheirProviderWithSigningKey($this->module);
        $this->module->getProvider()->save($idp);

        $sp = $this->metadata->createMyProviderWithKey($this->module);
        $sp->keychain->pluginHandle = $this->module->getHandle();
        KeyChain::getInstance()->getService()->save($sp->keychain);
        $this->module->getProvider()->save($sp);

        return [$idp, $sp];
    }

    private function call(object $object, string $method, mixed ...$args): mixed
    {
        return (new \ReflectionMethod($object, $method))->invoke($object, ...$args);
    }

    /**
     * Craft derives request segments from the URL once; tests set them directly.
     */
    private function setSegments(?array $segments): void
    {
        $request = \Craft::$app->getRequest();
        \Closure::bind(function() use ($segments) {
            if ($segments === null) {
                unset($this->_segments);
            } else {
                $this->_segments = $segments;
            }
        }, $request, Request::class)();
    }
}
