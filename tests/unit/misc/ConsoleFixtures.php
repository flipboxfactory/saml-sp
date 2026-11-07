<?php

namespace flipbox\saml\sp\tests\unit\misc;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use Step\Unit\Common\Metadata;

/**
 * Shared builders for the misc tests: persisted IdP providers and users.
 */
trait ConsoleFixtures
{
    protected function saveIdpProvider(Unit $test, array $attributes = []): ProviderRecord
    {
        $module = Saml::getInstance();
        $factory = new Metadata($module, new Scenario($test));

        $descriptor = $factory->createIdpEntityDescriptor();
        $descriptor->setEntityID('https://idp.example.com/' . uniqid());

        /** @var ProviderRecord $provider */
        $provider = $factory->createIdpProviderWithEntityDescriptor($module, $descriptor);
        \Craft::configure($provider, $attributes);
        $module->getProvider()->save($provider);

        return ProviderRecord::findOne($provider->id);
    }

    protected function saveUser(array $attributes = []): User
    {
        $handle = uniqid('saml');
        $user = new User(array_merge([
            'username' => $handle,
            'email' => $handle . '@example.com',
        ], $attributes));

        $this->assertTrue(\Craft::$app->getElements()->saveElement($user, false), 'User could not be saved.');

        return $user;
    }

    protected function reloadUser(User $user): User
    {
        return \Craft::$app->getUsers()->getUserById($user->id)
            ?? User::find()->id($user->id)->status(null)->one();
    }
}
