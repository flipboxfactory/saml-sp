<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 8:30 PM
 */

namespace flipbox\saml\sp\services;

use craft\base\Component;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\sp\events\UserLogin;
use flipbox\saml\sp\Saml;
use SAML2\Response as SamlResponse;
use yii\base\UserException;
use flipbox\saml\sp\services\login\AssertionTrait;

/**
 * Class Consumer
 * @package flipbox\saml\sp\services\login
 */
class Login extends Component
{
    use AssertionTrait;

    const EVENT_BEFORE_RESPONSE_TO_USER = 'eventBeforeResponseToUser';
    const EVENT_AFTER_RESPONSE_TO_USER = 'eventAfterResponseToUser';

    /**
     * @param SamlResponse $response
     * @return \flipbox\saml\sp\records\ProviderIdentityRecord
     * @throws InvalidMessage
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function login(SamlResponse $response)
    {

        $assertion = $this->getFirstAssertion($response);
        Saml::getInstance()->getResponse()->isValidTimeAssertion($assertion);
        Saml::getInstance()->getResponse()->isValidAssertion($assertion);

        /**
         * Get User
         */
        $user = Saml::getInstance()->getUser()->getByResponse($response);

        /**
         * Before user save
         */
        $event = new UserLogin();
        $event->response = $response;
        $event->user = $user;

        $this->trigger(
            static::EVENT_BEFORE_RESPONSE_TO_USER,
            $event
        );
        /**
         * Sync User
         */
        Saml::getInstance()->getUser()->sync($user, $response);

        /**
         * after user save
         */
        $event = new UserLogin();
        $event->response = $response;
        $event->user = $user;

        $this->trigger(
            static::EVENT_AFTER_RESPONSE_TO_USER,
            $event
        );

        /**
         * Get Identity
         */
        $identity = Saml::getInstance()->getProviderIdentity()->getByUserAndResponse($user, $response);

        /**
         * Log user in
         */
        if (! Saml::getInstance()->getUser()->login($identity)) {
            throw new UserException("Unknown error while logging in.");
        }

        /**
         * User's successfully logged in so we can now set the lastLogin for the
         * provider identity and save it to the db.
         */
        $identity->lastLoginDate = new \DateTime();
        if (! Saml::getInstance()->getProviderIdentity()->save($identity)) {
            throw new UserException("Error while saving identity.");
        }

        return $identity;
    }
}
