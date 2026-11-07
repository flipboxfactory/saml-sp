# saml-sp sandbox

A throwaway Craft 5 site running saml-sp from this working copy, with [saml-tool](https://github.com/dsmrt/saml-tool) as the identity provider. Every `dev/up` starts from scratch; `dev/down` deletes everything.

## Requirements

- saml-sp 5.1.4 or later checked out. Older versions accept saml-core 5.0.10, which Composer pairs with saml2 4.20.x on a fresh Craft install, and that combination doesn't work.
- Docker
- [Bun](https://bun.sh) and a saml-tool checkout with an IdP cert/key in `certs/` (default `~/Code/dsmrt/saml-tool`; override with `SAML_TOOL_DIR`)

## Use

```bash
dev/up            # fresh site on http://localhost:8080 (admin / password)
dev/idp-login     # IdP-initiated login: saml-tool POSTs a signed Response in your browser
dev/down          # destroy containers, database and generated config
```

`dev/up` does the following:

1. Generates IdP metadata from saml-tool.
2. Builds a new Craft project with saml-sp installed from this checkout. The install is symlinked, so code edits apply immediately and you don't need to re-run `dev/up`.
3. Creates the SP (with a fresh KeyChain key pair), the saml-tool IdP, and the `Editors`/`Reviewers` groups.
4. Writes `dev/.runtime/saml-tool.config.ts`, the Response config that `dev/idp-login` sends.

The home page shows who is logged in and their groups, plus SP-initiated login and logout links.

### SP-initiated login

```bash
(cd ~/Code/dsmrt/saml-tool && bun run receiver)   # IdP SSO endpoint on http://localhost:3000/receive
```

Click **SP-initiated login** on http://localhost:8080. The receiver shows the AuthnRequest. Reply with its ID and RelayState:

```bash
dev/idp-login --in-response-to <ID> -r <RelayState>
```

### Trying other responses

`dev/idp-login` passes extra options to saml-tool's `send-response.ts`:

```bash
dev/idp-login --no-sign-assertion     # should be rejected
dev/idp-login --no-sign-response      # should be rejected
dev/idp-login -o xml                  # print the Response instead of sending it
```

For different users, groups or attributes, edit `dev/.runtime/saml-tool.config.ts`. `-n` changes only the NameID; Craft takes the email from the `email` attribute. Groups match by name or handle (`Editors`/`editors`).

### Group sync checks

```bash
dev/idp-login --test-groups
```

Runs `dev/test-groups.ts` against the running sandbox, without a browser. For each scenario it:

1. sets plugin settings and login hooks in `dev/.runtime/saml-sp.overrides.json`;
2. gives the config's user a starting set of groups;
3. POSTs a signed Response asserting `Editors`;
4. checks the user's stored groups.

It covers `syncGroups`/`mergeExistingGroups` combinations, default groups, and a group assigned during login after the user's groups were cached. The overrides file is deleted afterwards. Sandboxes created before this was added need `dev/up` again.

## Options

| Variable | Default | |
|---|---|---|
| `SANDBOX_PORT` | `8080` | Site port |
| `LOCAL_DEPS` | `0` | `1` installs saml-core and keychain from the sibling checkouts instead of Packagist |
| `SIBLINGS_DIR` | `..` of this repo | Directory containing the `saml-core` and `keychain` checkouts |
| `SAML_TOOL_DIR` | `~/Code/dsmrt/saml-tool` | saml-tool checkout |
| `IDP_URL` | `http://localhost:3000` | saml-tool base URL used in the IdP metadata |

Example: `LOCAL_DEPS=1 dev/up`.

## Notes

- **Sandbox only.** The Craft security key, admin password and edition switch (Pro, which is free on localhost) are deliberately insecure. The setup command is `dev/sandbox/SetupController.php`, and it is not part of the plugin.
- **Composer `-W`.** saml2 4.19.x needs `webmozart/assert ^1.9`, while new Craft projects lock 2.x, so the install uses `composer require -W`.
- **Logs and shell.** `docker compose -f dev/compose.yaml logs -f web` shows the logs, and `docker compose -f dev/compose.yaml exec web bash` opens a shell; the Craft project is at `/app`.
