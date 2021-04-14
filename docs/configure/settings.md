## Plugin Settings
You may override the default plugin settings by creating a `/config/saml-sp.php` file.

View the settings you can override in your project at `/vendor/flipboxfactory/saml-sp/src/models/Settings.php`. 
Each setting has a description of what it does and how to customize it.

### EntityID
The Entity ID is the unique ID for the provider (your Craft instance) and defaults to the default site's base url. 
There are two Entity ID's to understand for Service Provider (again, your Craft instance) and these 2 Entity Ids 
must match to make things work properly.

1. System wide Entity ID
2. Provider Entity ID

#### System Wide Entity ID
The system wide Entity ID is the Entity ID of the current environment. You can have multiple environments which will
change depending on which environment you're on (like your base url changes based on the environment). 

Currently, you can edit the EntityID system wide a couple ways. 

##### 1. Add a config file in `config/saml-sp.php`.
This location lives right next to the `general.php`. Below is an example file contents.

```php
return [
   'entityId' => 'https://my-entity-id',
];
```

You can also use environmental variables with Craft parser by passing it as a string.
```php
return [
   'entityId' => '$ENTITY_ID',
];
```


##### 2. Edit from the admin (Goto the plugin in craft, then click on the "Settings" menu item under the plugins sub nav).
Set the "Default Entity ID" there which will save it to the project config. Environmental variable that can be parsed by 
the Craft parse can also be set here (ie, $ENTITY_ID).

#### Provider Entity ID
Like the system wide Entity ID, there can be multiple providers, possibly one per environment with 
different configurations. These providers are saved in the database and must be static due to the configuration data 
share with the IdP. Provider Entity IDs can be modified on the edit page of the provider in the plugin control panel.



### Group Configuration/Group Assignment
#### Group Attribute Name/Group Attribute Mapping
Many IdPs will send groups as an attribute within the SAML Response. When configured, the plugin use that attribute to
 assign the user to the group properly. To achieve this,  add a `config/saml-sp.php` then use the
 following configuration.
##### Example
```php
return [
    // change the value as needed
    'groupAttributeNames' => [ 'groups' ]
];
```

Make sure the value matches the attribute name sent from the IdP. 

#### Auto-Create Groups Removed (version 2.3.0)
Due to the release of Craft CMS 3.5, the project config has become more prevalant. Therefore, we've decided 
to remove the support for `autoCreateGroups` in version 2.3.0. Production environments shouldn't be saving user 
groups when following the project config workflow. This should be done at the lower level environments.

Please use an event as shown below.

#### Managing Permissions
Managing permissions ** is not supported ** automatically. There are a few options on how to manually manage 
permissions:
1. Create the groups before implementing the plugin. Make sure you know all of the groups needed to assign the users
properly.
2. Use an event! See the [EVENT_AFTER_RESPONSE_TO_USER](/configure/events.html#assign-user-to-a-user-group-based-on-a-property) example.

