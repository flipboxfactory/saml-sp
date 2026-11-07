<?php

namespace sandbox;

use Craft;
use craft\console\Controller;
use craft\elements\User;
use craft\enums\CmsEdition;
use craft\models\UserGroup;
use flipbox\keychain\KeyChain;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;
use yii\console\ExitCode;

/**
 * Sandbox only: configures saml-sp the way an admin would in the CP.
 *
 *   php craft sandbox/setup <idp-metadata.xml> <output.json>
 *   php craft sandbox/set-groups <email> [handle,handle]   (used by dev/idp-login --test-groups)
 *   php craft sandbox/groups <email>
 */
class SetupController extends Controller
{
    public function actionSetup(string $idpMetadataPath, string $outputPath): int
    {
        // Solo allows a single user; SAML needs to create users and groups. Pro is free on localhost.
        Craft::$app->setEdition(CmsEdition::Pro);

        $plugin = Saml::getInstance();
        $settings = $plugin->getSettings();
        $providers = $plugin->getProvider();

        // This site's SP, with a fresh signing/encryption key pair
        $keyPair = KeyChain::getInstance()->getService()->generateOpenssl([
            'pluginHandle' => $plugin->handle,
            'description' => 'Sandbox SP key pair',
        ]);

        $sp = new ProviderRecord();
        $sp->loadDefaultValues();
        $sp->providerType = SettingsInterface::SP;
        $sp->label = 'Sandbox SP';
        $sp->entityId = $settings->getEntityId();
        $sp->enabled = true;
        $sp->generateUid();
        $sp->setKeychain($keyPair);

        $spModel = $providers->create($plugin->getMetadata()->create($settings, $sp), $keyPair);
        $sp->metadata = $spModel->metadata;
        $sp->setMetadataModel($spModel->getMetadataModel());
        $providers->save($sp);

        // The saml-tool IdP, from the metadata dev/up generated
        $idpDescriptor = new EntityDescriptor(DOMDocumentFactory::fromFile($idpMetadataPath)->documentElement);
        $idp = $providers->create($idpDescriptor);
        $idp->providerType = SettingsInterface::IDP;
        $idp->label = 'saml-tool';
        $idp->enabled = true;
        $providers->save($idp);

        // Groups for testing group sync (send a "groups" attribute with these handles/names)
        $groupIds = [];
        foreach (['Editors' => 'editors', 'Reviewers' => 'reviewers'] as $name => $handle) {
            $group = new UserGroup(['name' => $name, 'handle' => $handle]);
            Craft::$app->getUserGroups()->saveGroup($group);
            $groupIds[$handle] = $group->id;
        }

        $base = rtrim((string)getenv('SANDBOX_URL'), '/');
        $info = [
            'siteUrl' => $base,
            'cpUrl' => $base . '/admin',
            'sp' => [
                'uid' => $sp->uid,
                'entityId' => $sp->entityId,
                'acsUrl' => $sp->firstSpAcsService()->getLocation(),
                'metadataUrl' => $base . '/actions/saml-sp/metadata',
            ],
            'idp' => [
                'uid' => $idp->uid,
                'entityId' => $idp->entityId,
                'loginRequestUrl' => $base . $idp->getLoginRequestPath(),
                'logoutRequestUrl' => $base . $idp->getLogoutRequestPath(),
            ],
            'groups' => $groupIds,
        ];

        file_put_contents($outputPath, json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        file_put_contents(dirname($outputPath) . '/sp-metadata.xml', $sp->metadata);
        $this->stdout(json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return ExitCode::OK;
    }

    /**
     * Creates the user (active) if needed and sets their groups to exactly these handles.
     */
    public function actionSetGroups(string $email, string $handles = ''): int
    {
        $user = User::find()->email($email)->status(null)->one();
        if (!$user) {
            $user = new User(['username' => $email, 'email' => $email, 'active' => true]);
            if (!Craft::$app->getElements()->saveElement($user, false)) {
                $this->stderr("Could not create $email\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        $ids = [];
        foreach (array_filter(explode(',', $handles)) as $handle) {
            $group = Craft::$app->getUserGroups()->getGroupByHandle($handle);
            if (!$group) {
                $this->stderr("No group with handle $handle\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $ids[] = $group->id;
        }
        Craft::$app->getUsers()->assignUserToGroups($user->id, $ids);

        return ExitCode::OK;
    }

    /**
     * Prints the user's stored group handles, sorted and comma-separated ("-" for none).
     */
    public function actionGroups(string $email): int
    {
        $user = User::find()->email($email)->status(null)->one();
        $handles = $user ? array_map(fn(UserGroup $g) => $g->handle, Craft::$app->getUserGroups()->getGroupsByUserId($user->id)) : [];
        sort($handles);
        $this->stdout(($handles ? implode(',', $handles) : '-') . "\n");

        return ExitCode::OK;
    }
}
