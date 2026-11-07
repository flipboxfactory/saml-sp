<?php

namespace flipbox\saml\sp\tests\unit\controllers;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\keychain\KeyChain;
use flipbox\saml\sp\controllers\LoginController;
use flipbox\saml\sp\events\RelayState;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\Constants;
use SAML2\Response as SamlResponse;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\Response;
use Step\Unit\Common\SamlPlugin;
use yii\base\ExitException;
use yii\base\InlineAction;
use yii\web\HttpException;

class LoginControllerTest extends Unit
{
    use \ScopedEvents;

    private const RELAY_STATE = 'http://test.craftcms.test/after-login';

    private const IDP_SSO_URL = 'http://www.flipboxlabs.com/idp/sso';

    private Saml $module;

    private Metadata $metadata;

    private Response $responses;

    private array $server;

    private array $post;

    private array $get;

    protected function _before(): void
    {
        $this->server = $_SERVER;
        $this->post = $_POST;
        $this->get = $_GET;

        $this->module = new Saml('saml-sp');
        $scenario = new Scenario($this);
        (new SamlPlugin($this->module, $scenario))->installIfNeeded();
        $this->module->loadSaml2Container();

        $this->metadata = new Metadata($this->module, $scenario);
        $this->responses = new Response($this->module, $this->metadata, $scenario);

        // Craft only creates sessions for requests with a user agent and IP
        \Craft::$app->getRequest()->getHeaders()->set('User-Agent', 'Codeception');
        \Craft::$app->getUser()->logout(false);
    }

    protected function _after(): void
    {
        $_SERVER = $this->server;
        $_POST = $this->post;
        $_GET = $this->get;
        \Craft::$app->getRequest()->setBodyParams(null);
        \Craft::$app->getRequest()->setQueryParams($this->get);
        \Craft::$app->getUser()->logout(false);
        $this->offScopedEvents();
    }

    /*
     * actionIndex: ACS endpoint
     */

