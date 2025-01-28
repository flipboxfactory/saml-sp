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
     * @param UserElement $user
     * @throws \Throwable
     */
    public static function enableUser(UserElement $user)
    {
        if (static::isUserSuspended($user)) {
            \Craft::$app->getUsers()->unsuspendUser($user);
        }

        if (static::isUserLocked($user)) {
            \Craft::$app->getUsers()->unlockUser($user);
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
        return false === static::isUserActive($user) &&
            $user->getStatus() === UserElement::STATUS_LOCKED;
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
