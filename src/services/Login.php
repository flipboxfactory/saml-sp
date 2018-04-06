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
use craft\models\UserGroup;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response as SamlResponse;
use yii\base\Event;
use yii\base\UserException;

class Login extends Component
{

    const EVENT_RESPONSE_TO_USER = 'eventResponseToUser';

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
         * Sync User
         */
        $identity = $this->syncUser($response);

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

        if ($ownProvider->keychain && $response->getFirstEncryptedAssertion()) {
            $this->decryptAssertions(
                $ownProvider->keychain,
                $response
            );
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
     * @return \flipbox\saml\sp\records\ProviderIdentityRecord
     * @throws InvalidMessage
     * @throws UserException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function syncUser(\LightSaml\Model\Protocol\Response $response)
    {

        $assertion = $this->getFirstAssertion($response);

        /**
         * Get username from the NameID
         * @todo Give an option to map another attribute value to $username (like email)
         */
        $nameId = $username = $assertion->getSubject()->getNameID()->getValue();

        $idpProvider = Saml::getInstance()->getProvider()->findByEntityId(
            $response->getIssuer()->getValue()
        );

        /** @var \flipbox\saml\sp\records\ProviderIdentityRecord $identity */
        if (! $identity = Saml::getInstance()->getProviderIdentity()->findByNameId(
            $nameId,
            $idpProvider
        )) {
            if (! Saml::getInstance()->getSettings()->createUser) {
                throw new UserException("System doesn't have permission to create a new user.");
            }
        }

        /**
         * Is there a user that exists already?
         */
        if ($user = \Craft::$app->getUsers()->getUserByUsernameOrEmail($username)) {
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
            $user = new User([
                'username' => $username
            ]);
        }

        $this->transformToUser($response, $user);

        /**
         * After event for Metadata creation
         */
        $event = new Event();
        $event->sender = $response;
        $event->data = $user;

        $this->trigger(
            static::EVENT_RESPONSE_TO_USER,
            $event
        );

        if (! \Craft::$app->getElements()->saveElement($user)) {
            throw new UserException("User save failed.");
        }

        /**
         * Sync groups depending on the plugin setting.
         */
        if (Saml::getInstance()->getSettings()->syncGroups) {
            $this->syncUserGroupsByAssertion($user, $assertion);
        }

        $sessionIndex = null;
        if ($assertion->hasAnySessionIndex()) {
            $sessionIndex = $assertion->getFirstAuthnStatement()->getSessionIndex();
        }

        /**
         * Create the new identity if one wasn't found above.
         * Since we now have the user id, and we might not have above,
         * do this last.
         */
        if (! $identity) {
            $identity = new ProviderIdentityRecord([
                'providerId' => $idpProvider->id,
                'nameId'     => $username,
                'userId'     => $user->id,
            ]);
        }

        $identity->enabled = true;
        $identity->sessionId = $sessionIndex;
        return $identity;
    }

    /**
     * @param User $user
     * @param Assertion $assertion
     * @return bool
     * @throws UserException
     */
    protected function syncUserGroupsByAssertion(User $user, Assertion $assertion)
    {
        $groupNames = Saml::getInstance()->getSettings()->groupAttributeNames;
        $groups = [];
        foreach ($assertion->getFirstAttributeStatement()->getAllAttributes() as $attribute) {
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
                 *           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">craft_admin</saml2:AttributeValue>
                 *   <saml2:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema"
                 *           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">craft_member</saml2:AttributeValue>
                 * </saml2:Attribute>
                 */

                foreach ($attribute->getAllAttributeValues() as $value) {
                    if ($group = $this->findOrCreateUserGroup($value)) {
                        $groups[] = $group->id;
                    }
                }

            }
        }

        return \Craft::$app->getUsers()->assignUserToGroups($user->getId(), $groups);

    }


    /**
     * @param SamlResponse $response
     * @param User $user
     * @return User
     */
    protected function transformToUser(\LightSaml\Model\Protocol\Response $response, User $user)
    {
        $assertion = $response->getFirstAssertion();

        $attributeMap = Saml::getInstance()->getSettings()->responseAttributeMap;
        /**
         * Loop thru attributes and set to the user
         */
        foreach ($assertion->getFirstAttributeStatement()->getAllAttributes() as $attribute) {
            if (isset($attributeMap[$attribute->getName()])) {
                $craftProperty = $attributeMap[$attribute->getName()];

                //check if it exists as a property first
                if (property_exists($user, $craftProperty)) {
                    $user->{$craftProperty} = $attribute->getFirstAttributeValue();
                } else {
                    if (is_callable($craftProperty)) {
                        call_user_func($craftProperty, $user, $attribute);
                    }
                }
            }
        }

        return $user;

    }

    /**
     * @param $groupHandle
     * @return UserGroup
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function findOrCreateUserGroup($groupHandle): UserGroup
    {

        if (! $userGroup = \Craft::$app->getUserGroups()->getGroupByHandle($groupHandle)) {
            if (! \Craft::$app->getUserGroups()->saveGroup($userGroup = new UserGroup([
                'name'   => $groupHandle,
                'handle' => $groupHandle,
            ]))) {
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
            /** @todo read session duration from the response */
            \Craft::$app->getConfig()->getGeneral()->userSessionDuration
        )) {
            $identity->lastLoginDate = new \DateTime();
        } else {
            throw new UserException("User login failed.");
        }

        return true;
    }

}