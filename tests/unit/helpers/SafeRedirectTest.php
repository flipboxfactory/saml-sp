<?php

namespace flipbox\saml\sp\tests\unit\helpers;

use Codeception\Test\Unit;
use flipbox\saml\sp\helpers\SafeRedirect;
use flipbox\saml\sp\Saml;

class SafeRedirectTest extends Unit
{
    /**
     * @dataProvider sameSite
     */
    public function testSameSiteTargetsAreAllowed(string $url): void
    {
        $this->assertSame($url, SafeRedirect::sameSite($url));
    }

    public static function sameSite(): array
    {
        return [
            'path' => ['/account'],
            'path with query and fragment' => ['/a/b?x=1&y=2#top'],
            'site URL' => ['http://test.craftcms.test/after-login'],
            'site URL over https with port' => ['https://test.craftcms.test:8443/x'],
            'site host is case-insensitive' => ['http://TEST.craftcms.test/'],
        ];
    }

    public function testRequestHostIsAllowed(): void
    {
        $host = \Craft::$app->getRequest()->getHostName();
        $this->assertNotEmpty($host);
        $this->assertSame("https://$host/x", SafeRedirect::sameSite("https://$host/x"));
    }

    public function testAllowedRedirectHostsAreAllowed(): void
    {
        $settings = Saml::getInstance()->getSettings();
        $original = $settings->allowedRedirectHosts;
        $this->assertSame([], $original, 'No extra hosts by default');
        $this->assertNull(SafeRedirect::sameSite('https://www.example.com/after-login'));

        try {
            $settings->allowedRedirectHosts = [' WWW.example.com ', '', 42];

            $this->assertSame('https://www.example.com/after-login', SafeRedirect::sameSite('https://www.example.com/after-login'));
            // Exact host only: neither subdomains nor lookalikes of an allowed host
            $this->assertNull(SafeRedirect::sameSite('https://evil.www.example.com/'));
            $this->assertNull(SafeRedirect::sameSite('https://www.example.com.evil.example/'));
        } finally {
            $settings->allowedRedirectHosts = $original;
        }
    }

    /**
     * @dataProvider offSite
     */
    public function testOffSiteAndMalformedTargetsAreRejected(?string $url): void
    {
        $this->assertNull(SafeRedirect::sameSite($url));
    }

    public static function offSite(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'other host' => ['https://evil.example.com/'],
            'protocol-relative' => ['//evil.example.com/'],
            'backslash path' => ['/\\evil.example.com/'],
            'backslash in URL' => ['http://test.craftcms.test\\@evil.example.com/'],
            'tab' => ["/\t/evil.example.com/"],
            'newline (header injection)' => ["/ok\r\nSet-Cookie: x=y"],
            'userinfo' => ['http://test.craftcms.test@evil.example.com/'],
            'credentials on own host' => ['http://user:pass@test.craftcms.test/'],
            'lookalike host' => ['http://test.craftcms.test.evil.example.com/'],
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html,<script>alert(1)</script>'],
            'ftp' => ['ftp://test.craftcms.test/'],
            'scheme without host' => ['http:/evil.example.com'],
            'relative without slash' => ['evil.example.com/path'],
            'too long' => ['/' . str_repeat('a', 2048)],
        ];
    }
}
