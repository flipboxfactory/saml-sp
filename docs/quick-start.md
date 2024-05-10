# Getting Started in 5 minutes

We are using Azure AD because it is the most popular IdP used but the steps are similar to all IdPs.
This guide assumes you've installed the plugin and are using Craft CMS Pro, which is required.

## Configure "My Provider", Craft as an SP

First configure Craft as an SP. Open the plugin admin and goto "My Provider" in the side menu.

1. Label the SP as needed. Adding environment here is helpful.
2. Click the "Generate key pair"

## Configure the IdP - Azure AD Application

Configuring the IdP varies. Here's how to configure Azure AD

1. Sign-in to Azure AD and navigate to the Active Directory area.
2. On the Side menu, goto "Enterprise Applications", then "+ Create your own application", at the top left, name the application and click next.
3. Then click "Single Sign-on", then "SAML", where you'll land on "Set up Single Sign-on with SAML".
4. Under "Basic SAML Configuration" 
   1. set the Entity ID to Craft's "My Provider" Entity ID.
   2. Reply URL can be found in the "My Provider" page under the "Metadata" tab (under "Bindings") and labeled "Assertion Consumer Service".
   3. Set SLO there as well if desired
   4. Save
5. Under "SAML Signing Certificate" download "Federation Metadata XML"

## Configure the IdP in Craft
1. Back in the Craft control panel, goto "Provider List" in the SAML SP Plugin side menu
2. Click "+ Add Identity Provider" to create the IdP 
3. Configure require fields
   1. Add a human readable label
   2. On the "Metadata" tab, paste the Federation Metadata XML downloaded from Azure AD
   3. On the "Configure" tab, click "+ Add a new mapping" and set all mapping
      1. Set "Attribute Name" to "http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname" and "Craft User Property" to "First Name"
      2. Set "Attribute Name" to "http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname" and "Craft User Property" to "Last Name"
      3. Set "Attribute Name" to "http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress" and "Craft User Property" to "Email". NOTE: Email is required.
   
## Configure Login Path
Within the `config/general.php`, you can set the `loginPath` to the value ("Login Path") set in the IdP (within the Craft SAML SP Plugin control panel), 
under the "Configure" tab.

## Other Plugin Configurations and Recommendations
Create a `config/saml-sp.php` to add some of the following configurations:
- `requireResponseToBeSigned` - By default, Azure AD doesn't sign the response. Set this value to `false`. This will error on login if the config isn't set to false and the signature isn't found.
- `entityId` - We recommend setting this to an environmental variable which matches the Entity ID of "My Provider" you created.

