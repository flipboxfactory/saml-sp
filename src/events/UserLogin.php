<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\events;

use craft\elements\User;
use LightSaml\Model\Protocol\Response;
use yii\base\Event;

/**
 * Class UserLogin
 * @package flipbox\saml\sp\events
 */
class UserLogin extends Event
{

    /**
     * @var User
     */
    public $user;
    /**
     * @var Response
     */
    public $response;
}
