<?php

/**
 * Sandbox only: login hooks for dev/idp-login --test-groups. Not part of the plugin.
 *
 * Does nothing unless /runtime/saml-sp.overrides.json has a "hooks" key:
 *   readGroupsBeforeSync  Load the user's (cached) groups before the user is synced, as another
 *                         listener or plugin might.
 *   assignDuringLogin     Group handles to add in Login::EVENT_AFTER_RESPONSE_TO_USER with
 *                         Users::assignUserToGroups(), without $user->setGroups().
 */

use craft\models\UserGroup;
use flipbox\saml\sp\events\UserLogin;
use flipbox\saml\sp\services\Login;
use yii\base\Event;

$overridesFile = '/runtime/saml-sp.overrides.json';
$hooks = is_file($overridesFile) ? (json_decode((string)file_get_contents($overridesFile), true)['hooks'] ?? []) : [];

if (!empty($hooks['readGroupsBeforeSync'])) {
    Event::on(Login::class, Login::EVENT_BEFORE_RESPONSE_TO_USER, function(UserLogin $event) {
        if ($event->user->id) {
            $event->user->getGroups();
        }
    });
}

if (!empty($hooks['assignDuringLogin'])) {
    Event::on(Login::class, Login::EVENT_AFTER_RESPONSE_TO_USER, function(UserLogin $event) use ($hooks) {
        $groups = Craft::$app->getUserGroups();
        $ids = array_map(fn(UserGroup $g) => $g->id, $groups->getGroupsByUserId($event->user->id));
        foreach ($hooks['assignDuringLogin'] as $handle) {
            $ids[] = $groups->getGroupByHandle($handle)?->id;
        }
        Craft::$app->getUsers()->assignUserToGroups($event->user->id, array_filter($ids));
    });
}
