<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 8:30 PM
 */

namespace flipbox\saml\sp\services;

use craft\base\Component;
use craft\elements\User;
use craft\helpers\StringHelper;
use craft\models\UserGroup;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\sp\events\UserLogin;
use flipbox\saml\sp\helpers\UserHelper;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;
use LightSaml\Error\LightSamlException;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Protocol\Response as SamlResponse;
use yii\base\Event;
use yii\base\UserException;

class Login extends Component
{

    /**
     * Use before or after now
     * @deprecated
     */
    const EVENT_RESPONSE_TO_USER = 'eventResponseToUser';
    const EVENT_BEFORE_RESPONSE_TO_USER = 'eventBeforeResponseToUser';
    const EVENT_AFTER_RESPONSE_TO_USER = 'eventAfterResponseToUser';

    protected $isAssertionDecrypted = false;

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
        $user = $this->getUserByResponse($response);

        /**
         * Sync User
         */
        $this->syncUser($user, $response);

        /**
         * Get Identity
         */
        $identity = $this->getIdentityByUserAndResponse($user, $response);

        /**
         * Log user in
         */
        if (! $this->loginUser($identity)) {
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

    /**
     * @param KeyChainRecord $keyChainRecord
     * @param SamlResponse $response
     */
    protected function decryptAssertions(KeyChainRecord $keyChainRecord, \LightSaml\Model\Protocol\Response $response)
    {
        Saml::getInstance()->getResponse()->decryptAssertions(
            $response,
            $keyChainRecord
        );
    }

    /**
     * @param SamlResponse $response
     * @return Assertion
     * @throws InvalidMessage
     */
    public function getFirstAssertion(\LightSaml\Model\Protocol\Response $response)
    {

        $ownProvider = Saml::getInstance()->getProvider()->findOwn();

        if ($ownProvider->keychain &&
            $response->getFirstEncryptedAssertion() &&
            $this->isAssertionDecrypted === false
        ) {
            $this->decryptAssertions(
                $ownProvider->keychain,
                $response
            );
            /**
             * try to only do this once
             */
            $this->isAssertionDecrypted = true;
        }

        $assertions = $response->getAllAssertions();

        if (! isset($assertions[0])) {
            throw new InvalidMessage("Invalid message. No assertions found in response.");
        }
        /**
         * Just grab the first one for now.
         */
        return $assertions[0];
    }

    /**
     * @param SamlResponse $response
     * @return User
     * @throws InvalidMessage
     * @throws UserException
     */
    protected function getUserByResponse(\LightSaml\Model\Protocol\Response $response)
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

        return $this->getUser($username);
    }

    /**
     * @param User $user
     * @param SamlResponse $response
     * @throws UserException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function syncUser(User $user, \LightSaml\Model\Protocol\Response $response)
    {
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
         * enable and transform the user
         */
        $this->constructUser($user, $response);

        /**
         * Save
         */
        $this->saveUser($user);

