<?php

namespace sandbox;

use Craft;
use craft\console\Controller;
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
        foreach (['Editors' => 'editors', 'Reviewers' => 'reviewers'] as $name => $handle) {
            Craft::$app->getUserGroups()->saveGroup(new UserGroup(['name' => $name, 'handle' => $handle]));
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
        ];

        file_put_contents($outputPath, json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        file_put_contents(dirname($outputPath) . '/sp-metadata.xml', $sp->metadata);
        $this->stdout(json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return ExitCode::OK;
    }
}
