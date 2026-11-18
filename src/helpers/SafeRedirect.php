<?php

namespace flipbox\saml\sp\helpers;

use flipbox\saml\sp\Saml;

/**
 * Validates redirect targets that come from untrusted input, such as RelayState.
 *
 * RelayState travels as a plain form field beside the signed SAMLResponse, so whoever submits the
 * Response chooses it. Only same-site targets are allowed: a path on this host, or an http(s) URL
 * whose host is the current request's, one of the Craft sites' hosts, or in allowedRedirectHosts.
 */
class SafeRedirect
{
    /**
     * @return string|null the URL if it's a same-site target, otherwise null
     */
    public static function sameSite(?string $url): ?string
    {
        if ($url === null || $url === '' || strlen($url) > 2048) {
            return null;
        }

        // Backslashes and control characters are normalised differently by browsers (e.g. "/\evil.com")
        if (preg_match('/[\\\\\x00-\x1F\x7F]/', $url)) {
            return null;
        }

        // A path on this host, but not protocol-relative ("//evil.com")
        if ($url[0] === '/') {
            return str_starts_with($url, '//') ? null : $url;
        }

        $parts = parse_url($url);
        if (
            $parts === false
            || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return null;
        }

        return in_array(strtolower($parts['host']), static::allowedHosts(), true) ? $url : null;
    }

    /**
     * @return string[]
     */
    protected static function allowedHosts(): array
    {
        $hosts = [];

        $requestHost = \Craft::$app->getRequest()->getHostName();
        if ($requestHost) {
            $hosts[] = strtolower($requestHost);
        }

        foreach (\Craft::$app->getSites()->getAllSites(true) as $site) {
            $host = $site->getBaseUrl() ? parse_url($site->getBaseUrl(), PHP_URL_HOST) : null;
            if ($host) {
                $hosts[] = strtolower($host);
            }
        }

        foreach ((array)Saml::getInstance()->getSettings()->allowedRedirectHosts as $host) {
            if (is_string($host) && trim($host) !== '') {
                $hosts[] = strtolower(trim($host));
            }
        }

        return array_values(array_unique($hosts));
    }
}
