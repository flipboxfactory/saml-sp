---
title: Configure Login
permalink: configure/login/
---

# Configure Login

## Intiate Login with the IDP
Now that the plugin is configured, point login within the General Config in CraftCMS to 
`/sso/login/request`.

```php
...
#example general config

  'loginPath' => '/sso/login/request',
...
```

## User Sync Configurations

These configuration options can be found in the Settings model within the plugin
at `flipbox\saml\sp\models\Settings`.

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

### `enableUser`

* Description:
 
    * When a user logs in successfully and is found in Craft and is not enabled, that user will be 
    enabled. If this is false and an user exception will be thrown.
     
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