        /**
         * Sync groups depending on the plugin setting.
         */
        $this->syncUserGroupsByAssertion($user, $this->getFirstAssertion($response));

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
    }

    /**
     * @param User $user
     * @param SamlResponse $response
     * @return ProviderIdentityRecord
     * @throws InvalidMessage
     * @throws UserException
     */
    protected function getIdentityByUserAndResponse(User $user, \LightSaml\Model\Protocol\Response $response)
    {

        $idpProvider = Saml::getInstance()->getProvider()->findByEntityId(
            $response->getIssuer()->getValue()
        )->one();

        /**
         * Get Identity
         */
        $identity = $this->forceGetIdentity(
            $this->getFirstAssertion($response)->getSubject()->getNameID()->getValue(),
            $idpProvider
        );

        /**
         * Get Session
         */
        $sessionIndex = null;
        if ($response->getFirstAssertion()->hasAnySessionIndex()) {
            $sessionIndex = $response->getFirstAssertion()->getFirstAuthnStatement()->getSessionIndex();
        }

        /**
         * Set Identity Properties
         */
        $identity->userId = $user->id;
        $identity->enabled = true;
        $identity->sessionId = $sessionIndex;
        return $identity;
    }

    /**
     * @param User $user
     * @return bool
     * @throws UserException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function saveUser(User $user)
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
     * @param $username
     * @return User
     * @throws UserException
     */
    protected function getUser($username)
    {
        return $this->forceGetUser($username);
    }

    /**
     * @param User $user
     * @param SamlResponse $response
     * @throws UserException
     * @throws \Throwable
     */
    protected function constructUser(User $user, \LightSaml\Model\Protocol\Response $response)
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
            $this->transformToUser($response, $user);
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
     * @param string $nameId
     * @param ProviderInterface $provider
     * @return ProviderIdentityRecord
     * @throws UserException
     */
    protected function forceGetIdentity($nameId, ProviderInterface $provider)
    {
        // @var \flipbox\saml\sp\records\ProviderIdentityRecord $identity
        if (! $identity = Saml::getInstance()->getProviderIdentity()->findByNameId(
            $nameId,
            $provider
        )->one()
        ) {
            if (! Saml::getInstance()->getSettings()->createUser) {
                throw new UserException("System doesn't have permission to create a new user.");
            }

            /**
             * Create the new identity if one wasn't found above.
             * Since we now have the user id, and we might not have above,
             * do this last.
             */
            $identity = new ProviderIdentityRecord(
                [
                    'providerId' => $provider->id,
                    'nameId'     => $nameId,
                ]
            );
        }

        return $identity;
    }

    /**
     * @param $username
     * @return User
     * @throws UserException
     */
    protected function forceGetUser($username)
    {

        /**
         * Is there a user that exists already?
         */
        if ($user = $this->getUserByUsernameOrEmail($username)) {
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
             * New User
             */
            $user = new User(
                [
                    'username' => $username
                ]
            );
        }

        return $user;
    }

    /**
     * @param $emailOrUsername
     * @return User|null
     */
    protected function getUserByUsernameOrEmail($usernameOrEmail, bool $archived = false)
    {

        return User::find()
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

    /**
     * @param User $user
     * @param Assertion $assertion
     * @return bool
     * @throws UserException
     */
    protected function syncUserGroupsByAssertion(User $user, Assertion $assertion)
    {
        /**
         * Nothing to do, move on
         */
        if (false === Saml::getInstance()->getSettings()->syncGroups) {
            return true;
        }

        $groupNames = Saml::getInstance()->getSettings()->groupAttributeNames;
        $groups = [];
        /**
         * Make sure there is an attribute statement
         */
        if (! $assertion->getFirstAttributeStatement()) {
            Saml::debug(
                'No attribute statement found, moving on.'
            );
            return true;
        }

        foreach ($assertion->getFirstAttributeStatement()->getAllAttributes() as $attribute) {
            Saml::debug(
                sprintf(
                    'Is attribute group? "%s" in %s',
                    $attribute->getName(),
                    json_encode($groupNames)
                )
            );
            /**
             * Is there a group name match?
             * Match the attribute name to the specified name in the plugin settings
             */
            if (in_array($attribute->getName(), $groupNames)) {
                /**
                 * Loop thru all of the attributes values because they could have multiple values.
                 * Example XML:
                 * <saml2:Attribute Name="groups" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
                 *   <saml2:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema"
                 *           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">
                 *           craft_admin
                 *           </saml2:AttributeValue>
                 *   <saml2:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema"
                 *           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">
                 *           craft_member
                 *           </saml2:AttributeValue>
                 * </saml2:Attribute>
                 */
                foreach ($attribute->getAllAttributeValues() as $value) {
                    if ($group = $this->findOrCreateUserGroup($value)) {
                        Saml::debug(
                            sprintf(
                                'Assigning group: %s',
                                $group->name
                            )
                        );
                        $groups[] = $group->id;
                    }
                }
            }
        }
        /**
         * just return if this is empty
         */
        if (empty($groups)) {
            return true;
        }

        return \Craft::$app->getUsers()->assignUserToGroups($user->id, $groups);
    }

    /**
     * @param SamlResponse $response
     * @param User $user
     * @return User
     */
    protected function transformToUser(
        SamlResponse $response,
        User $user
    ) {

        $assertion = $response->getFirstAssertion();

        /**
         * Check the provider first
         */
        $attributeMap = Provider::providerMappingToKeyValue(
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
                $this->assignUserProperty(
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
    protected function assignUserProperty(
        User $user,
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

    /**
     * @param string $groupName
     * @return UserGroup
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function findOrCreateUserGroup($groupName): UserGroup
    {

        $groupHandle = StringHelper::camelCase($groupName);

        if (! $userGroup = \Craft::$app->getUserGroups()->getGroupByHandle($groupHandle)) {
            if (! \Craft::$app->getUserGroups()->saveGroup(
                $userGroup = new UserGroup(
                    [
                        'name'   => $groupName,
                        'handle' => $groupHandle,
                    ]
                )
            )
            ) {
                throw new UserException("Error saving new group {$groupHandle}");
            }
        }

        return $userGroup;
    }

    /**
     * @param ProviderIdentityRecord $identity
     * @return bool
     * @throws UserException
     * @throws \Throwable
     */
    protected function loginUser(\flipbox\saml\sp\records\ProviderIdentityRecord $identity)
    {
        if ($identity->getUser()->getStatus() !== User::STATUS_ACTIVE) {
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
}
