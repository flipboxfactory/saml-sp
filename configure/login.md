---
title: Configure Login
permalink: configure/login/
---

# Configure Login

## Initiate Login with the IDP
Now that the plugin is configured, point login within the Craft general config (`config/general.php`) to 
`/sso/login/request`.

```php
...
#example general config

  'loginPath' => '/sso/login/request',
...
```

## Plugin Settings | `config/saml-sp.php`

These configuration options can be found in the Settings model within the plugin
at `flipbox\saml\sp\models\Settings` and you can override these options by adding 
a config file within your repo at `config/saml-sp.php`.

### `enableUsers`
* Description:
 
    * When a user logs in successfully _BUT IS NOT ACTIVE/ENABLED_ in Craft, that user will be 
    activated/enabled. This is set to true because, the IDP is the authority on whether the user
    is active or not. They should be disabled in the IDP if they aren't supposed to have access.
    If this is false an user exception will be thrown.
     
* Default: `true`

### `createUser`
* Description:
 
    * When a user logs in successfully _BUT IS NOT FOUND_ in Craft, that user will be 
    created. If this is false an user exception will be thrown.
     
* Default: `true`

### `mergeLocalUsers`
* Description:
 
    * When a user logs in successfully and _IS FOUND_ in Craft, that user will be 
    created. If this is false an user exception will be thrown.
     
* Default: `true`

### `syncGroups`

* Description:
 
    * Gives the green light to attempt to sync groups via the 
    groups array map ([`groupAttributeNames`](#groupattributenames)).
     
* Default: `true`

### `groupAttributeNames`

* Description:
 
    * A list of strings that will be used to look through the attributes xml nodes
    sent by the IDP to be identified as a group. This is a key to the group name value(s).
     
* Default:
```php
[
'groups'
]
```

### `autoCreateGroups`
* Description:
 
    * When a group is found that doesn't exist in Craft, it will be automatically created.
     
* Default: `true`

### `responseAttributeMap`
* Description: 
    * An array map with the Response attribute names as the array keys and the
    array values as the user element field. The array value can also be a callable. 
* Required: A map item needs to exist for email.
* Default:
```php
[
        # "IDP Attribute Name" => "Craft Property Name"
        ClaimTypes::EMAIL_ADDRESS => 'email',
        ClaimTypes::GIVEN_NAME    => 'firstName',
        ClaimTypes::SURNAME       => 'lastName',

        'email'     => 'email',
        'firstName' => 'firstName',
        'lastName'  => 'lastName',
]
```

## Example `config/saml-sp.php`

```php
<?php
return [
    'responseAttributeMap' => [
        # "IDP Attribute Name" => "Craft Property Name"
        ClaimTypes::EMAIL_ADDRESS => 'email',
        ClaimTypes::GIVEN_NAME    => 'firstName',
        ClaimTypes::SURNAME       => 'lastName',

        'email'     => 'email',
        'firstName' => 'firstName',
        'lastName'  => 'lastName',
    ]
];
```
