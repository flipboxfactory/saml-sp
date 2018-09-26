# KeyChain

### Set a key pair for your provider
This plugin installs and uses our [KeyChain Plugin](https://github.com/flipboxfactory/keychain) to manage your key pairs. It's able to create key pairs using OpenSSL or import your existing key pairs.

::: tip Recommended
Create a key pair for your provider. SAML protocol may sign and encrypt messages between the Identity Provider (IDP) and the Service Provider (SP). This makes the communication and transmission of the data more secure and any good IDP will support signing and encrypting message data. This requires a key pair.
:::

#### Option 1: Generate Key Pair 

When you are creating your provider, there is an option on the right sidebar to "Generate key pair".

Clicking that button will generate a key pair for you using OpenSSL and some default settings.

- Control Panel path reference: `/admin/saml-sp/metadata/my-provider`.
- Navigate (from sidebar): My Provider

#### Option 2: Create a Key Pair with OpenSSL

From the SAML plugin in the Craft admin, go to 'Create a new key pair'. This form contains the same fields you may be familiar with when running the command via the CLI. Use a descriptive name to identify the key pair so you can match it to your provider later.

- Control Panel path reference: `/admin/saml-sp/keychain/new-openssl`.
- Navigate (from sidebar): Keychain > Dropdown button in the upper right "Create New OpenSSL Key Pair"

#### Option 3: Bring your Own Key Pair (BYOK)

Generate a key by running the following command in your terminal. Copy the key pairs. 
```bash
openssl req -new -x509 -days 365 -nodes -sha256 -out saml-sp.crt -keyout saml-sp.pem
```
From the SAML plugin in the Craft admin, import the key pair at 'Create new key pair'.

- Control Panel path reference: `/admin/saml-sp/keychain/new`.
- Navigate (from sidebar): Keychain > Button in the upper right "Add New Key Pair"
