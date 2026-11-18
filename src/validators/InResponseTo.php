<?php

namespace flipbox\saml\sp\validators;

use flipbox\saml\sp\Saml;
use SAML2\Response as SamlResponse;
use SAML2\XML\saml\SubjectConfirmation;

/**
 * Ties a Response to an AuthnRequest this SP issued, and lets each one be answered only once.
 *
 * Outstanding request IDs are kept in Craft's cache rather than only the session: the IdP posts the
 * Response cross-site, and with a SameSite Lax/Strict session cookie the session never arrives. When it
 * does arrive, it must be the session that sent the AuthnRequest.
 *
 * The InResponseTo of the Response and of every SubjectConfirmationData is checked, so stripping it from
 * an unsigned Response doesn't turn a captured solicited assertion into an unsolicited one.
 */
class InResponseTo
{
    public const CACHE_KEY_PREFIX = 'saml-sp/authn-request/';

    /**
     * How long an AuthnRequest may stay unanswered, in seconds.
     */
    public const TTL = 3600;

    /**
     * Request IDs this response has already been matched to (and so consumed).
     *
     * @var string[]
     */
    private array $accepted = [];

    private bool $solicited = false;

    public function __construct(
        private bool $allowUnsolicited = true,
    ) {
    }

    /**
     * Record an AuthnRequest sent to an IdP so its Response can be matched.
     */
    public static function remember(string $requestId): void
    {
        \Craft::$app->getCache()->set(
            static::cacheKey($requestId),
            Saml::getInstance()->getSession()->getId(),
            static::TTL
        );
    }

    /**
     * @return string[] errors
     */
    public function validateResponse(SamlResponse $response): array
    {
        return $this->check($response->getInResponseTo(), 'Response');
    }

    /**
     * @return string[] errors
     */
    public function validateSubjectConfirmation(SubjectConfirmation $subjectConfirmation): array
    {
        return $this->check(
            $subjectConfirmation->getSubjectConfirmationData()?->getInResponseTo(),
            'SubjectConfirmationData'
        );
    }

    /**
     * Call once every Response and assertion check is done.
     *
     * @return string[] errors
     */
    public function finish(): array
    {
        if (!$this->solicited && !$this->allowUnsolicited) {
            return ['Unsolicited (IdP-initiated) responses are not allowed; the Response has no InResponseTo.'];
        }

        return [];
    }

    /**
     * @return string[] errors
     */
    private function check(?string $inResponseTo, string $element): array
    {
        if ($inResponseTo === null || $inResponseTo === '') {
            return [];
        }

        $this->solicited = true;

        if (in_array($inResponseTo, $this->accepted, true) || $this->consume($inResponseTo)) {
            $this->accepted[] = $inResponseTo;
            return [];
        }

        return [
            sprintf(
                'The %s InResponseTo does not match an outstanding AuthnRequest from this browser; ' .
                'it is unknown, expired or already used.',
                $element
            ),
        ];
    }

    /**
     * Single use: the request ID is removed whether or not the session matches.
     */
    private function consume(string $requestId): bool
    {
        $cache = \Craft::$app->getCache();
        $key = static::cacheKey($requestId);
        $sessionId = $cache->get($key);
        if ($sessionId === false) {
            return false;
        }
        $cache->delete($key);

        // The session cookie is often absent on the IdP's cross-site POST; only compare when it's there.
        $session = \Craft::$app->getSession();
        if ($session->getHasSessionId()) {
            // Nothing may have opened it yet in this request (e.g. an /actions/ ACS URL), and session_id()
            // is empty until it's open
            $session->open();
        }
        if ($session->getHasSessionId() && !hash_equals((string)$sessionId, Saml::getInstance()->getSession()->getId())) {
            return false;
        }

        return true;
    }

    private static function cacheKey(string $requestId): string
    {
        return static::CACHE_KEY_PREFIX . hash('sha256', $requestId);
    }
}
