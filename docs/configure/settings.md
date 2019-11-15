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

TODO: We are currenly working on an enhancement that allows changing the EntityID per service provider much like you can do system wide.
