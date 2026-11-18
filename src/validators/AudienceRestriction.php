<?php

namespace flipbox\saml\sp\validators;

use SAML2\Assertion;
use SAML2\Assertion\Validation\AssertionConstraintValidator;
use SAML2\Assertion\Validation\Result;

/**
 * The assertion must be audience-restricted to this SP (saml-profiles 4.1.4.2).
 *
 * Unlike saml2's SpIsValidAudience, a missing AudienceRestriction is an error: an
 * assertion without one could have been issued for any SP that trusts the same IdP.
 */
class AudienceRestriction implements AssertionConstraintValidator
{
    public function __construct(
        private string $entityId,
    ) {
    }

    public function validate(Assertion $assertion, Result $result): void
    {
        $audiences = $assertion->getValidAudiences();
        if (empty($audiences)) {
            $result->addError('The assertion has no AudienceRestriction.');
            return;
        }

        if (!in_array($this->entityId, $audiences, true)) {
            $result->addError(sprintf(
                'This Service Provider [%s] is not a valid audience for the assertion. Audiences: [%s]',
                $this->entityId,
                implode('], [', $audiences)
            ));
        }
    }
}
