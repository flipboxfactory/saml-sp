Changelog
=========
# 1.0.0-RC4
### Changed
- Changed the Before and after events to use a custom event `flipbox\saml\sp\events\UserLogin`. This event has a resposne and a user property on it.

# 1.0.0-RC3
### Fixed
- Fixed issue added in RC update where the attribute map forces the craft property to be a string. Callables are allowed as well.

# 1.0.0-RC2
### Added
- Throwing exception when the Response has no NameID in it.
- Adding some buffer to the conditional time validation

# 1.0.0-RC1
### Added
- Improved Control Panel UI
- Login via Control Panel with IDPs listed
- Labels for Providers
- Auto generate OpenSSL key pairs with Keychain
- Mapping attributes based on provider

# 1.0.0-beta.15
### Fixed
- Issue with provider identity being saved with username instead of NameId

# 1.0.0-beta.14
### Added
- Adding `relayStateOverrideParam` to the settings model.

# 1.0.0-beta.13
### Fixed
- Login: if there aren't any attribute statements, try and use the NameID as the email and continue.

# 1.0.0-beta.12
### Fixed
- Saml core fix: Changing default rsa1 to rsa256

# 1.0.0-beta.11
### Fixed
- Fixed a bug where during the verification of a signature, we were pulling the first key from the metadata
which could be the wrong one. Now specify the signing key.

# 1.0.0-beta.10

### Added
- Plugin logging for attribute mapping while values are being added to the user.
- Remember relay state when the user clicks a login link on a page without requireLogin set Ref: https://github.com/flipboxfactory/saml-sp/issues/11

### Fixed
- Issue with relay state being base64 encoded twice

# 1.0.0-beta.9

### Added
- Support for Azure AD as the IDP
- Support for Google Apps as the IDP

### Fixed
- Map array callables are now called correctly. Ref: https://github.com/flipboxfactory/saml-sp/issues/5

# 1.0.0-beta.8
Initial release.
