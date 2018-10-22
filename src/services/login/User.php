<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use craft\elements\User as UserElement;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\sp\helpers\ProviderHelper;
use flipbox\saml\sp\helpers\UserHelper;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;
use LightSaml\Error\LightSamlException;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Protocol\Response as SamlResponse;
use yii\base\UserException;

/**
 * Class User
 * @package flipbox\saml\sp\services
 */
class User
{
    use AssertionTrait;

    /**
     * @param SamlResponse $response
     * @return UserElement
     * @throws InvalidMessage
     * @throws UserException
     */
    public function getByResponse(\LightSaml\Model\Protocol\Response $response)
    {

        $assertion = $this->getFirstAssertion($response);

        if (! $assertion->getSubject()->getNameID()) {
            throw new LightSamlException('Name ID is missing.');
        }

        /**
         * Get username from the NameID
         *
         * @todo Give an option to map another attribute value to $username (like email)
         */
        $username = $assertion->getSubject()->getNameID()->getValue();

        return $this->find($username);
    }

    /**
     * @param ProviderIdentityRecord $identity
     * @return bool
     * @throws UserException
     * @throws \Throwable
     */
    public function login(\flipbox\saml\sp\records\ProviderIdentityRecord $identity)
    {
        if ($identity->getUser()->getStatus() !== UserElement::STATUS_ACTIVE) {
            if (! \Craft::$app->getUsers()->activateUser($identity->getUser())) {
                throw new UserException("Can't activate user.");
            }
        }

        if (\Craft::$app->getUser()->login(
            $identity->getUser(),
            /**
             * @todo read session duration from the response
             */
            \Craft::$app->getConfig()->getGeneral()->userSessionDuration
        )
        ) {
            $identity->lastLoginDate = new \DateTime();
        } else {
            throw new UserException("User login failed.");
        }

        return true;
    }

    /**
     * @param UserElement $user
     * @param SamlResponse $response
     * @throws UserException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function sync(UserElement $user, \LightSaml\Model\Protocol\Response $response)
    {
        /**
         * enable and transform the user
         */
        $this->construct($user, $response);

        /**
         * Save
         */
        $this->save($user);


        /**
         * Sync groups depending on the plugin setting.
         */
        Saml::getInstance()->getUserGroups()->syncByAssertion($user, $this->getFirstAssertion($response));

        /**
         * Sync defaults
         */
        Saml::getInstance()->getUserGroups()->assignDefaultGroups($user);
    }

    /**
     * @param UserElement $user
     * @return bool
     * @throws UserException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function save(UserElement $user)
    {
        if (! \Craft::$app->getElements()->saveElement($user)) {
            Saml::error(
                'User save failed: ' . json_encode($user->getErrors())
            );
            throw new UserException("User save failed: " . json_encode($user->getErrors()));
        }

        return true;
    }

    /**
     * Response Based Methods
     */

    /**
     * @param UserElement $user
     * @param SamlResponse $response
     * @throws UserException
     * @throws \Throwable
     */
    protected function construct(UserElement $user, \LightSaml\Model\Protocol\Response $response)
    {
        /**
         * Is User Active?
         */
        if (! UserHelper::isUserActive($user)) {
            if (! Saml::getInstance()->getSettings()->enableUsers) {
                throw new UserException('User access denied.');
            }
            UserHelper::enableUser($user);
        }

        $assertion = $this->getFirstAssertion($response);

        if ($assertion->getFirstAttributeStatement()) {
            $this->transform($response, $user);
        } else {
            /**
             * There doesn't seem to be any attribute statements.
             * Try and use username for the email and move on.
             */
            \Craft::warning(
                'No attribute statements found! Trying to assign username as the email.',
                Saml::getInstance()->getHandle()
            );
            $user->email = $user->email ?: $user->username;
        }
    }

    /**
     * @param SamlResponse $response
     * @param UserElement $user
     * @return UserElement
     */
    protected function transform(
        SamlResponse $response,
        UserElement $user
    ) {

        $assertion = $response->getFirstAssertion();

        /**
         * Check the provider first
         */
        $attributeMap = ProviderHelper::providerMappingToKeyValue(
            $idpProvider = Saml::getInstance()->getProvider()->findByEntityId(
                $response->getIssuer()->getValue()
            )->one()
        ) ?:
            Saml::getInstance()->getSettings()->responseAttributeMap;

        /**
         * Loop thru attributes and set to the user
         */
        foreach ($assertion->getFirstAttributeStatement()->getAllAttributes() as $attribute) {
            if (isset($attributeMap[$attribute->getName()])) {
                $craftProperty = $attributeMap[$attribute->getName()];
                $this->assignProperty(
                    $user,
                    $attribute,
                    $craftProperty
                );
            }
        }

        return $user;
    }

    /**
     * @param User $user
     * @param Attribute $attribute
     * @param mixed $craftProperty
     */
    protected function assignProperty(
        UserElement $user,
        Attribute $attribute,
        $craftProperty
    ) {

        if (is_string($craftProperty) && property_exists($user, $craftProperty)) {
            Saml::debug(
                sprintf(
                    'Attribute %s is scalar and should set value "%s" to user->%s',
                    $attribute->getName(),
                    $attribute->getFirstAttributeValue(),
                    $craftProperty
                )
            );
            $user->{$craftProperty} = $attribute->getFirstAttributeValue();
        } elseif (is_callable($craftProperty)) {
            Saml::debug(
                sprintf(
                    'Attribute %s is handled with a callable.',
                    $attribute->getName()
                )
            );
            call_user_func($craftProperty, $user, $attribute);
        }
    }

    /**************************************************
     * Craft User Methods
     **************************************************/

    /**
     * @param $username
     * @return UserElement
     * @throws UserException
     */
    protected function find($username)
    {
        return $this->forceGet($username);
    }

    /**
     * @param $username
     * @return UserElement
     * @throws UserException
     */
    protected function forceGet($username)
    {

        /**
         * Is there a user that exists already?
         */
        if ($user = $this->getByUsernameOrEmail($username)) {
            /**
             * System check for whether we are allowed merge with this this user
             */
            if (! Saml::getInstance()->getSettings()->mergeLocalUsers) {
                //don't continue
                throw new UserException(
                    sprintf(
                        "User (%s) already exists.",
                        $username
                    )
                );
            }
        } else {
            /**
             * New UserElement
             */
            $user = new UserElement(
                [
                    'username' => $username
                ]
            );
        }

        return $user;
    }

    /**
     * @param $emailOrUsername
     * @return UserElement|null
     */
    protected function getByUsernameOrEmail($usernameOrEmail, bool $archived = false)
    {

        return UserElement::find()
            ->where(
                [
                    'or',
                    ['username' => $usernameOrEmail],
                    ['email' => $usernameOrEmail]
                ]
            )
            ->addSelect(['users.password', 'users.passwordResetRequired'])
            ->status(null)
            ->archived($archived)
            ->one();
    }
}
