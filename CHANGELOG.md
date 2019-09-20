# Release Notes for Craft CMS Plugin SAML SP

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

