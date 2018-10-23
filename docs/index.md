# Overview

SAML SSO Service Provider creates an easy way to do Single Sign-On (SSO) and Single Logout (SLO) in Craft CMS version 3.

### Features Include:
* Single Sign-On (SSO) with Identity Providers like [Okta](https://okta.com), Google, etc
* SSO using SAML 2.0 over POST Bindings
* Consuming signed messages & assertions from Identity Provider (IDP)
* Consuming encrypted attributes/assertions from Identity Provider
* User field attribute sync on login (Via config options and Yii2 events)
* User group sync on login
* Single Logout (SLO)
* Supports both MySQL and PostgreSQL drivers