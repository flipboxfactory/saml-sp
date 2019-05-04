# Configure Login (SSO)
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

## Explicit Config
If you want to set multiple IDPs by environment, you should be explicit which provider you want to use. 

Under the "Configure" tab there is a "Login/Logout Paths" header which contains read-only properties. Copy the "Login Path"
and use it as the Craft CMS `loginPath`.


```php
<?php

//multi-environment example
return [
    'production' => [
       'loginPath' => '/sso/login/request/<production provider uid>',
    ],
    'dev' => [
       'loginPath' => '/sso/login/request/<local provider uid>',
    ],
];
```
