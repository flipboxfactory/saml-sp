# Adding Users to User Groups using SSO/SAML

Attributes within the SAML Response Assertions can be mapped to automatically add the users logging-in to a 
matching Craft User Group. 

::: tip
The Craft User Groups must already exist in Craft since these are managed with the project config. Automatic 
group creation is not possible. 

Furthermore, Assigning admin permission without Craft User Group assignment is not supported through the plugin.
:::

Since the User Group must already exist, assign the permissions accordingly.

## User Group SAML SP Configs
### Map the User Group
By default, the attribute the plugin looks for is `groups`. When an attribute name is found in an assertion 
matching `groups`, a lookup to done to match the value of that attribute with the *handle* of the existing group. 

If the value of this attribute doesn't match the handle exactly, the lookup with fail and the user will not be assigned.

Here is how to overwrite this setting in the `config/saml-sp.php`.
```php
    return [
        'groupAttributeNames' => [
            'MyUserGroupAttributeName',
        ]
    ];
```

### Merging the User's Existing Groups

By default, the existing groups the user is assigned to will be merged into the ones that  are found in the Response 
Assertions. You can modify this behavior by setting `mergeExistingGroups` in the `config/saml-sp.php` to false.

> WARNING: If you set this config to false, the user groups will be FULLY MANAGED by SSO/SAML. This includes removing
> groups when the attribute is not found or when the attribute is empty.

Example `config/saml-sp.php`:
```php
    return [
        'mergeExistingGroups'=>false,
    ];
```

### Adding a Default Group
Using the `defaultGroupAssignments` property, you can add groups to everyone logging-in through SSO automatically. This
property is a list of User Group ids.

Example `config/saml-sp.php`:
```php
    return [
        'defaultGroupAssignments'=>[1,2,4],
    ];
```
## Customizing Groups Assignments

If the existing group configs don't fit your business case, evaluate the 
[events examples](/configure/events.html#assign-user-to-a-user-group-based-on-a-property)