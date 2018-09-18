# Configure Login (SSO)
Set the loginPath within the Craft general config (config/general.php).
 
```php
return [
    ...
   'loginPath' => '/sso/login/request',
]
```

### Plugin Settings - config/saml-sp.php
You may override the default plugin settings by creating a `/config/saml-sp.php` file.

View the settings you can override at `/plugins/saml-sp/src/models/Settings.php`. Each setting has a description of what it does and how to customize it.

```php
<?php

return [
    'responseAttributeMap' => [
        # "IDP Attribute Name" => "Craft Property Name"
        ClaimTypes::EMAIL_ADDRESS => 'email',
        ClaimTypes::GIVEN_NAME => 'firstName',
        ClaimTypes::SURNAME => 'lastName',

        'email' => 'email',
        'firstName' => 'firstName',
        'lastName' => 'lastName',
    ]
];
```