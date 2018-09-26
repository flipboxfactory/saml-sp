# Overview

There are two steps to configure the plugin.

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




