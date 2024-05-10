# Configure Logout (SLO)

::: tip Optional
This feature is optional and your IDP must support it and be configured. SLO support for the Craft backend/admin/control
panel, is currently not supported.
:::

If you want to implement Single Logout, point your site's logout button to `/sso/logout/request`.

This endpoint initiates the Single Logout process with the IDP.

## Twig Logout URL Extension
With the saml-sp plugin installed, there's a twig helper function for generating the url for logout.

> Note: An InvalidArgumentException will be throw if the IdP Entity Id is passed in doesn't match 
> any records in the database.

### Usage
Use the following method.

`samlSpLogoutUrl(<IdP entityId>)`

Parameter:
- IdP Entity IdP

### Example

```html
<a href="{{ samlSpLogoutUrl("https://idp") }}">Logout</a>
```
