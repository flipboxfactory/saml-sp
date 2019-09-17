# Installation / Upgrading

### Option 1: Composer
Composer is a simple, straight forward installation.  Simply run the following command from your project root:

```bash
composer require flipboxfactory/saml-sp
```

### Option 2: Plugin Store
Within your Craft CMS project admin panel, navigate to the 'Plugin Store' and search for `SAML SSO Service Provider`.  Installation is a button click away.


Once the plugin is installed, head over to [configure](/configure/) the plugin.

## Upgrading to 2.0
There are a lot of breaking changes in 2.0. Upgrading is seamless if there is not any custom code that 
hooks into the SAML SP plugin.  

### When Changes are Needed 

You will need to refactor some of the code if you are doing any of the following:
1) Hooking into the plugin with Craft/Yii2 Events. See a [list of the events](/configure/events.html#list).
2) Are using callables/anonymous functions with the 
`responseAttributeMap` config within `config/saml-sp.php`.
3)  Have any references to [lightsaml/lightsaml](https://github.com/lightSAML/lightSAML) package. We have 
switch from using this package to using [simplesamlphp/saml2](https://github.com/simplesamlphp/saml2)

::: tip 
Search for `LightSaml` within your project to find items that need to be refactored. 
:::


### Be Diligent with this Upgrade!
1) Backup the production database 
2) Test the upgrade on a dev instance
3) Test Login/SSO and Logout/SLO (if logout is used)

[Submit an Issue](https://github.com/flipboxfactory/saml-sp/issues) or [Contact us](https://www.flipboxdigital.com/contact) if you are running into any problems.