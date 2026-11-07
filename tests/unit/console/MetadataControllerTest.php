<?php

namespace flipbox\saml\sp\tests\unit\console;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\core\services\Metadata as MetadataService;
use flipbox\saml\sp\commands\MetadataController;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;
use Step\Unit\Common\Metadata;
use yii\console\ExitCode;

class MetadataControllerTest extends Unit
{
    private Saml $module;

    private Metadata $metadataFactory;

    private ?object $originalMetadata = null;

    protected function _before(): void
    {
        $this->module = Saml::getInstance();
        $this->metadataFactory = new Metadata($this->module, new Scenario($this));
        $this->originalMetadata = $this->module->get('metadata');
    }

    protected function _after(): void
    {
        $this->module->set('metadata', $this->originalMetadata);
    }

    public function testIndexListsProviders(): void
    {
        $provider = $this->saveIdpProvider('TestIdP');
        $controller = $this->controller();

        $this->assertSame(ExitCode::OK, $controller->actionIndex());

        $rows = $this->parseTable($controller->out);
        $this->assertSame(['ID', 'TYPE', 'LABEL', 'ENTITY ID', 'UID', 'ENABLED/DISABLED'], array_shift($rows));
        $this->assertContains(
            [(string)$provider->id, ProviderRecord::TYPE_IDP, 'TestIdP', $provider->entityId, $provider->uid, 'enabled'],
            $rows
        );
    }

    public function testIndexShowsDisabledProviders(): void
    {
        $provider = $this->saveIdpProvider('DisabledIdP', ['enabled' => false]);
        $controller = $this->controller();

        $controller->actionIndex();

        $byUid = array_column($this->parseTable($controller->out), 5, 4);
        $this->assertSame('disabled', $byUid[$provider->uid]);
    }

    public function testRefreshWithUnknownUidFails(): void
    {
        $controller = $this->controller();

        $this->assertSame(ExitCode::DATAERR, $controller->actionRefreshWithUrl('does-not-exist'));
        $this->assertStringContainsString('Provider with uid does-not-exist not found.', $controller->err);
    }

    public function testRefreshWithoutMetadataUrlFails(): void
    {
        $provider = $this->saveIdpProvider('No URL');
        $controller = $this->controller();

        $this->assertSame(ExitCode::DATAERR, $controller->actionRefreshWithUrl($provider->uid));
        $this->assertStringContainsString('does not have a metadata url', $controller->err);
    }

    public function testRefreshWithUrlFetchesAndSavesMetadata(): void
    {
        $provider = $this->saveIdpProvider('With URL', [
            'metadataOptions' => json_encode(['url' => 'https://idp.example.com/metadata']),
        ]);

        // Refreshed metadata: same entity, now with signing/encryption keys
        $refreshed = $this->metadataFactory->createIdpEntityDescriptorWithKeyDescriptors();
        $refreshed->setEntityID($provider->entityId);
        $this->module->set('metadata', $this->stubMetadataService($refreshed, $requestedUrls));

        $controller = $this->controller();

        $this->assertSame(ExitCode::OK, $controller->actionRefreshWithUrl($provider->uid));
        $this->assertSame(['https://idp.example.com/metadata'], $requestedUrls->getArrayCopy());
        $this->assertStringContainsString('found with URL https://idp.example.com/metadata', $controller->err);
        $this->assertStringContainsString(sprintf('Provider %s saved!', $provider->uid), $controller->out);

        $saved = ProviderRecord::findOne($provider->id);
        $savedDescriptor = new EntityDescriptor(DOMDocumentFactory::fromString($saved->metadata)->documentElement);
        $this->assertSame($provider->entityId, $savedDescriptor->getEntityID());
        $this->assertStringContainsString('KeyDescriptor', $saved->metadata);
        $this->assertNotSame($provider->sha256, $saved->sha256);
    }

    /**
     * Rebuilds the cells of a yii console Table. Cells wider than the terminal are wrapped
     * across several lines, so segments are joined per column within each row.
     *
     * @return string[][]
     */
    private function parseTable(string $output): array
    {
        $rows = [];
        $current = null;
        foreach (preg_split('/\R/u', $output) as $line) {
            if (str_starts_with($line, '║')) {
                $inner = preg_replace('/^║|║\s*$/u', '', $line);
                $cells = array_map('trim', preg_split('/│/u', $inner));
                if ($current === null) {
                    $current = $cells;
                } else {
                    foreach ($cells as $i => $cell) {
                        $current[$i] .= $cell;
                    }
                }
            } elseif ($current !== null) {
                $rows[] = $current;
                $current = null;
            }
        }

        return $rows;
    }

    /**
     * Console controller with stdout/stderr captured instead of written to the terminal.
     */
    private function controller(): MetadataController
    {
        return new class('metadata', $this->module) extends MetadataController {
            public string $out = '';

            public string $err = '';

            public function stdout($string)
            {
                $this->out .= $string;
                return strlen($string);
            }

            public function stderr($string)
            {
                $this->err .= $string;
                return strlen($string);
            }
        };
    }

    private function saveIdpProvider(string $label, array $attributes = []): ProviderRecord
    {
        $descriptor = $this->metadataFactory->createIdpEntityDescriptor();
        $descriptor->setEntityID('https://idp.example.com/' . uniqid());

        /** @var ProviderRecord $provider */
        $provider = $this->metadataFactory->createIdpProviderWithEntityDescriptor($this->module, $descriptor);
        $provider->label = $label;
        \Craft::configure($provider, $attributes);

        $this->module->getProvider()->save($provider);

        return ProviderRecord::findOne($provider->id);
    }

    private function stubMetadataService(EntityDescriptor $descriptor, ?\ArrayObject &$requestedUrls): MetadataService
    {
        $requestedUrls = new \ArrayObject();

        return new class($descriptor, $requestedUrls) extends MetadataService {
            public function __construct(private EntityDescriptor $descriptor, private \ArrayObject $urls)
            {
                parent::__construct();
            }

            public function fetchByUrl(string $url)
            {
                $this->urls->append($url);
                return $this->descriptor;
            }
        };
    }
}
