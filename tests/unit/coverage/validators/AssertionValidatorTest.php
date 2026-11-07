<?php

namespace flipbox\saml\sp\tests\unit\coverage\validators;

use Codeception\Test\Unit;
use flipbox\saml\sp\validators\Assertion as AssertionValidator;
use SAML2\Constants;
use SAML2\EncryptedAssertion;

class AssertionValidatorTest extends Unit
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

    public function testEncryptedAssertionIsDecryptedAndValidated(): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp, true);
        $this->assertInstanceOf(EncryptedAssertion::class, $response->getAssertions()[0]);

        $result = (new AssertionValidator($response, $idp, $sp))->validate($response->getAssertions()[0]);

        $this->assertSame([], $result->getErrors());
    }

    public function testFutureNotBeforeIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->setNotBefore((new \DateTime('+1 hour'))->getTimestamp());
        });

        $this->assertNotEmpty($errors);
    }

    public function testExpiredSessionIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->setSessionNotOnOrAfter((new \DateTime('-1 hour'))->getTimestamp());
        });

        $this->assertNotEmpty($errors);
    }

    public function testNonBearerSubjectConfirmationIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->getSubjectConfirmation()[0]->setMethod(Constants::CM_HOK);
        });

        $this->assertNotEmpty($errors);
    }

    public function testExpiredSubjectConfirmationIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->getSubjectConfirmation()[0]->getSubjectConfirmationData()
                ->setNotOnOrAfter((new \DateTime('-1 hour'))->getTimestamp());
        });

        $this->assertNotEmpty($errors);
    }

    public function testSubjectConfirmationInResponseToMustMatchResponse(): void
    {
        $errors = $this->errorsAfter(function($assertion, $response) {
            $response->setInResponseTo('_expected-request-id');
            $assertion->getSubjectConfirmation()[0]->getSubjectConfirmationData()
                ->setInResponseTo('_some-other-request-id');
        });

        $this->assertNotEmpty($errors);
    }

    public function testIdpWithoutSigningKeysIsAllowedWhenSignatureNotRequired(): void
    {
        $idp = $this->idpWithoutKeys();
        $sp = $this->sp();
        $response = $this->unsignedResponse($idp, $sp);

        $result = (new AssertionValidator($response, $idp, $sp, false))->validate($response->getAssertions()[0]);

        $this->assertSame([], $result->getErrors());
    }

    private function errorsAfter(callable $mutate): array
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $assertion = $response->getAssertions()[0];
        $mutate($assertion, $response);

        return (new AssertionValidator($response, $idp, $sp))->validate($assertion)->getErrors();
    }
}
