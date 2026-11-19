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

/**
 * Security regression tests for the ACS endpoint, backported from the 5.x LoginControllerTest.
 */
class LoginControllerSecurityTest extends Unit
{
    private const RELAY_STATE = 'http://test.craftcms.test/after-login';

    private const IDP_SSO_URL = 'http://www.flipboxlabs.com/idp/sso';

    /** Origin of the test SP's ACS URL (http://sp.localhost:9090/sso/login) */
    private const ACS_ORIGIN = 'http://sp.localhost:9090';

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
        // Logins normally start on the ACS host
        \Craft::$app->getRequest()->setHostInfo(self::ACS_ORIGIN);
        \Craft::$app->getUser()->logout(false);
    }

    protected function _after(): void
    {
        $_SERVER = $this->server;
        $_POST = $this->post;
        $_GET = $this->get;
        \Craft::$app->getRequest()->setBodyParams(null);
        \Craft::$app->getRequest()->setHostInfo(null);
        \Craft::$app->getRequest()->setUrl(null);
        \Craft::$app->getRequest()->setQueryParams($this->get);
        \Craft::$app->getUser()->logout(false);
    }

    public function testValidSignedResponseLogsUserInAndRedirectsToRelayState(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), base64_encode(self::RELAY_STATE));

        $response = $this->controller()->actionIndex();

        $this->assertSame(self::RELAY_STATE, $response->getHeaders()->get('Location'));
        $this->assertLoggedInAs($this->responses->getEmail());
    }

    /**
     * RelayState is an unsigned form field: whoever posts the Response chooses it.
     *
     * @dataProvider offSiteRelayStates
     */
    public function testOffSiteRelayStateIsNotFollowed(string $relayState): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), base64_encode($relayState));

        $response = $this->controller()->actionIndex();

        $location = (string)$response->getHeaders()->get('Location');
        $sameSiteHosts = ['test.craftcms.test', \Craft::$app->getRequest()->getHostName()];
        $this->assertContains(parse_url($location, PHP_URL_HOST), $sameSiteHosts, "Redirected off-site to $location");
        $this->assertStringNotContainsString('evil', $location);
        $this->assertLoggedInAs($this->responses->getEmail());
    }

    public static function offSiteRelayStates(): array
    {
        return [
            'absolute' => ['https://evil.example.com/login'],
            'protocol-relative' => ['//evil.example.com/login'],
            'backslash' => ['/\\evil.example.com/login'],
            'userinfo' => ['http://test.craftcms.test@evil.example.com/'],
            'javascript' => ['javascript:alert(document.domain)'],
            'subdomain lookalike' => ['http://test.craftcms.test.evil.example.com/'],
            'not base64' => ['%%%not-base64%%%'],
        ];
    }

    public function testSameSitePathRelayStateIsFollowed(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp), base64_encode('/after-login?tab=2'));

        $response = $this->controller()->actionIndex();

        $this->assertSame(
            \Craft::$app->getRequest()->getHostInfo() . '/after-login?tab=2',
            $response->getHeaders()->get('Location')
        );
    }

    public function testAssertionForAnotherAudienceIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->getAssertions()[0]->setValidAudiences(['https://another-sp.example.com']);
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'not a valid audience');
    }

    public function testAssertionWithoutAudienceRestrictionIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->getAssertions()[0]->setValidAudiences(null);
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'no AudienceRestriction');
    }

    public function testResponseToOurAuthnRequestLogsUserIn(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->solicitedResponse($idp, $sp, $this->issueAuthnRequest($idp, $sp)));

        $this->controller()->actionIndex();

        $this->assertLoggedInAs($this->responses->getEmail());
    }

    public function testResponseToOurAuthnRequestCannotBeReplayed(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->solicitedResponse($idp, $sp, $this->issueAuthnRequest($idp, $sp)));
        $this->controller()->actionIndex();
        \Craft::$app->getUser()->logout(false);

        $this->assertRejected(\Exception::class, 'InResponseTo');
    }

    public function testResponseReturnedToTheRequestingSessionLogsUserIn(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $requestId = $this->issueAuthnRequest($idp, $sp);
        \Craft::$app->getSession()->setHasSessionId(true);
        $this->postResponse($this->solicitedResponse($idp, $sp, $requestId));

        $this->controller()->actionIndex();

        $this->assertLoggedInAs($this->responses->getEmail());
    }

    public function testResponseDeliveredToAnotherSessionIsRejected(): void
    {
        // Login CSRF: an attacker starts a login, then makes the victim's browser post the attacker's Response
        [$idp, $sp] = $this->saveProviders();
        $requestId = $this->issueAuthnRequest($idp, $sp);
        $session = \Craft::$app->getSession();
        $before = $session->getId();
        $session->regenerateID(true);
        $this->assertNotSame($before, $session->getId());
        $session->setHasSessionId(true);
        $this->postResponse($this->solicitedResponse($idp, $sp, $requestId));

        $this->assertRejected(\Exception::class, 'InResponseTo');
    }

    public function testResponseToAnUnknownRequestIsRejected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->issueAuthnRequest($idp, $sp);
        $this->postResponse($this->solicitedResponse($idp, $sp, '_not-issued-by-this-sp'));

        $this->assertRejected(\Exception::class, 'InResponseTo');
    }

    public function testSubjectConfirmationInResponseToAnUnknownRequestIsRejected(): void
    {
        // A captured solicited assertion with the (possibly unsigned) Response@InResponseTo stripped
        [$idp, $sp] = $this->saveProviders();
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->getAssertions()[0]->getSubjectConfirmation()[0]->getSubjectConfirmationData()
            ->setInResponseTo('_not-issued-by-this-sp');
        $this->postResponse($response);

        $this->assertRejected(\Exception::class, 'InResponseTo');
    }

    public function testUnsolicitedResponseIsAcceptedWhenIdpInitiatedLoginIsAllowed(): void
    {
        $this->assertTrue($this->module->getSettings()->allowIdpInitiatedLogin, 'Allowed by default');
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp));

        $this->controller()->actionIndex();

        $this->assertLoggedInAs($this->responses->getEmail());
    }

    public function testUnsolicitedResponseIsRejectedWhenIdpInitiatedLoginIsDisabled(): void
    {
        $this->module->getSettings()->allowIdpInitiatedLogin = false;
        [$idp, $sp] = $this->saveProviders();
        $this->postResponse($this->responses->createSuccessfulResponse($idp, $sp));

        $this->assertRejected(\Exception::class, 'Unsolicited');
    }

    /*
     * actionRequest: SP-initiated login
     */

    public function testRequestFromAnotherHostRedirectsToTheAcsHost(): void
    {
        // saml-sp#266: the Response comes back to the ACS host with that host's session cookie, so an
        // AuthnRequest remembered against another host's session can't match it
        [$idp, $sp] = $this->saveProviders();
        $this->startRequestOn('http://cms.example.com', '/sso/login/request/' . $idp->uid, ['foo' => 'bar']);
        \Craft::$app->getUser()->setReturnUrl('/members');

        $response = $this->controller()->actionRequest($idp->uid, $sp->uid);

        $this->assertInstanceOf(\yii\web\Response::class, $response);
        $location = (string)$response->getHeaders()->get('Location');
        $this->assertStringStartsWith(self::ACS_ORIGIN . '/sso/login/request/' . $idp->uid . '?', $location);
        parse_str((string)parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('bar', $query['foo']);
        $this->assertSame('1', $query[LoginController::ACS_HOST_PARAM]);
        $this->assertStringEndsWith('/members', $query['RelayState'], 'The return URL must survive the host change');
        $this->assertNull(
            $this->module->getSession()->getRequestId(),
            'No AuthnRequest may be issued from the wrong host'
        );
    }

    public function testRequestFromAnotherHostKeepsTheRelayStateParam(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->startRequestOn('http://cms.example.com', '/sso/login/request/' . $idp->uid, ['RelayState' => '/after']);

        $response = $this->controller()->actionRequest($idp->uid, $sp->uid);

        parse_str((string)parse_url((string)$response->getHeaders()->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('/after', $query['RelayState']);
    }

    public function testRequestOnTheAcsHostInAnotherCaseIsNotRedirected(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->startRequestOn('HTTP://SP.LOCALHOST:9090', '/sso/login/request/' . $idp->uid);

        $html = $this->sendRequest($idp->uid, $sp->uid);

        $this->assertSame('AuthnRequest', $this->postedMessage($html, 'SAMLRequest')->documentElement->localName);
    }

    public function testRequestStillOnAnotherHostAfterTheRedirectSendsTheAuthnRequest(): void
    {
        // Don't loop when a proxy hides the ACS host; InResponseTo still checks the Response
        [$idp, $sp] = $this->saveProviders();
        $this->startRequestOn(
            'http://cms.example.com',
            '/sso/login/request/' . $idp->uid,
            [LoginController::ACS_HOST_PARAM => '1']
        );

        $html = $this->sendRequest($idp->uid, $sp->uid);

        $this->assertSame('AuthnRequest', $this->postedMessage($html, 'SAMLRequest')->documentElement->localName);
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

    /**
     * Runs actionRequest and returns the ID of the AuthnRequest sent to the IdP.
     */
    private function issueAuthnRequest(ProviderRecord $idp, ProviderRecord $sp): string
    {
        $html = $this->sendRequest($idp->uid, $sp->uid);
        \Craft::$app->getResponse()->data = null;

        return $this->postedMessage($html, 'SAMLRequest')->documentElement->getAttribute('ID');
    }

    private function startRequestOn(string $hostInfo, string $path, array $query = []): void
    {
        $request = \Craft::$app->getRequest();
        $request->setHostInfo($hostInfo);
        $request->setUrl($path . ($query ? '?' . http_build_query($query) : ''));
        $_GET = $query;
        $request->setQueryParams($query);
    }

    private function solicitedResponse(ProviderRecord $idp, ProviderRecord $sp, string $requestId): SamlResponse
    {
        $response = $this->responses->createSuccessfulResponse($idp, $sp);
        $response->setInResponseTo($requestId);
        $response->getAssertions()[0]->getSubjectConfirmation()[0]->getSubjectConfirmationData()
            ->setInResponseTo($requestId);

        return $response;
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
