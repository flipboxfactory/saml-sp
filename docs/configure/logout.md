# Configure Logout (SLO) - Optional
If you want to implement Single Logout, point your logout button to `/sso/logout/request`.

This endpoint initiates the single logout process with the IDP. This must be configured with the IDP (and the IDP must support this feature) if you plan to use it.             