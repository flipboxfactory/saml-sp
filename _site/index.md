# Overview

Saml Service Provider (SP) gives Single Sign On (SSO) and Single Logout Out (SLO) to CraftCMS (version 3)

## Features include:
* Single Sign On (SSO) with Identity Providers such as [OKTA](https://www.okta.com/)
* SSO using SAML 2.0 over POST Bindings
* Can consume signed messages and assertions from the Identity Provider (IDP)
* Can consume encrypted attributes/assertions from the IDP
* User field attribute sync on login with configuration options and Yii2 
events your customize process
* User group sync on login 
* Single Log Out (SLO)
* Supports both MySQL and PostgreSQL drivers