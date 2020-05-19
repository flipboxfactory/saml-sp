<?php


namespace flipbox\saml\sp\events;

use flipbox\saml\sp\records\ProviderRecord;
use yii\base\Event;

/**
 * Handles event hooks for modifying the RelayState
 * @package flipbox\saml\sp\events
 */
class RelayState extends Event
{
    /**
     * @var ProviderRecord
     */
    public $idp;
    /**
     * @var ProviderRecord
     */
    public $sp;
    /**
     * The RelayState
     * This variable will be used to create the RelayState, which is sent to the IdP, then comes back here to the SP.
     * Alter for \flipbox\saml\sp\controllers\LoginController::EVENT_BEFORE_RELAYSTATE_CREATION
     * @var string
     */
    public $relayState;
    /**
     * Where the user is being redirected.
     * This variable will be used to redirect the user, when they come back to craft on successful login.
     * Alter for \flipbox\saml\sp\controllers\LoginController::EVENT_BEFORE_RELAYSTATE_REDIRECT
     * @var string
     */
    public $redirect;
}