    public function testValidSignedResponseLogsUserInAndRedirectsToRelayState(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), base64_encode(self::RELAY_STATE));

        $response = $this->controller()->actionIndex();

        $this->assertSame(self::RELAY_STATE, $response->getHeaders()->get('Location'));
        $this->assertLoggedInAs($this->responses->getEmail());
    }

    public function testRelayStateIsUsedAsIsWhenEncodingIsDisabled(): void
    {
        $this->module->getSettings()->encodeRelayState = false;
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), self::RELAY_STATE);

        $response = $this->controller()->actionIndex();

        $this->assertSame(self::RELAY_STATE, $response->getHeaders()->get('Location'));
    }

    public function testRelayStateEventCanChangeTheRedirect(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), base64_encode(self::RELAY_STATE));

        $seen = null;
        $this->onEvent(
            LoginController::class,
            LoginController::EVENT_BEFORE_RELAYSTATE_REDIRECT,
            function(RelayState $event) use (&$seen) {
                $seen = $event;
                $event->redirect = 'http://test.craftcms.test/custom';
            }
        );

        $response = $this->controller()->actionIndex();

        $this->assertSame('http://test.craftcms.test/custom', $response->getHeaders()->get('Location'));
        $this->assertSame($idp->getEntityId(), $seen->idp->getEntityId());
        $this->assertSame($sp->getEntityId(), $seen->sp->getEntityId());
        $this->assertSame(self::RELAY_STATE, base64_decode($seen->relayState));
    }

    public function testServiceProviderCanBeSelectedByUid(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), base64_encode(self::RELAY_STATE));

        $this->controller()->actionIndex($sp->uid);

        $this->assertLoggedInAs($this->responses->getEmail());
    }

    public function testUnknownIdentityProviderIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->getIssuer()->setValue('https://unknown-idp.example.com');
        $this->postResponse($response);

        $this->assertRejected(HttpException::class, 'Identity Provider is not found');
    }

    public function testDisabledIdentityProviderIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $idp->enabled = false;
        $this->module->getProvider()->save($idp);
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp));

        $this->assertRejected(HttpException::class, 'Identity Provider is not found');
    }

    public function testMissingServiceProviderIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp));
        $this->module->getSettings()->setEntityId('https://not-this-sp.example.com');

        $this->assertRejected(HttpException::class, 'Service Provider is not found');
    }

    public function testUnknownServiceProviderUidIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp));

        $this->assertRejected(HttpException::class, 'Service Provider is not found', 'not-a-real-uid');
    }

    public function testExpiredAssertionIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->getAssertions()[0]->setNotOnOrAfter((new \DateTime('-5 minutes'))->getTimestamp());
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'Errors during validation');
    }

    public function testAssertionNotYetValidIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->getAssertions()[0]->setNotBefore((new \DateTime('+10 minutes'))->getTimestamp());
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'Errors during validation');
    }

    public function testResponseSignedWithTheWrongKeyIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->setSignatureKey($this->metadata->spPrivateKey());
        $response->getAssertions()[0]->setSignatureKey($this->metadata->spPrivateKey());
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'Unable to validate Signature');
    }

    public function testUnsignedResponseIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->setSignatureKey(null);
        $response->getAssertions()[0]->setSignatureKey(null);
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'Signature required but not found: Response');
    }

    public function testResponseForAnotherDestinationIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->setDestination('https://attacker.example.com/acs');
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'Errors during validation');
    }

    public function testUnsuccessfulStatusIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->setStatus(['Code' => Constants::STATUS_RESPONDER, 'Message' => 'Authentication failed']);
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'Errors during validation');
    }

    public function testRequestWithoutSamlResponseIsRejected(): void
    {
        $this->saveProviders();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        $this->assertRejected(\Exception::class, 'Missing SAMLRequest or SAMLResponse parameter');
    }

    public function testIndexAndRequestActionsSkipCraftsLoginRequirement(): void
    {
        $controller = $this->controller();

        $this->assertTrue($controller->beforeAction(new InlineAction('index', $controller, 'actionIndex')));
        $this->assertTrue($controller->beforeAction(new InlineAction('request', $controller, 'actionRequest')));
    }

    public function testOtherActionsRequireLogin(): void
    {
        $controller = $this->controller();

        try {
            $allowed = $controller->beforeAction(new InlineAction('other', $controller, 'actionOther'));
        } catch (ExitException|\yii\web\ForbiddenHttpException) {
            $allowed = false;
        }

        $this->assertFalse($allowed);
    }

    /*
     * actionRequest: SP-initiated login
     */

    public function testRequestSendsSignedAuthnRequestToIdentityProvider(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $_GET = ['RelayState' => self::RELAY_STATE];
        \Craft::$app->getRequest()->setQueryParams($_GET);

        $html = $this->sendRequest($idp->uid, $sp->uid);

        $authnRequest = $this->postedMessage($html, 'SAMLRequest');
        $this->assertSame('AuthnRequest', $authnRequest->documentElement->localName);
        $this->assertSame(
            $this->module->getSession()->getRequestId(),
            $authnRequest->documentElement->getAttribute('ID'),
            'The AuthnRequest ID must be stored to validate InResponseTo on the way back'
        );
        $this->assertStringContainsString('action="' . self::IDP_SSO_URL . '"', $html);
        $this->assertSame(self::RELAY_STATE, base64_decode($this->postedValue($html, 'RelayState')));
    }

    public function testRequestRelayStateEventCanChangeTheRelayState(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->onEvent(
            LoginController::class,
            LoginController::EVENT_AFTER_RELAYSTATE_CREATION,
            function(RelayState $event) {
                $event->relayState = 'custom-relay-state';
            }
        );

        $html = $this->sendRequest($idp->uid, $sp->uid);

        $this->assertSame('custom-relay-state', $this->postedValue($html, 'RelayState'));
    }

    public function testRequestFallsBackToOwnServiceProvider(): void
    {
        [$idp] = $this->saveProviders();
        $this->module->getSettings()->encodeRelayState = false;

        $html = $this->sendRequest($idp->uid);

        $this->assertSame('AuthnRequest', $this->postedMessage($html, 'SAMLRequest')->documentElement->localName);
    }

    public function testRequestWithUnknownIdentityProviderIsRejected(): void
    {
        $this->saveProviders();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Identity Provider is not found with UID: not-a-real-uid');
        $this->controller()->actionRequest('not-a-real-uid');
    }

    public function testRequestWithoutServiceProviderIsRejected(): void
    {
        [$idp] = $this->saveProviders();
        $this->module->getSettings()->setEntityId('https://not-this-sp.example.com');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Service Provider is not found');
        $this->controller()->actionRequest($idp->uid);
    }

    /**
     * @return ProviderRecord[] [idp, sp]
     */
    private function saveProviders(): array
    {
        $idp = $this->metadata->createTheirProviderWithSigningKey($this->module);
        $this->responses->setAttributeMapping($idp);
        $this->module->getProvider()->save($idp);

        $sp = $this->metadata->createMyProviderWithKey($this->module);
        $sp->keychain->pluginHandle = $this->module->getHandle();
        KeyChain::getInstance()->getService()->save($sp->keychain);
        $this->module->getProvider()->save($sp);

        return [$idp, $sp];
    }

    private function postResponse(SamlResponse $response, ?string $relayState = null): void
    {
        $xml = $response->toSignedXML();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['SAMLResponse' => base64_encode($xml->ownerDocument->saveXML($xml))];
        if ($relayState !== null) {
            $_POST['RelayState'] = $relayState;
        }
        \Craft::$app->getRequest()->setBodyParams($_POST);
    }

    private function assertRejected(string $exception, ?string $message = null, ?string $uid = null): void
    {
        try {
            $this->controller()->actionIndex($uid);
            $this->fail('The response was accepted.');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->assertInstanceOf($exception, $e);
            if ($message !== null) {
                $this->assertStringContainsString($message, $e->getMessage());
            }
        }

        $this->assertTrue(\Craft::$app->getUser()->getIsGuest(), 'No user may be logged in after a rejected response');
    }

    private function assertLoggedInAs(string $email): void
    {
        $identity = \Craft::$app->getUser()->getIdentity();
        $this->assertInstanceOf(User::class, $identity);
        $this->assertSame($email, $identity->email);
    }

    /**
     * Runs actionRequest, which renders the HTTP-POST auto-submit form and ends the request.
     */
    private function sendRequest(?string $idpUid, ?string $spUid = null): string
    {
        ob_start();
        try {
            $this->controller()->actionRequest($idpUid, $spUid);
            $this->fail('Expected the request to end after sending the AuthnRequest.');
        } catch (ExitException) {
        } finally {
            $output = ob_get_clean();
        }

        return (string)(\Craft::$app->getResponse()->data ?: $output);
    }

    private function postedValue(string $html, string $name): string
    {
        $pattern = '/name="' . $name . '" value="([^"]*)"/';
        $this->assertMatchesRegularExpression($pattern, $html);
        preg_match($pattern, $html, $matches);

        return html_entity_decode($matches[1]);
    }

    private function postedMessage(string $html, string $name): \DOMDocument
    {
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML(base64_decode($this->postedValue($html, $name), true)));

        return $document;
    }

    private function controller(): LoginController
    {
        return new LoginController('login', $this->module);
    }
}
