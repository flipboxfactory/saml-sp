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