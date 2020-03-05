<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use craft\base\Field;
use craft\elements\User as UserElement;
use craft\models\FieldLayout;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\helpers\ProviderHelper;
use flipbox\saml\sp\helpers\UserHelper;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;
use SAML2\Response as SamlResponse;
use yii\base\UserException;

/**
 * Class User
 * @package flipbox\saml\sp\services
 */
class User
{
    use AssertionTrait;
    /**
     * @var FieldLayout|null
     */
    private $fieldLayout;
    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @param SamlResponse $response
     * @return UserElement
     * @throws InvalidMessage
     * @throws UserException
     */
    public function getByResponse(SamlResponse $response)
    {

        $assertion = $this->getFirstAssertion($response);

        if (! $assertion->getNameId()) {
            throw new InvalidMessage('Name ID is missing.');
        }

        Saml::debug('NameId: ' . $assertion->getNameId()->getValue());
        /**
         * Get username from the NameID
         *
         * @todo Give an option to map another attribute value to $username (like email)
         */
        $username = $assertion->getNameId()->getValue();

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
    public function sync(UserElement $user, SamlResponse $response)
    {

        // enable and transform the user
        $this->construct($user, $response);


        // Save
        $this->save($user);


        // Sync groups depending on the plugin setting.
        Saml::getInstance()->getUserGroups()->sync($user, $response);


        // Sync defaults
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
                'User save failed: ' . \json_encode($user->getErrors())
            );
            throw new UserException("User save failed: " . \json_encode($user->getErrors()));
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
    protected function construct(UserElement $user, SamlResponse $response)
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

        foreach ($this->getAssertions($response) as $assertion) {
            $hasAttributes = count($assertion->getAttributes()) > 0;
            Saml::debug('assertion attributes: ' . \json_encode($assertion->getAttributes()));
            if ($hasAttributes) {
                $this->transform($response, $user);
            } else {
                /**
                 * There doesn't seem to be any attribute statements.
                 * Try and use username for the email and move on.
                 */
                Saml::warning(
                    'No attribute statements found! Trying to assign username as the email.'
                );
                $user->email = $user->email ?: $user->username;
            }
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

        foreach ($this->getAssertions($response) as $assertion) {
            /**
             * Check the provider first
             */
            $attributeMap = ProviderHelper::providerMappingToKeyValue(
                $idpProvider = Saml::getInstance()->getProvider()->findByEntityId(
                    MessageHelper::getIssuer($response->getIssuer())
                )->one()
            ) ?:
                Saml::getInstance()->getSettings()->responseAttributeMap;

            Saml::debug('Attribute Map: ' . json_encode($attributeMap));

            /**
             * Loop thru attributes and set to the user
             */
            foreach ($assertion->getAttributes() as $attributeName => $attributeValue) {
                Saml::debug('Attributes: ' . $attributeName . ' ' . json_encode($attributeValue));
                if (isset($attributeMap[$attributeName])) {
                    $craftProperty = $attributeMap[$attributeName];
                    $this->assignProperty(
                        $user,
                        $attributeName,
                        $attributeValue,
                        $craftProperty
                    );
                } else {
                    Saml::debug('No match for: ' . $attributeName);
                }
            }
        }

        return $user;
    }

    /**
     * @param UserElement $user
     * @param $attributeName
     * @param $attributeValue
     * @param $craftProperty
     */
    protected function assignProperty(
        UserElement $user,
        $attributeName,
        $attributeValue,
        $craftProperty
    ) {

        $originalValues = $attributeValue;
        if (is_array($attributeValue)) {
            $attributeValue = isset($attributeValue[0]) ? $attributeValue[0] : null;
        }

        if (is_string($craftProperty) && in_array($craftProperty, $user->attributes())) {
            Saml::debug(
                sprintf(
                    'Attribute %s is scalar and should set value "%s" to user->%s',
                    $attributeName,
                    $attributeValue,
                    $craftProperty
                )
            );

            $this->setSimpleProperty($user, $craftProperty, $attributeValue);
        } elseif (is_callable($craftProperty)) {
            Saml::debug(
                sprintf(
                    'Attribute %s is handled with a callable.',
                    $attributeName
                )
            );

            call_user_func($craftProperty, $user, [
                $attributeName => $originalValues,
            ]);
        }
    }

    /**
     * @param UserElement $user
     * @return Field|null
     */
    protected function getFieldLayoutField(UserElement $user, $fieldHandle)
    {
        if (! $this->fieldLayout) {
            $this->fieldLayout = $user->getFieldLayout();
        }
        if (is_null($this->fieldLayout)) {
            return null;
        }

        if (! isset($this->fields[$fieldHandle])) {
            $this->fields[$fieldHandle] = $this->fieldLayout->getFieldByHandle($fieldHandle);
        }


        return $this->fields[$fieldHandle];
    }

    /**
     * @param UserElement $user
     * @param string $name
     * @param mixed $value
     */
    private function setSimpleProperty(UserElement $user, $name, $value)
    {
        $field = $this->getFieldLayoutField($user, $name);

        Saml::info(
            sprintf(
                '%s as %s. Is Field? %s',
                $name,
                $value,
                $field instanceof Field ? $field->id : 'Nope'
            )
        );

        if (! is_null($field)) {
            $user->setFieldValue($name, $value);
        } else {
            $user->{$name} = $value;
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
                    'username' => $username,
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
                    ['email' => $usernameOrEmail],
                ]
            )
            ->addSelect(['users.password', 'users.passwordResetRequired'])
            ->status(null)
            ->archived($archived)
            ->one();
    }
}
