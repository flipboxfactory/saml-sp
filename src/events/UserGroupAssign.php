<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\events;

use craft\elements\User;
use craft\models\UserGroup;
use flipbox\saml\sp\models\Settings;
use SAML2\Response;
use yii\base\Event;

/**
 * Class UserLogin
 * @package flipbox\saml\sp\events
 */
class UserGroupAssign extends Event
{
    /**
     * @var User
     */
    public $user;
    /**
     * @var Response
     */
    public $response;

    /**
     * Pulled from $user->getGroups()
     * @var UserGroup
     */
    public $existingGroups;
    /**
     * All of the groups found in all of the assertions
     * @var UserGroup
     */
    public $groupsFoundInAssertions;

    /**
     * The list of user group to be assigned.
     * Edit this property list as needed.
     * If the config for merging existing groups is set to true, this property will reflect it
     * accordingly.
     * @see Settings::$mergeExistingGroups
     * @var UserGroup[]
     */
    public $groupToBeAssigned;
}
