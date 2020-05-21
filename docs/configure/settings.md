## Plugin Settings
You may override the default plugin settings by creating a `/config/saml-sp.php` file.

View the settings you can override in your project at `/vendor/flipboxfactory/saml-sp/src/models/Settings.php`. Each setting has a description of what it does and how to customize it.

### EntityID
Currently you can edit the EntityID **system wide** a couple ways. Note that the value can be an environmental variable so that might be something to consider.

#### 1. Add a config file in `config/saml-sp.php`.
This location lives right next to the `general.php`. Below is an example file contents.

```php
return [
   'entityId' => 'https://my-entity-id',
];
```

#### 2. Edit from the admin (Goto the plugin in craft, then click on the "Settings" menu item under the plugins sub nav).
Set the Entity ID there which will save it to the db

### Group Configuration/Group Assignment
#### Group Attribute Name/Group Attribute Mapping
Many IdPs will send groups as an attribute within the SAML Response. When configured, the plugin use that attribute to 
automatically create (if needed) and assign the user properly. To achieve this,  add a `config/saml-sp.php` then use the
 following configuration.
##### Example
```php
return [
    // change the value as needed
    'groupAttributeNames' => 'groups'
];
```

Make sure the value matches the attribute name sent from the IdP. 

#### Auto-Create Group
By default the plugin will create the group if the group attribute name is mapped correctly. You can 
turn this off if desired using `autoCreateGroups`.

##### Example
```php
return [
    'groupAttributeNames' => 'groups',
    // this will turn off the automatic creation of groups
    'autoCreateGroups' => false,
];
```

#### Managing Permissions
Managing permissions ** is not supported ** automatically. There are a few options on how to manually manage 
permissions:
1. Create the groups before the SAML Plugin creates them, then configure the permissions on the group.
Know that the plugin will take the name of the group and camel case (refer to: `\craft\helpers\StringHelper::camelCase`)
the name before saving it (or look up).
2. Let the plugin create the groups, then add the needed permissions
3. Use an event! See the [EVENT_AFTER_RESPONSE_TO_USER](/configure/events.html#assign-user-to-a-user-group-based-on-a-property) example.

