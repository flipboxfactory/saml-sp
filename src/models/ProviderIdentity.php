<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:30 PM
 */

namespace flipbox\saml\sp\models;


use craft\elements\User;
use flipbox\ember\models\ModelWithId;
use flipbox\saml\sp\Saml;

/**
 * Class ProviderIdentity
 * @package flipbox\saml\sp\models
 */
class ProviderIdentity extends ModelWithId
{
    public $id;
    protected $provider;
    protected $providerId;
    protected $userId;
    protected $user;
    public $providerIdentity;
    public $enabled;
    public $lastLoginDate;


    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setProviderId($id)
    {
        if (! $this->provider) {
            $this->setProvider($this->getProviderById($id));
        }

        $this->providerId = $id;
        return $this;
    }

    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    public function getProvider()
    {
        if (! $this->provider && $this->getProviderId()) {
            $this->setProvider($this->getProviderById($this->getProviderId()));
        }
        return $this->provider;
    }

    /**
     * @param int $providerId
     * @return Provider
     */
    public function getProviderById(int $providerId)
    {

        return Saml::getInstance()->getProvider()->get($providerId);
    }

    /**
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setUserId(int $id)
    {
        if (! $this->user) {
            $this->setUser(\Craft::$app->getUsers()->getUserById($id));
        }
        $this->userId = $id;
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        if (! $this->getId() && $user->getId()) {
            $this->setUserId($user->getId());
        }

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (! $this->user && $this->getUserId()) {
            $this->setUser(
                \Craft::$app->getUsers()->getUserById($this->getUserId())
            );
        }

        return $this->user;
    }
}