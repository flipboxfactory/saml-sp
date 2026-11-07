# Configure Login (SSO)
::: tip Use Case
Configuring login, as documented below, is for frontend use only. If your users are only meant to login via the 
Craft backend/admin/control panel, these configurations aren't needed. 
:::

Set the `loginPath` within the Craft general config `config/general.php`.

After the IDP is saved in Craft, navigate back to the provider. Under the "Configure" tab in Craft, navigate back to the provider. 
The following code snippets show login configuration options.
 
## Simple Config
If there is only one IDP in the system, you can use the following simple config which is also the default.
```php
<?php

return [
    ...
   'loginPath' => '/sso/login/request',
];
```

## Explicit Config (recommended)
If you want to set multiple IDPs by environment, you should be explicit which provider you want to use. 

Under the "Configure" tab there is a "Login/Logout Paths" header which contains read-only properties. Copy the "Login Path"
and use it as the Craft CMS `loginPath`.


```php
<?php

//multi-environment example
return [
    'production' => [
       'loginPath' => '/sso/login/request/<production IdP provider uid>',
    ],
    'dev' => [
       'loginPath' => '/sso/login/request/<dev IdP provider uid>',
    ],
];
```

### 🆕 Specifying Service Provider/My Provider
You can now go further and specify the uid to the Service Provider want to ensure SAML uses. To do so, append the Service
Provider/My Provider.

```php
<?php

//multi-environment example
return [
    'production' => [
       'loginPath' => '/sso/login/request/<production IdP provider uid>/<production SP provider uid>',
    ],
    'dev' => [
       'loginPath' => '/sso/login/request/<dev IdP provider uid>/<dev SP provider uid>',
    ],
];
```
## Require SSO (disable password sign-in)

Set `requireSsoLogin` to only allow sign-in through SAML:

```php
// config/saml-sp.php
use craft\helpers\App;

return [
    'requireSsoLogin' => App::env('SAML_REQUIRE_SSO') ?? false,
];
```

When enabled:

- Craft rejects password and passkey sign-in for **every** user, admins included. That covers the control panel login page, front-end `users/login` forms, the "Your session has ended" modal, and the "Confirm your identity" (elevated session) prompt. Users see "Sign in with single sign-on (SSO)."
- The control panel login page and those modals show only your IdP buttons. The native form stays visible if `enableCpLoginButtons` is off or no IdP providers exist. Sign-in still fails, but the page isn't blank.
- Signing in through SAML is unaffected, and it starts a fresh elevated session.

Things to know:

- **Keep a way back in.** If your IdP is down, nobody can sign in. Drive the setting from an environment variable so you can turn it off without a deploy, or run `php craft users/impersonate <admin>` to get a one-time sign-in link.
- Password reset emails still send and users can still set a password, but the password can't be used to sign in. If you have Craft's `autoLoginAfterAccountActivation` turned on, turn it off (the default is off). Otherwise a pending user who sets a password from an activation email is signed in directly.
- The session-expired and identity modals send the whole page to your IdP, so unsaved changes on that page are lost. Raising `elevatedSessionDuration`, or setting it to `0` to turn off re-verification, reduces how often users see the identity prompt.
