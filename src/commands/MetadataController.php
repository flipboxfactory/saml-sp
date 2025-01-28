<?php


namespace flipbox\saml\sp\commands;

use craft\console\Controller;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use yii\console\ExitCode;
use yii\console\widgets\Table;

/**
 * Automate Provider Operations
 * Class MetadataController
 * @package flipbox\saml\sp\commands
 */
class MetadataController extends Controller
{
    public function actionIndex()
    {
        $providerQuery = ProviderRecord::find();

        $rows = [];
        foreach ($providerQuery->all() as $provider) {
            /** @var ProviderRecord $provider */

            $rows[] = [
                $provider->id,
                $provider->getType(),
                $provider->label,
                $provider->entityId,
                $provider->uid,
                $provider->enabled ? 'enabled' : 'disabled',
            ];
        }
        $this->stdout(Table::widget([
            'headers' => [
                'ID',
                'TYPE',
                'LABEL',
                'ENTITY ID',
                'UID',
                'ENABLED/DISABLED',
            ],
            'rows' => $rows,
        ]));
        return ExitCode::OK;
    }

    /**
     * Refresh and save the metadata from the URL saved on the provider.
     * Configure the URL in the Craft Control Panel before using this.
     *
     * @param $uid
     */
    public function actionRefreshWithUrl(string $uid)
    {
        $message =
            sprintf('Trying Provider with uid %s.', $uid);
        $this->stderr($message . PHP_EOL);
        Saml::info($message);

        /** @var ProviderRecord $provider */
        if (!$provider = Saml::getInstance()->getProvider()->findByIdp([
            'uid' => $uid,
        ])->one()) {
            $message =
                sprintf('Provider with uid %s not found.', $uid);
            $this->stderr(
                $message
            );
            Saml::warning($message);
            return ExitCode::DATAERR;
        }

        if (!$url = $provider->getMetadataOptions()->url) {
            $message =
                sprintf('Provider with uid %s does not have a metadata url.', $uid);
            $this->stderr($message . PHP_EOL);
            Saml::warning($message);
            return ExitCode::DATAERR;
        }
        $message =
            sprintf('Provider %s (%s) found with URL %s.', $uid, $provider->getEntityId(), $url);
        $this->stderr($message . PHP_EOL);
        Saml::info($message);

        $entityDescriptor = Saml::getInstance()->getMetadata()->fetchByUrl($url);

        $provider->metadata = $entityDescriptor->toXML()->ownerDocument->saveXML();
        $provider->setMetadataModel($entityDescriptor);

        Saml::getInstance()->getProvider()->save($provider);
        $message = sprintf('Provider %s saved! (%s)', $uid, $entityDescriptor->getEntityID());
        Saml::info($message);
        $this->stdout($message . PHP_EOL);

        return ExitCode::OK;
    }
}
