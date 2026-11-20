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
 *
 * Accepted audiences are this SP's Entity ID, `spn:<Entity ID>` (Microsoft Entra ID sends
 * the audience this way when the app's Identifier is its bare application ID), and any
 * extra audiences from the `allowedAudiences` setting. Matching is exact.
 */
class AudienceRestriction implements AssertionConstraintValidator
{
    /** Prefix Entra ID adds to the audience when the Identifier is the application ID GUID */
    public const ENTRA_SPN_PREFIX = 'spn:';

    /**
     * @param string $entityId this SP's Entity ID
     * @param mixed[] $allowedAudiences extra accepted audiences (the `allowedAudiences` setting); non-strings and blanks are ignored
     */
    public function __construct(
        private string $entityId,
        private array $allowedAudiences = [],
    ) {
    }

    public function validate(Assertion $assertion, Result $result): void
    {
        $audiences = $assertion->getValidAudiences();
        if (empty($audiences)) {
            $result->addError('The assertion has no AudienceRestriction.');
            return;
        }

        if (array_intersect($this->acceptedAudiences(), $audiences) === []) {
            $result->addError(sprintf(
                'This Service Provider [%s] is not a valid audience for the assertion. Audiences: [%s]',
                $this->entityId,
                implode('], [', $audiences)
            ));
        }
    }

    /**
     * @return string[]
     */
    public function acceptedAudiences(): array
    {
        $accepted = [];
        if ($this->entityId !== '') {
            $accepted[] = $this->entityId;
            $accepted[] = self::ENTRA_SPN_PREFIX . $this->entityId;
        }

        foreach ($this->allowedAudiences as $audience) {
            if (is_string($audience) && trim($audience) !== '') {
                $accepted[] = trim($audience);
            }
        }

        return array_values(array_unique($accepted));
    }
}
