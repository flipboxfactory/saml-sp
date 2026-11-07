#!/usr/bin/env bash
# Builds a fresh Craft 5 site with saml-sp on every container start, then serves it.
set -euo pipefail

log() { echo "[sandbox] $*"; }

# Container restart (not dev/up): the site already exists, just serve it
if [ -f /app/craft ] && [ -f /runtime/sandbox.json ]; then
    exec apache2-foreground
fi

rm -f /runtime/sandbox.json

log "Creating Craft project"
composer create-project craftcms/craft /app --no-interaction --no-scripts --quiet

cd /app
composer config minimum-stability dev
composer config prefer-stable true
composer config allow-plugins.craftcms/plugin-installer true
composer config allow-plugins.yiisoft/yii2-composer true

# saml-sp always comes from the working copy (symlinked, so edits are live)
composer config repositories.saml-sp '{"type":"path","url":"/plugins/saml-sp","options":{"symlink":true}}'
if [ "${LOCAL_DEPS}" = "1" ]; then
    log "Using local saml-core and keychain checkouts"
    [ -f /siblings/saml-core/composer.json ] && [ -f /siblings/keychain/composer.json ] \
        || { log "LOCAL_DEPS=1 needs saml-core and keychain next to saml-sp (or set SIBLINGS_DIR)"; exit 1; }
    composer config repositories.saml-core '{"type":"path","url":"/siblings/saml-core","options":{"symlink":true}}'
    composer config repositories.keychain '{"type":"path","url":"/siblings/keychain","options":{"symlink":true}}'
fi

log "Installing saml-sp"
# -W: saml2 4.19.x needs webmozart/assert ^1.9, while the Craft starter locks 2.x
composer require --no-interaction --no-progress -W "flipboxfactory/saml-sp:*@dev" 2>&1 | grep -vE "^\s*-|^\s*$" || true
composer show flipboxfactory/saml-sp >/dev/null 2>&1 || { log "saml-sp failed to install"; exit 1; }
for p in flipboxfactory/saml-sp flipboxfactory/saml-core flipboxfactory/keychain simplesamlphp/saml2; do
    printf '[sandbox]   %-28s %s\n' "$p" "$(composer show "$p" | sed -n 's/^versions : \* //p') $(composer show "$p" | sed -n 's/^dist *: \[path\] \([^ ]*\).*/(local: \1)/p' | head -1)"
done

# Sandbox console command + front-end pages
printf '<?php\nrequire_once "/sandbox/SetupController.php";\nreturn ["controllerMap" => ["sandbox" => \\sandbox\\SetupController::class]];\n' > config/app.console.php
printf '<?php\nreturn ["entityId" => getenv("SANDBOX_URL")];\n' > config/saml-sp.php
cp -R /sandbox-templates/. templates/

log "Installing Craft"
php craft install/craft --interactive=0 \
    --username=admin --password=password --email=admin@example.com \
    --site-name="saml-sp sandbox" --site-url="${SANDBOX_URL}" --language=en-US
php craft plugin/install keychain --interactive=0
php craft plugin/install saml-sp --interactive=0

log "Configuring SP and IdP"
php craft sandbox/setup /runtime/idp-metadata.xml /runtime/sandbox.json --interactive=0

chown -R www-data:www-data storage web/cpresources config
log "Ready at ${SANDBOX_URL}"
exec apache2-foreground
