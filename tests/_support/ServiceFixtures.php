<?php

use Codeception\Scenario;
use craft\elements\User;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\DOMDocumentFactory;
use SAML2\EncryptedAssertion;
use SAML2\Message;
use SAML2\Response as SamlResponse;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\Response;

/**
 * Shared providers, signed responses and settings handling for the service/validator tests.
 */
trait ServiceFixtures
{
    protected Saml $module;

    protected Metadata $metadataFactory;

    protected Response $responseFactory;

    private array $originalSettings = [];

    protected function setUpFixtures(): void
    {
        $plugin = Saml::getInstance() ?? \Craft::$app->getPlugins()->getPlugin('saml-sp');
        $this->module = $plugin instanceof Saml ? $plugin : new Saml('saml-sp');
        $this->module->loadSaml2Container();

        $scenario = new Scenario($this);
        $this->metadataFactory = new Metadata($this->module, $scenario);
        $this->responseFactory = new Response($this->module, $this->metadataFactory, $scenario);

        $this->originalSettings = $this->module->getSettings()->toArray();
    }

    protected function tearDownFixtures(): void
    {
        // Plugin settings are a shared object; undo any per-test changes
        $settings = $this->module->getSettings();
        foreach ($this->originalSettings as $name => $value) {
            if ($settings->canSetProperty($name)) {
                $settings->$name = $value;
            }
        }
    }

    protected function settings(array $overrides = []): Settings
    {
        $settings = $this->module->getSettings();
        foreach ($overrides as $name => $value) {
            $settings->$name = $value;
        }

        return $settings;
    }

    protected function idp(): ProviderRecord
    {
        $idp = $this->metadataFactory->createTheirProviderWithSigningKey($this->module);
        $this->responseFactory->setAttributeMapping($idp);

        return $idp;
    }

    protected function idpWithoutKeys(): ProviderRecord
    {
        return $this->metadataFactory->createTheirProvider($this->module);
    }

    protected function sp(): ProviderRecord
    {
        return $this->metadataFactory->createMyProviderWithKey($this->module);
    }

    /**
     * A response signed by the IdP fixture key and parsed back from XML, as LoginController receives it.
     */
    protected function signedResponse(ProviderRecord $idp, ProviderRecord $sp, bool $encrypted = false): SamlResponse
    {
        $response = $this->responseFactory->createSuccessfulResponse($idp, $sp, $encrypted);

        return $this->parse($response->toSignedXML());
    }

    protected function unsignedResponse(ProviderRecord $idp, ProviderRecord $sp): SamlResponse
    {
        $response = $this->responseFactory->createSuccessfulResponse($idp, $sp);
        $response->setSignatureKey(null);
        foreach ($response->getAssertions() as $assertion) {
            if (!$assertion instanceof EncryptedAssertion) {
                $assertion->setSignatureKey(null);
            }
        }

        return $this->parse($response->toUnsignedXML());
    }

    protected function parse(\DOMElement $element): SamlResponse
    {
        $document = DOMDocumentFactory::fromString($element->ownerDocument->saveXML($element));
        /** @var SamlResponse $parsed */
        $parsed = Message::fromXML($document->documentElement);

        return $parsed;
    }

    protected function savedUser(string $username, ?string $email = null): User
    {
        $user = new User([
            'username' => $username,
            'email' => $email ?? $username,
        ]);
        if (!\Craft::$app->getElements()->saveElement($user)) {
            throw new \RuntimeException('Could not save user: ' . json_encode($user->getErrors()));
        }

        return $user;
    }
}
