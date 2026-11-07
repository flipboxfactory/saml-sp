<?php

namespace flipbox\saml\sp\tests\unit\coverage\validators;

use Codeception\Test\Unit;
use flipbox\saml\sp\validators\Response as ResponseValidator;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

class ResponseValidatorTest extends Unit
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

    public function testValidSignedResponsePasses(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();

        $result = (new ResponseValidator($idp, $sp))->validate($this->signedResponse($idp, $sp));

        $this->assertSame([], $result->getErrors());
        $this->assertTrue($result->isValid());
    }

    public function testDestinationMismatchIsRejected(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->setDestination('https://attacker.example.com/acs');

        $result = (new ResponseValidator($idp, $sp))->validate($response);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Destination', implode(' ', $result->getErrors()));
    }

    public function testUnsuccessfulStatusIsRejected(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->setStatus(['Code' => Constants::STATUS_RESPONDER, 'Message' => 'Authentication failed']);

        $result = (new ResponseValidator($idp, $sp))->validate($response);

        $this->assertFalse($result->isValid());
    }

    public function testUnsignedResponseIsRejectedWhenSignatureRequired(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Signature required but not found: Response');

        (new ResponseValidator($idp, $sp, true, true))->validate($this->unsignedResponse($idp, $sp));
    }

    public function testSignedResponseWithUnsignedAssertionIsRejectedWhenAssertionSignatureRequired(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->responseFactory->createSuccessfulResponse($idp, $sp);
        $response->getAssertions()[0]->setSignatureKey(null);
        $parsed = $this->parse($response->toSignedXML());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Signature required but not found: Assertion');

        (new ResponseValidator($idp, $sp, true, true))->validate($parsed);
    }

    public function testResponseSignedWithUntrustedKeyIsRejected(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->responseFactory->createSuccessfulResponse($idp, $sp);
        // Sign everything with a key the IdP metadata doesn't vouch for
        $attackerKey = $this->metadataFactory->spPrivateKey();
        $response->setSignatureKey($attackerKey);
        $response->getAssertions()[0]->setSignatureKey($attackerKey);
        $parsed = $this->parse($response->toSignedXML());

        $this->expectException(\Exception::class);

        (new ResponseValidator($idp, $sp))->validate($parsed);
    }

    public function testIdpWithoutSigningKeysCannotSatisfyRequiredResponseSignature(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Response must be signed');

        new ResponseValidator($this->idpWithoutKeys(), $this->sp(), true, true);
    }

    public function testIdpWithoutSigningKeysCannotSatisfyRequiredAssertionSignature(): void
    {
        $idp = $this->idpWithoutKeys();
        $sp = $this->sp();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Assertion must be signed');

        (new ResponseValidator($idp, $sp, false, true))->validate($this->unsignedResponse($idp, $sp));
    }

    public function testAssertionErrorsAreReportedOnTheResponse(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->getSubjectConfirmation()[0]
            ->getSubjectConfirmationData()->setRecipient('https://attacker.example.com/acs');

        $result = (new ResponseValidator($idp, $sp))->validate($response);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Recipient', implode(' ', $result->getErrors()));
    }

    public function testResponseSignedWithSha512IsAccepted(): void
    {
        // The IdP key store comes from metadata certificates; any RSA digest they can verify is accepted
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->responseFactory->createSuccessfulResponse($idp, $sp);
        $response->setSignatureKey($this->metadataFactory->idpPrivateKey(XMLSecurityKey::RSA_SHA512));

        $result = (new ResponseValidator($idp, $sp))->validate($this->parse($response->toSignedXML()));

        $this->assertSame([], $result->getErrors());
    }
}
