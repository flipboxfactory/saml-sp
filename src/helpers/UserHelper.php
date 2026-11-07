<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\helpers;

use craft\elements\User as UserElement;

/**
 * Class UserHelper
 *
 * @package flipbox\saml\sp\helpers
 */
class UserHelper
{
    /**
     * Re-enables a disabled, archived, suspended, pending or inactive user. It deliberately doesn't
     * clear a password lockout on an otherwise active user; login is refused until the lockout ends.
     *
     * @param UserElement $user
     * @throws \Throwable
     */
    public static function enableUser(UserElement $user)
    {
        if (static::isUserSuspended($user)) {
            \Craft::$app->getUsers()->unsuspendUser($user);
        }

        if (!$user->enabled) {
            $user->enabled = true;
        }

        if ($user->archived) {
            $user->archived = false;
        }

        if (!static::isUserActive($user)) {
            \Craft::$app->getUsers()->activateUser($user);
        }
    }

    /**
     * @param UserElement $user
     * @return bool
     */
    public static function isUserPending(UserElement $user)
    {
        return false === static::isUserActive($user) &&
            $user->getStatus() === UserElement::STATUS_PENDING;
    }

    /**
     * @param UserElement $user
     * @return bool
     */
    public static function isUserArchived(UserElement $user)
    {
        return false === static::isUserActive($user) &&
            $user->getStatus() === UserElement::STATUS_ARCHIVED;
    }

    /**
     * @param UserElement $user
     * @return bool
     */
    public static function isUserLocked(UserElement $user)
    {
        // Craft 5 tracks lockouts with a flag, not a status: getStatus() never returns STATUS_LOCKED
        return $user->locked;
    }

    /**
     * @param UserElement $user
     * @return bool
     */
    public static function isUserSuspended(UserElement $user)
    {
        return false === static::isUserActive($user) &&
            $user->getStatus() === UserElement::STATUS_SUSPENDED;
    }

    /**
     * @param UserElement $user
     * @return bool
     */
    public static function isUserActive(UserElement $user)
    {
        return $user->getStatus() === UserElement::STATUS_ACTIVE;
    }
}
