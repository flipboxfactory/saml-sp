<?php

namespace flipbox\saml\sp\tests\unit\coverage\validators;

use Codeception\Test\Unit;
use flipbox\saml\sp\validators\SignedElement;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Assertion\Validation\Result;

class SignedElementValidatorTest extends Unit
{
    use \ServiceFixtures;

    protected function _before(): void
    {
        $this->setUpFixtures();
    }

    protected function _after(): void
    {
        $this->tearDownFixtures();
    }

    public function testSecondKeyVerifiesAfterFirstFails(): void
    {
        // e.g. during IdP certificate rotation
        $assertion = $this->signedResponse($this->idp(), $this->sp())->getAssertions()[0];
        $result = new Result();

        $returned = (new SignedElement([$this->publicKey('saml-sp.crt'), $this->publicKey('saml-idp.crt')]))
            ->validate($assertion, $result);

        $this->assertSame($result, $returned);
        $this->assertSame([], $result->getErrors());
    }

    public function testUntrustedKeysOnlyIsRejected(): void
    {
        $assertion = $this->signedResponse($this->idp(), $this->sp())->getAssertions()[0];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');

        (new SignedElement([$this->publicKey('saml-sp.crt')]))->validate($assertion, new Result());
    }

    public function testUnsignedElementIsRejectedWhenRequired(): void
    {
        $assertion = $this->unsignedResponse($this->idp(), $this->sp())->getAssertions()[0];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Signature required but not found: Assertion');

        (new SignedElement([$this->publicKey('saml-idp.crt')], true, 'Assertion'))->validate($assertion, new Result());
    }

    public function testUnsignedElementIsAllowedWhenNotRequired(): void
    {
        $assertion = $this->unsignedResponse($this->idp(), $this->sp())->getAssertions()[0];
        $result = new Result();

        $returned = (new SignedElement([$this->publicKey('saml-idp.crt')], false))->validate($assertion, $result);

        $this->assertSame($result, $returned);
        $this->assertSame([], $result->getErrors());
    }

    private function publicKey(string $certificateFile): XMLSecurityKey
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $key->loadKey(file_get_contents(codecept_data_dir() . '/keypairs/' . $certificateFile), false, true);

        return $key;
    }
}
