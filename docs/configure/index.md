# Overview

There are two steps to configure the plugin.

## Getting Started

::: warn
Quick Warning: Obviously, security is of the utmost importance. There are 2 explicit 
security settings that may need to be toggled BUT it is crucial you understand 
that one needs to be set to true.
:::

See the following:


```php
// config/saml-sp.php

return [
    'requireResponseToBeSigned' => true,
    'requireAssertionToBeSigned' => true
];
```
Azure Ad or Azure Entra ID, by default, only signs the assertion so you may have
set the `requireResponseToBeSigned` to `false` **BUT THERE ARE NO CIRCUMSTANCES 
WHERE BOTH OF THESE CONFIGURATION WOULD BE FALSE!** This is a huge security risk. 
The signature ensures the sender which is cirtical.


### Step 1: Create your Service Provider
- Navigation (from the sidebar): My Provider
- Control Panel path reference: `/admin/saml-sp/metadata/my-provider`.

To create your Service Provider, the sites entity definition, navigate to "My Provider". There you can 
generate a key pair for encryption and message signing, as well as, give your provider a label. 
Giving providers a label can be helpful when you have multiple environments. You can name it whatever you 
think best but something like `Production` can be useful.

### Step 2: Import your Identity Provider's (IDP) metadata
- Navigation (from the sidebar): Provider List > click the "+ Add Identity Provider" button
- Control Panel path reference: `/admin/saml-sp/metadata/new-idp`.

Retrieve your IDP's metadata and import it into Craft. The IDP metadata can be difficult to find but they do give access to this information. 
The metadata has everything needed to communicate with the remote provider. Copy the contents of the metadata xml, 
navigate to "+Add Identity Provider", and paste it into the metadata field. Label the IDP with a descriptor of the provider. 
For example, if the IDP is ADFS it can be labeled as such: `ADFS`. From this view, you can click the "Configure" tab where
you can map attributes from the IDP response to the Craft user property.




