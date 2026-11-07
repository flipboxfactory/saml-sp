# saml-sp

Commercial Craft CMS plugin: SAML 2.0 Service Provider (SSO and SLO into Craft). Built on `flipboxfactory/saml-core` (shared SAML code, `simplesamlphp/saml2` 4.19.x) and `flipboxfactory/keychain` (key pairs). Changes to those packages only arrive here through a release and a constraint bump in `composer.json`.

## Security first

Customers buy this plugin to protect who can sign in to their Craft sites. A bug here can let an attacker log in as any user, including admins. Security and code quality take priority over speed, convenience and scope.

- **Fail closed.** Reject a response on any parse, signature, decryption or validation error. Never log a user in from unsigned, unvalidated or partially validated data.
- **Never weaken or skip a validation to make something work**: signatures (response and/or assertion), Destination, Audience, Issuer, `InResponseTo`, NotBefore/NotOnOrAfter, SubjectConfirmation. The same goes for algorithm restrictions and tests. If a check gets in the way, stop and raise it.
- **Treat all SAML input as hostile**: SAMLResponse, RelayState, metadata and attribute values. Only redirect to validated, same-site URLs (no open redirects), and never trust attribute values for privilege decisions without the mapping the admin configured.
- **User provisioning and group sync** must never grant admin or extra permissions beyond the provider's configured mapping. Changes to user matching, creation or group assignment need tests for the negative cases.
- **Never log or expose secrets**: private keys, decrypted assertions, session or security keys. That includes exceptions, debug logs and CP templates.
- **Dependencies**: run `composer audit` and check GitHub advisories before adding or bumping one, and justify any new dependency.
- **Security fixes** ship as a patch release with a regression test (one that fails without the fix) and a CHANGELOG entry naming the advisory (GHSA/CVE). Handle unreleased vulnerabilities privately (GitHub security advisory), not in public issues, PRs or commit messages.
- When unsure how the SAML spec, saml2 or Craft behaves, verify it against the source or the spec rather than guessing, and say what you couldn't verify.

## Before every commit

```bash
composer check           # ECS + PHPStan + Codeception; must pass
composer fix-cs          # auto-fix style, then re-run check
composer test-coverage   # line coverage must stay >= 85% (CI enforces; needs pcov or Xdebug)
```

Tests need MySQL and `tests/.env` (see README "Development").

## Rules

- Add or update tests in `tests/unit/` for every behaviour change or bug fix. Bug fixes get a test that fails without the fix. Assert outcomes (rejected, logged in, redirected), not just that code ran.
- Don't lower the PHPStan level, the coverage minimum, or add a baseline. Use an inline `@phpstan-ignore <identifier>` with a reason only for genuine false positives.
- Schema changes need a migration in `src/migrations/` and a `schemaVersion` bump in `composer.json` `extra`.
- Keep style-only changes (`fix-cs`) in their own commit, separate from logic changes.
- Don't commit generated or local files: `tests/.env`, `tests/_output`, `tests/_support/_generated`, `vendor/`, `composer.lock`.

## Workflow

- Default branch is `master`. Work on a branch and open a PR; CI (`.github/workflows/ci.yml`) must be green.
- SemVer. A PR that changes `src/` or `composer.json` must bump `composer.json` `version` and `extra.version` (kept equal) and add a `CHANGELOG.md` entry (newest first, `## X.Y.Z - YYYY-MM-DD` with `### Fixed|Added|Changed|Removed`). CI enforces this. `/bump-version` does the bump.
- Merging to `master` creates the unprefixed tag (`5.1.4`) and GitHub release from that CHANGELOG entry once checks and tests pass (`.github/scripts/version.sh`). Never tag manually.
- User docs live in `docs/` (VitePress: `npm run docs:dev`; published to https://saml-sp.flipboxfactory.com from `master`). Update them with any user-facing change. Keep `README.md` short.
