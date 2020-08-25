<?php


namespace flipbox\saml\sp\events;

use yii\base\Event;

class AuthnRequest extends Event
{
    /** @var \SAML2\AuthnRequest */
    public $message;
}
