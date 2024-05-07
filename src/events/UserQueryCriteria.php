<?php

namespace flipbox\saml\sp\events;

use craft\elements\db\UserQuery;
use yii\base\Event;

class UserQueryCriteria extends Event
{
    public UserQuery $userQuery;

    public string $usernameOrEmail;

    public bool $applyDefaultCriteria = true;

    public bool $archived;
}
