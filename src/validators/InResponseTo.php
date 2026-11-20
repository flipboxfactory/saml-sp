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
     * Marks request IDs that have been answered, to log replays and double posts as such.
     */
    public const USED_CACHE_KEY_PREFIX = 'saml-sp/authn-request-used/';

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

    /**
     * Request IDs this response has already been rejected for, so the Response and its
     * SubjectConfirmationData don't log the same rejection twice.
     *
     * @var string[]
     */
    private array $rejected = [];

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
            [
                'session' => Saml::getInstance()->getSession()->getId(),
                'issuedAt' => time(),
                'server' => static::server(),
            ],
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

        if (in_array($inResponseTo, $this->accepted, true)) {
            return [];
        }
        if (!in_array($inResponseTo, $this->rejected, true)) {
            if ($this->consume($inResponseTo)) {
                $this->accepted[] = $inResponseTo;
                return [];
            }
            $this->rejected[] = $inResponseTo;
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
     *
     * Each rejection is logged with its cause, without session IDs, since the error shown to the user is the
     * same for all of them.
     */
    private function consume(string $requestId): bool
    {
        $cache = \Craft::$app->getCache();
        $key = static::cacheKey($requestId);
        $entry = $cache->get($key);
        if ($entry === false) {
            $usedAt = $cache->get(static::usedKey($requestId));
            Saml::warning(is_int($usedAt)
                ? sprintf(
                    'Rejected InResponseTo %s: its AuthnRequest was already answered %ds ago (the Response was posted more than once).',
                    static::loggable($requestId),
                    max(0, time() - $usedAt)
                )
                : sprintf(
                    'Rejected InResponseTo %s: no AuthnRequest with this ID was issued by this site in the last %ds, '
                    . 'or it was stored in a cache this server can\'t read (check that all servers share the cache).',
                    static::loggable($requestId),
                    static::TTL
                ));
            return false;
        }
        $cache->delete($key);
        $cache->set(static::usedKey($requestId), time(), static::TTL);

        // 5.2.8 and 5.2.9 stored just the session hash
        $issued = is_array($entry) ? $entry : ['session' => $entry];

        // The session cookie is often absent on the IdP's cross-site POST; only compare when it's there.
        $session = \Craft::$app->getSession();
        if ($session->getHasSessionId() && !hash_equals((string)($issued['session'] ?? ''), Saml::getInstance()->getSession()->getId())) {
            Saml::warning(sprintf(
                'Rejected InResponseTo %s: the Response arrived with a session cookie for a different session than the '
                . 'one that sent the AuthnRequest (issued %s on %s; received on %s).',
                static::loggable($requestId),
                isset($issued['issuedAt']) ? sprintf('%ds ago', max(0, time() - (int)$issued['issuedAt'])) : 'earlier',
                static::loggable((string)($issued['server'] ?? 'an unknown server')),
                static::loggable(static::server())
            ));
            return false;
        }

        return true;
    }

    private static function cacheKey(string $requestId): string
    {
        return static::CACHE_KEY_PREFIX . hash('sha256', $requestId);
    }

    private static function usedKey(string $requestId): string
    {
        return static::USED_CACHE_KEY_PREFIX . hash('sha256', $requestId);
    }

    /**
     * The PHP host handling this request, to tell servers apart in the logs.
     */
    private static function server(): string
    {
        return gethostname() ?: 'unknown';
    }

    /**
     * InResponseTo comes from the Response, so keep anything odd out of the logs.
     */
    private static function loggable(string $value): string
    {
        return preg_match('/^[A-Za-z0-9_.:-]{1,128}$/', $value) ? $value : '(invalid value)';
    }
}
