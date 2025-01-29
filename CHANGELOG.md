# Release Notes for SAML SP

## 5.1.3 - 2025-01-29

### Fixed
- admin login screen buttons kicking off sso

## 5.1.2 - 2024-12-03 [CRITICAL]

### Fixed
- SECURITY PATCH with saml-core/saml2 dependencies. Update REQUIRED! More info can be found here: https://github.com/simplesamlphp/saml2/security/advisories/GHSA-pxm4-r5ph-q2m2#event-375127

## 5.1.1
### Feature
- Allow to modify UserQuery in getByUsernameOrEmail (#216)

## 5.0.0 - 2024-04-17

### Fixed
- Craft 5.0 compatibility

## 4.1.1 - 2024-02-10

### Feature
- Add `samlSpLogoutUrl` for easier logout url generation

## 4.1.0 - 2024-02-10

### Fixed
- Fixing issue with multi-site linking for the external id field

## 4.0.7 - 2023-11-29

### Fixed
- bumping saml-core to use pinned psr/log at 1.1.4

## 4.0.6.1 - 2023-11-28

### Fixed
- revert: issue with logger interface compatibility #197

## 4.0.6 - 2023-11-28

### Fixed
- issue with logger interface compatibility #197

## 4.0.5 - 2022-12-15

### Fixed
- issue with ui https://github.com/flipboxfactory/saml-sp/issues/182

## 4.0.4 - 2022-11-09

### Fixed
- issue with craft version being off compared to the composer version 🤪. fixes #179

## 4.0.3 - 2022-09-29

### Fixed
- issue with custom attributes not being picked up by the validation #177

## 4.0.2 - 2022-09-01

### Fixed
- excluding disabled IdPs in login controller findByEntityId() closing #175

## 4.0.1 2022-08-11

### Fixed
- issues with 4.0 typing matching craft parent classes (saml-core)

## 4.0.0 2022-06-18

### Fixed
- Craft 4.0 support

## 2.7.4 - 2022-05-31

### Fixed
- issue with using the default settings for entity id instead of the provider entity id, closing #171

## 2.7.5 - 2022-09-01

### Fixed
- excluding disabled IdPs in login controller findByEntityId() closing #175

## 2.7.3 - 2021-10-27

### Fixed
- updated saml-core: When "This site has it's own base URL" isn't checked but the site is selected. ref: https://github.com/flipboxfactory/saml-sp/issues/139

## 2.7.2 - 2021-10-04

### Fixed
- fixed missing config being passed to the validator for controls on assertions being signed (thanks @lindseydiloreto for catching this and PR). Ref: https://github.com/flipboxfactory/saml-sp/issues/126

## 2.7.1 - 2021-10-01
> {warning} Encrypted Assertions are now set to be decrypted before events may interact with them. If you currently decrypt assertions in an custom event, verify the assertion is an instance of `\SAML2\EncryptedAssertion` before decryption.

### Added
- Event `\flipbox\saml\sp\events\UserGroupAssign` and `\flipbox\saml\sp\services\login\UserGroups::EVENT_BEFORE_USER_GROUP_ASSIGN` manipulate groups to be assigned before assignment #133
- Config (which can be added in `config/saml-sp.php`) `mergeExistingGroups` to opt-in to merging groups if desired. Default is true, the groups will be merged. #133

### Changed
- Add decrypted assertions to Response after assertions are initially decrypted. See above warning.

## 2.7.0 - 2021-09-13 [CRITICAL]
> {warning} Setting have been added to improve security (requireResponseToBeSigned and requireAssertionToBeSigned). It's recommend to update ASAP and leave these enabled. Test login before deploying.

### Fixed
- Adding controls to require Response and assertions to be signed. Ref: https://github.com/flipboxfactory/saml-sp/issues/126

### Added
- `\flipbox\saml\sp\validators\Response`
- `\flipbox\saml\sp\validators\Assertion`
- `\flipbox\saml\sp\validators\SignedElement`
- `\flipbox\saml\sp\models\Settings::$requireResponseToBeSigned`
- `\flipbox\saml\sp\models\Settings::$requireAssertionToBeSigned`

## 2.6.10 - 2021-09-02
### Fixed
- Fixing validation errors that don't throw exceptions: https://github.com/flipboxfactory/saml-sp/issues/126

## 2.6.9 - 2021-05-14
### Fixed
- Issue with clipboard (using navigator.clipboard with a fallback of the previous method) #113
- Disallow viewing to settings when allowAdminChanges is false #114

## 2.6.8 - 2021-04-13

### Fixed
- *Possible* issue with SP initiated SSO. Result from 2.6.7 controller changes.

## 2.6.7 - 2021-04-13

### Added
- Ability to be explicit with internal provider when passing a request url.
- docs updates

## 2.6.5 - 2021-03-11

### Fixed
- adding support for when there is not NameID sent and admin is using nameIDOverride.

## 2.6.4 - 2021-02-12

### Fixed
- Fixing migration issue with duplicate metadataOptions error.

## 2.6.3 - 2021-02-11

### Fixed
- Forcing core update for those updating to Craft CMS 3.6 (from a lower version).

## 2.6.2 - 2021-02-10

### Fixed
- Fixing latest login page.`dashboard` isn't a variable, it's the destination (string).

## 2.6.1 - 2021-01-28

### Fixed
- Updated login for Craft version 3.5.18 and greater.

## 2.6.0 - 2021-01-08

> {warning} Breaking changes: There are significant endpoint and metadata changes with this release. Please make sure you have a testing site and test this upgrade with your code when you apply this change.

### Added
- Better multisite support.
- EntityID is is now editible

## Changed
- Breaking change: Url formating from settings (if you are using the settings model for URLs, check this!)
- Breaking change: `flipbox\saml\core\services\Metadata::create` (moved to providers records)

## 2.5.3 - 2020-12-16
### Added
- Github Actions CICD! 🚀

## 2.5.2 - 2020-10-29
### Fixed
- Issue where SP and IdP plugin couldn't be installed on the same craft db due to table conflicts.

## 2.5.1 - 2020-10-01

### Fixed
- Issue with EntityID override (added in 2.5.0), fixing https://github.com/flipboxfactory/saml-sp/issues/84

## 2.5.0 - 2020-09-22
> {warning} Breaking changes

### Changed
- Breaking change: Changed `\flipbox\saml\sp\services\login\User::getByResponse` parameters.

### Added
- Added ability to set NameId Override per IdP provider in the backend.
- Added event for before user save, `\flipbox\saml\sp\services\login\User::EVENT_BEFORE_USER_SAVE`.

## 2.4.1 - 2020-08-31

### Fixed
- Missing event `EVENT_AFTER_RESPONSE_TO_USER`. Event was added back in.

## 2.4.0 - 2020-08-25

> {warning} Breaking changes: Changed `\flipbox\saml\sp\services\messages\AuthnRequest::EVENT_AFTER_MESSAGE_CREATED` event to use
new class `\flipbox\saml\sp\events\AuthnRequest` instead of `\yii\base\Event`

### Changed
- Changed the event object used from `\flipbox\saml\sp\services\messages\AuthnRequest::EVENT_AFTER_MESSAGE_CREATED` event to use
new class `\flipbox\saml\sp\events\AuthnRequest` instead of `\yii\base\Event`. AuthnRequest message is now in the `$message` property instead of `$data`.

## 2.3.1 - 2020-08-06

### Fixed
- Issue with constraint on the Provider Identity table when the user's NameID changes.

## 2.3.0 - 2020-08-05

> {warning} ** `autoCreateGroups` functionality has been removed. Automatic creation of user groups, has been removed. ** This is due to the project
>config changes in Craft CMS 3.5. Users are still assigned to a user group when the group
>match one existing within Craft. If a user group is not in Craft, the group is logged (as a warning)
>and no error is thrown.

> {warning} `responseAttributeMap` functionality has been removed. Please use the admin panel interface.

### Added
- Added `nameIdAttributeOverride` setting. This is a system level setting override allowing you to map a username
to a different assertion attribute, besides the NameID.

### Fixed
- Issue with the `createUser` setting which allowed the user to be created but not login.
The user will no longer be created.

### Removed / Deprecated
- The following settings have been deprecated while the functionality of the those
settings have been removed:
    - `mergeLocalUsers`
    - `autoCreateGroups`
    - `responseAttributeMap`

## 2.2.0 - 2020-07-14

### Added
- More unit testing!

### Changed
- Updated saml-core which upgraded the `simplesamlphp/saml2` library.
- `\flipbox\saml\sp\services\login\UserGroups::assignDefaultGroups` to a protected method
- `\flipbox\saml\sp\services\login\UserGroups::syncByAssertion` to a protected method
- `\flipbox\saml\sp\services\login\UserGroups::getDefaultGroups` to a protected method

### Removed
- `\flipbox\saml\sp\services\Login::login`

## 2.1.12 - 2020-07-10
### Fixed
- Issue with diabled provider (My Provider) being picked as own provider when there's an enabled and disable provider
with the same EntityId #68

## 2.1.11 - 2020-07-10
### Fixed
- Issue with `autoCreateGroups` plugin setting not doing what it's supposed to do. #65

## 2.1.10 - 2020-07-09
### Fixed
- Issue with saving groups with non-ascii conforming groups.

## 2.1.9 - 2020-05-18
### Added
- Adding Yii events to allow devs to modify RelayState

## 2.1.8 - 2020-05-15
### Added
- Adding setting to turn off base64 encoding of the RelayState: `encodeRelayState`.

## 2.1.7 - 2020-05-06
### Fixed
- Missed a spot with https://github.com/flipboxfactory/saml-sp/issues/57

## 2.1.6 - 2020-05-05
### Fixed
- Issue with missing Assertion Consumer Service URL: Fixing https://github.com/flipboxfactory/saml-sp/issues/58
- Issue CP panel presenting the SLO endpoint, fixing: https://github.com/flipboxfactory/saml-sp/issues/57

## 2.1.5 - 2020-03-12
### Fixed
- Fixed issue with Metadata URL not overwriting the metadata correctly via the control panel and cli.

### Added
- CLI command for listing all providers. See `./craft saml-sp/metadata`.

## 2.1.4 - 2020-03-05
### Fixed
- Fixed issue introduced in 2.1.3 Fixes: https://github.com/flipboxfactory/saml-sp/issues/53
- Fixed issue with attributes statements with one attribute (they'd be skipped over). Fixes: https://github.com/flipboxfactory/saml-sp/issues/54

## 2.1.3 - 2020-03-04
### Fixed
- Fixes issue with `GeneralConfig::headlessMode` by explicitly setting response to HTML. Fixes: https://github.com/flipboxfactory/saml-sp/issues/53
- Fixed issue with setting custom fields in Craft 3.4. Now using `setFieldValue`. Fixes: https://github.com/flipboxfactory/saml-sp/issues/53

## 2.1.2 - 2020-02-06
### Fixed
- Fixing issue with migration from 1.x to 2.x. Fixes: https://github.com/flipboxfactory/saml-sp/issues/51

## 2.1.1.2 - 2020-01-08
### Fixed
- Fixing issue with Craft 3.2 twig error within the editableTable

## 2.1.1.1 - 2020-01-08
### Fixed
- Fixing table name for craft installs with prefixes.

## 2.1.1 - 2020-01-08
### Fixed
- Fixing issue with postgres uid - https://github.com/flipboxfactory/saml-sp/issues/49

## 2.1.0 - 2020-01-07
### Fixed
- Fixing issue with requiring admin when project config when `allowAdminChanges` general config is set.
- Duplicate `metadata` html attribute id on the edit page
- Fixed issue with large Metadata too big for the db metadata column (requires migration) https://github.com/flipboxfactory/saml-sp/issues/48

### Added
- Support for Saving Metadata via url (requires migration) https://github.com/flipboxfactory/saml-sp/issues/47
- Support for 3.4 login page

## 2.0.15 - 2020-01-03
### Fixed
- RelayState when going directly to `/admin/login`. If the siteUrl matches the returnUrl, the user will now be redirected to the dashboard (`cpUrl('dashboard')`).

## 2.0.14 - 2019-11-26
### Added
- Fixed admin login `Via <IdP>` button relay state, redirecting properly now.

## 2.0.13 - 2019-11-21
### Added
- Added support for HTTP-Redirect https://github.com/flipboxfactory/saml-sp/issues/41

## 2.0.12 - 2019-11-21
### Fixed
- Fixed issue with too many redirects when the site is set to offline. https://github.com/flipboxfactory/saml-sp/issues/42

## 2.0.11 - 2019-11-18
### Added
- Added support for parsing multiple assertions. Possibly related to https://github.com/flipboxfactory/saml-sp/issues/40

## 2.0.9 - 2019-10-07
### Removed
- Removed flipboxfactory/craft-ember package for easier updates with dependancies.

## 2.0.7 - 2019-09-26
### Fixed
- Fixed issue with decrypting assertions

## 2.0.6 - 2019-09-25
### Fixed
- Fixing more xsd schema compatibility. Changed message ids to be compatible.
- Fixed exception when the user tries to logout (SLO) when they are already logged out.

## 2.0.5 - 2019-09-25
> {warning} **THE 2.0 UPGRADE HAS BREAKING CHANGES.** All existing events have changed. Please reference: https://saml-sp.flipboxfactory.com/installation.html#upgrading-to-2-0
### Fixed
- Added protocolSupportEnumeration in the metadata. That is required by SAML and stricter IdPs will complain.

## 2.0.4 - 2019-09-20
> {warning} **THE 2.0 UPGRADE HAS BREAKING CHANGES.** All existing events have changed. Please reference: https://saml-sp.flipboxfactory.com/installation.html#upgrading-to-2-0
### Fixed
- Fixed AssertionConsumerServiceIndex type. Made it an int like it's intended to be.

### Added
- More friendly exceptions when there are configuration issues with IdP or SP, therefore not being found.

## 2.0.3 - 2019-09-20
> {warning} **THE 2.0 UPGRADE HAS BREAKING CHANGES.** All existing events have changed. Please reference: https://saml-sp.flipboxfactory.com/installation.html#upgrading-to-2-0

### Fixed
- Issue with the `Via` buttons on the login page pointing to the incorrect endpoint. https://github.com/flipboxfactory/saml-sp/issues/31

## 2.0.2 - 2019-09-18
> {warning} **THE 2.0 UPGRADE HAS BREAKING CHANGES.** All existing events have changed. Please reference: https://saml-sp.flipboxfactory.com/installation.html#upgrading-to-2-0

## 2.0.1 - 2019-09-17
> {warning} **THE 2.0 UPGRADE HAS BREAKING CHANGES.** All existing events have changed. Please reference: https://saml-sp.flipboxfactory.com/installation.html#upgrading-to-2-0

## 2.0.0 - 2019-09-17
> {warning} **THE 2.0 UPGRADE HAS BREAKING CHANGES.** All existing events have changed. If you have hooked or have a custom `attributeMap` (within `config/saml-sp.php`), please test the upgrade and sso login completely. Changes will most likely be needed.

> {warning} Any references to the [LightSaml](https://github.com/lightSAML/lightSAML) php package need to be changed. LightSAML has been swapped out for the simplesamlphp core package [simplesamlphp](https://github.com/simplesamlphp/saml2)

### Removed
- Remove static method and associated (deprecated) constants: `\flipbox\saml\sp\services\messages\Metadata::getLoginLocation`. Get this from the settings model now.
- Remove static method and associated (deprecated) constants: `\flipbox\saml\sp\services\messages\Metadata::getLogoutRequestLocation`. Get this from the settings model now.
- Remove static method and associated (deprecated) constants: `\flipbox\saml\sp\services\messages\Metadata::getLogoutResponseLocation`. Get this from the settings model now.
- Removed the LightSaml package

### Changed
- Switched from the php LightSaml package to the simplesamlphp core lib

### Fixed
- Typo in attribute map in the provider table (requires migration)

### Added
- Support for environmental variables in the plugin settings. Works better with the project config.

## 1.0.6 - 2018-10-24
### Fixed
- Fixed issues with `\flipbox\saml\sp\services\login\UserGroups::syncByAssertion` deleting existing user groups

## 1.0.4 - 2018-10-22
### Added
- Added config `defaultGroupAssignments` to give the ability to add users by default to certain groups.

## 1.0.3.1 - 2018-10-05
### Fixed
- issue with ACS within the auth and request presented in 1.0.3

## 1.0.3 - 2018-10-05
### Changed
- Broke/cleaned up the Login service

## 1.0.0 - 2018-09-26
### Added
- New Docs! and Tests!
