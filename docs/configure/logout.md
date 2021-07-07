# Configure Logout (SLO)

::: tip Optional
This feature is optional and your IDP must support it and be configured. SLO support for the Craft backend/admin/control
panel, is currently not supported.
:::

If you want to implement Single Logout, point your site's logout button to `/sso/logout/request`.

This endpoint initiates the Single Logout process with the IDP.