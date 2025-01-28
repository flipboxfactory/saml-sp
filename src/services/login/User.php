<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use craft\base\Component;
use craft\base\Field;
use craft\elements\User as UserElement;
use craft\errors\InvalidElementException;
use craft\events\ElementEvent;
use craft\models\FieldLayout;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\ProviderHelper;
use flipbox\saml\sp\events\UserQueryCriteria;
use flipbox\saml\sp\helpers\UserHelper;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\Response as SamlResponse;
use yii\base\Event;
use yii\base\UserException;

/**
 * Class User
 * @package flipbox\saml\sp\services
 */
class User extends Component
{
    use AssertionTrait;

    public const EVENT_BEFORE_USER_SAVE = 'eventBeforeUserSave';

    public const EVENT_GET_CUSTOM_USER_CRITERIA = 'eventGetCustomUserCriteria';

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
    public function getByResponse(
        SamlResponse $response,
        ProviderRecord $serviceProvider,
        ProviderRecord $identityProvider,
        Settings $settings,
    ) {
        $username = null;

        $nameIdOverride = $settings->nameIdAttributeOverride ?? $identityProvider->nameIdOverride;

        if ($nameIdOverride) {
            // use override
            foreach ($this->getAssertions(
                $response,
                $serviceProvider
            ) as $assertion) {
                $attributes = $assertion->getAttributes();
                if (isset($attributes[$nameIdOverride])) {
                    $attributeValue = $attributes[$nameIdOverride];
                    $username = $this->getAttributeValue($attributeValue);
                }
            }
        } else {
            // use nameid
            $assertion = $this->getFirstAssertion($response, $serviceProvider);

            if (!$assertion->getNameId()) {
                throw new InvalidMessage('Name ID is missing.');
            }
            $username = $assertion->getNameId()->getValue();

            Saml::debug('NameId: ' . $assertion->getNameId()->getValue());
        }

        return $this->find($username);
    }

    /**
     * @throws \Throwable
     * @throws InvalidElementException
     */
    private function enableUser(UserElement $user): void
    {
        if ($user->getId()) {
            \Craft::$app->getUsers()->activateUser($user);

            return;
        }

        $user->enabled = true;
        $user->archived = false;

        $user->active = true;
        $user->pending = false;
        $user->locked = false;
        $user->suspended = false;
        $user->verificationCode = null;
        $user->verificationCodeIssuedDate = null;
        $user->invalidLoginCount = null;
        $user->lastInvalidLoginDate = null;
        $user->lockoutDate = null;
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
            $this->enableUser($identity->getUser());
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
     * @param ProviderRecord $idp
     * @param Settings $settings
     * @throws UserException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function sync(
        UserElement $user,
        SamlResponse $response,
        ProviderRecord $idp,
        ProviderRecord $sp,
        Settings $settings,
    ) {

        // enable and transform the user
        $this->construct(
            $user,
            $response,
            $idp,
            $sp,
            $settings
        );

        // Save
        $this->save($user);
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
        $event = new ElementEvent();
        $event->element = $user;
        $event->isNew = !$user->id;

        $this->trigger(
            static::EVENT_BEFORE_USER_SAVE,
            $event
        );

        if (!\Craft::$app->getElements()->saveElement($user)) {
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
     * @param ProviderRecord $idp
     * @param Settings $settings
     * @throws UserException
     * @throws \Throwable
     */
    protected function construct(
        UserElement $user,
        SamlResponse $response,
        ProviderRecord $idp,
        ProviderRecord $sp,
        Settings $settings,
    ) {
        /**
         * Is User Active?
         */
        if ($user->id && !UserHelper::isUserActive($user)) {
            if (!$settings->enableUsers) {
                throw new UserException('User access denied.');
            }
            UserHelper::enableUser($user);
        }

        foreach ($this->getAssertions($response, $sp) as $assertion) {
            $hasAttributes = count($assertion->getAttributes()) > 0;
            Saml::debug('assertion attributes: ' . \json_encode($assertion->getAttributes()));
            if ($hasAttributes) {
                $this->transform(
                    $user,
                    $response,
                    $idp,
                    $sp,
                    $settings
                );
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
     * @param UserElement $user
     * @param SamlResponse $response
     * @return UserElement
     */
    protected function transform(
        UserElement $user,
        SamlResponse $response,
        ProviderRecord $idp,
        ProviderRecord $sp,
        Settings $settings,
    ) {
        foreach ($this->getAssertions($response, $sp) as $assertion) {
            /**
             * Check the provider first
             */
            $attributeMap = ProviderHelper::providerMappingToKeyValue(
                $idp
            ) ?:
                $settings->responseAttributeMap;

            Saml::debug('Attribute Map: ' . \json_encode($attributeMap));

            /**
             * Loop thru attributes and set to the user
             */
            foreach ($assertion->getAttributes() as $attributeName => $attributeValue) {
                Saml::debug('Attributes: ' . $attributeName . ' ' . \json_encode($attributeValue));
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
        $craftProperty,
    ) {
        $originalValues = $attributeValue;
        if (is_array($attributeValue)) {
            $attributeValue = isset($attributeValue[0]) ? $attributeValue[0] : null;
        }

        if (is_string($craftProperty)) {
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
        if (!$this->fieldLayout) {
            $this->fieldLayout = $user->getFieldLayout();
        }
        if (is_null($this->fieldLayout)) {
            return null;
        }

        if (!isset($this->fields[$fieldHandle])) {
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

        if (!is_null($field)) {
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
        if (!($user = $this->getByUsernameOrEmail($username))) {
            // Should we create a new user? what's the setting say?
            if (!Saml::getInstance()->getSettings()->createUser) {
                throw new UserException("System doesn't have permission to create a new user.");
            }

            // new user!
            $user = new UserElement(
                [
                    'username' => $username,
                ]
            );
        }
        return $user;
    }

    /**
     * @param $usernameOrEmail
     * @param bool $archived
     * @return array|bool|\craft\base\ElementInterface|UserElement|null
     */
    protected function getByUsernameOrEmail($usernameOrEmail, $archived = false)
    {
        $event = new UserQueryCriteria([
            'userQuery' => UserElement::find(),
            'usernameOrEmail' => $usernameOrEmail,
            'archived' => $archived,
        ]);

        if (Event::hasHandlers(self::class, self::EVENT_GET_CUSTOM_USER_CRITERIA)) {
            Event::trigger(
                self::class,
                self::EVENT_GET_CUSTOM_USER_CRITERIA,
                $event
            );
        }

        return ($event->applyDefaultCriteria ? $event->userQuery
            ->where(
                [
                    'or',
                    ['username' => $event->usernameOrEmail],
                    ['email' => $event->usernameOrEmail],
                ]
            )
            ->status(null)
            ->archived($event->archived) : $event->userQuery)->one();
    }

    private function getAttributeValue($attributeValue)
    {
        if (is_array($attributeValue)) {
            $attributeValue = isset($attributeValue[0]) ? $attributeValue[0] : null;
        }

        return $attributeValue;
    }
}
