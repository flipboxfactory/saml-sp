---
title: Configure
permalink: configure/
---

# Configure

There are 3 simple steps to configuring this plugin.
1. Create and save a key pair
2. Create your Provider (the Service Provider)
3. Import your Remote Provider's metadata (the Identity Provider) 

## Key Configuration

First thing to do is create a key pair.

It is highly recommended that you create a key pair for your provider. 
Saml protocol has the ability to sign and encrypt messages between the 
Identity Provider (IDP) and the Service Provider (SP). This makes the 
communication and transmission of the data more secure and any good IDP
will support signing and encrypting message data. This requires a key pair. 


Saml SP installs and uses the [KeyChain Plugin](https://github.com/flipboxfactory/keychain)
to manage key pairs which includes a way to create them using OpenSSL or 
import them. 

### Create a Key Pair with OpenSSL
From the Saml SP main plugin page in the admin, you can goto Create 
a new key pair with OpenSSL. This form contains the same fields you 
may be familiar with when running the command via openssl cli. Use 
the description to name and identify the key pair so you can 
choose it later and match it to your provider.  

### Bring your Own Key Pair (BYOK)
Create a key on your computer and import it. 

```bash
openssl req -new -x509 -days 365 -nodes -sha256 -out saml-sp.crt -keyout saml-sp.pem
```

Copy the contents and import them at "Create new key pair".

## Provider Configuration

### Your Provider (The Service Provider)
Once you have your key pair created, you can create your 
provider (the Service Provider or SP).

### The Remote Provider (The Identity Provider)
Now you must retrieve the IDPs metadata and import it into Craft. Sometimes
the IDP metadata can be somewhat difficult to find but they should be able
to give you access to this. The metadata has everything needed to communicate 
with the remote provider.
