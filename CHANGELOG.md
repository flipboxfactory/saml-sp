Changelog
=========
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
