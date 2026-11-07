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

        // Only the check under test may fail
        $this->assertSame(['Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.'], $errors);
    }

    public function testExpiredSessionIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->setSessionNotOnOrAfter((new \DateTime('-1 hour'))->getTimestamp());
        });

        // Only the check under test may fail
        $this->assertSame(['Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.'], $errors);
    }

    public function testNonBearerSubjectConfirmationIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->getSubjectConfirmation()[0]->setMethod(Constants::CM_HOK);
        });

        // Only the check under test may fail
        $this->assertSame([sprintf('Invalid Method on SubjectConfirmation, current;y only Bearer (%s) is supported', Constants::CM_BEARER)], $errors);
    }

    public function testExpiredSubjectConfirmationIsRejected(): void
    {
        $errors = $this->errorsAfter(function($assertion) {
            $assertion->getSubjectConfirmation()[0]->getSubjectConfirmationData()
                ->setNotOnOrAfter((new \DateTime('-1 hour'))->getTimestamp());
        });

        // Only the check under test may fail
        $this->assertSame(['NotOnOrAfter in SubjectConfirmationData is in the past'], $errors);
    }

    public function testSubjectConfirmationInResponseToMustMatchResponse(): void
    {
        $errors = $this->errorsAfter(function($assertion, $response) {
            $response->setInResponseTo('_expected-request-id');
            $assertion->getSubjectConfirmation()[0]->getSubjectConfirmationData()
                ->setInResponseTo('_some-other-request-id');
        });

        // Only the check under test may fail
        $this->assertSame(['InResponseTo in SubjectConfirmationData ("_some-other-request-id") does not match the Response InResponseTo ("_expected-request-id")'], $errors);
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
