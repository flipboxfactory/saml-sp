## What is SAML?
Answer: [Here's wikipedia's answer](https://en.wikipedia.org/wiki/Security_Assertion_Markup_Language)
> Security Assertion Markup Language (SAML, pronounced SAM-el) is an open standard for exchanging authentication and authorization data between parties, in particular, between an identity provider and a service provider. SAML is an XML-based markup language for security assertions (statements that service providers use to make access-control decisions). SAML is also:
> - A set of XML-based protocol messages
> - set of protocol message bindings
> - set of profiles (utilizing all of the above)
> - The single most important use case that SAML addresses is web browser single sign-on (SSO). Single sign-on is relatively easy to accomplish within a security domain (using cookies, for example) but extending SSO across security domains is more difficult and resulted in the proliferation of non-interoperable proprietary technologies. The SAML Web Browser SSO profile was specified and standardized to promote interoperability.

::: tip

### SAML SP 101

This plugin is concerned with the Service Provider (SP) site. It's role is to receive authentication and authorization
messages from the Identity Provider (IdP). Upon a successful IdP login, a message login status (success or not), 
session info, and user attributes are POSTed (usually POST, sometimes GET) to the Craft site and received and validated by the plugin. 
Then the user is synced, logged in, and redirected to where the user initially intended to go.  
::: 

## What is the Entity ID?
Answer: An entity ID is a globally unique name for a SAML entity, either an Identity Provider (IdP) or a Service Provider (SP)

## Is there [Multi-Site](https://docs.craftcms.com/v3/sites.html) support?
Answer: Yes, as of 2.0.1, a multi-site configuration should work seamlessly. 

::: tip
Multi-Site
When you create "My Provider", the Entity Id and endpoints default to the current site. 
This should not create any problems. If the appearance is a aesthetic issue for whatever reason, 
use the Primary site when creating "My Provider". You can also override the default Entity Id if desired.  
::: 

## Error: Trying to get property 'keychain' of non-object
Answer: This usually means "My Provider", the sites metadata/provider can't be found, or possibly, hasn't 
been created. Go to `https://<your domain>/admin/saml-sp/metadata/my-provider` and create a new provider
for your site.

