<?php

namespace flipbox\saml\sp\tests\unit\misc;

use Codeception\Test\Unit;
use craft\helpers\Db;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;

class ProviderIdentityRecordTest extends Unit
{
    use ConsoleFixtures;

    public function testTableName(): void
    {
        $this->assertSame('{{%saml_sp_provider_identity}}', ProviderIdentityRecord::tableName());
    }

    public function testRelationsResolveProviderAndUser(): void
    {
        $provider = $this->saveIdpProvider($this);
        $user = $this->saveUser(['active' => true]);

        $identity = new ProviderIdentityRecord([
            'providerId' => $provider->id,
            'userId' => $user->id,
            'nameId' => 'name-id-' . uniqid(),
            'lastLoginDate' => Db::prepareDateForDb(new \DateTime()),
        ]);
        $this->assertTrue($identity->save(), json_encode($identity->getErrors()));

        $reloaded = ProviderIdentityRecord::findOne($identity->id);
        $this->assertInstanceOf(ProviderRecord::class, $reloaded->provider);
        $this->assertSame($provider->id, $reloaded->provider->id);
        $this->assertSame($user->id, $reloaded->getUser()->id);
    }
}
