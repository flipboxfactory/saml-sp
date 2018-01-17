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
use flipbox\saml\sp\events\RegisterAttributesTransformer;
use flipbox\saml\sp\exceptions\InvalidMessage;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\traits\Security;
use flipbox\saml\sp\transformers\AbstractResponseToUser;
use Flipbox\Transform\Factory;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response as SamlResponse;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use yii\base\UserException;

class Login extends Component
{
    use Security;

    const EVENT_ATTRIBUTE_TRANSFORMER = 'attributeTransformer';

    public function getKey(): XMLSecurityKey
    {
        return Saml::getInstance()->getSettings()->getKey();
    }

    public function getCertificate(): X509Certificate
    {
        return Saml::getInstance()->getSettings()->getCertificate();
    }


    /**
     * @param SamlResponse $response
     * @return \flipbox\saml\sp\models\ProviderIdentity
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

    /**
     * @param SamlResponse $response
     * @return Assertion
     * @throws InvalidMessage
     */
    public function getFirstAssertion(\LightSaml\Model\Protocol\Response $response)
    {

        if (Saml::getInstance()->getSettings()->encryptAssertions) {
            $assertions = $this->decryptAssertions($response);
        } else {
            $assertions = $response->getAllAssertions();
        }

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
     * @return \flipbox\saml\sp\models\ProviderIdentity
     * @throws UserException
     */
    protected function syncUser(\LightSaml\Model\Protocol\Response $response)
    {

        $assertion = $this->getFirstAssertion($response);

        /**
         * Get username from the NameID
         * @todo Give an option to map another attribute value to $username (like email)
         */
        $providerIdentity = $username = $assertion->getSubject()->getNameID()->getValue();

        /** @var \flipbox\saml\sp\models\ProviderIdentity $identity */
        if (! $identity = Saml::getInstance()->getProviderIdentity()->findByString($providerIdentity)) {
            if (! Saml::getInstance()->getSettings()->createUser) {
                throw new UserException("System doesn't have permission to create a new user.");
            }
        }

        /** @var \flipbox\saml\sp\models\Provider $provider */
        $provider = Saml::getInstance()->getProvider()->findByIssuer(
            $response->getIssuer()
        );

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

        $event = new RegisterAttributesTransformer();
        $this->trigger(static::EVENT_ATTRIBUTE_TRANSFORMER, $event);
        /** @var AbstractResponseToUser $transformer */
        $transformer = $event->getTransformer($provider->getEntityId());
        if ($transformer instanceof AbstractResponseToUser) {
            Factory::item(new $transformer($user), $response);
        } else {
            $this->setAttributesFromAssertion($user, $assertion);
        }

        if (! \Craft::$app->getElements()->saveElement($user)) {
            throw new UserException("User save failed.");
        }

        if(! $identity) {
            $identity = new \flipbox\saml\sp\models\ProviderIdentity([
                'providerId'       => $provider->id,
                'providerIdentity' => $username,
                'user'             => $user,
                'enabled'          => true,
            ]);
        }

        return $identity;
    }

    /**
     * @param User $user
     * @param Assertion $assertion
     */
    protected function setAttributesFromAssertion(User $user, Assertion $assertion)
    {
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
    }

    /**
     * @param \flipbox\saml\sp\models\ProviderIdentity $identity
     * @return bool
     * @throws UserException
     */
    protected function loginUser(\flipbox\saml\sp\models\ProviderIdentity $identity)
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