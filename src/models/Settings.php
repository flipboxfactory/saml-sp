<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:37 AM
 */

namespace flipbox\saml\sp\models;


use craft\base\Model;
use craft\helpers\UrlHelper;

class Settings extends Model
{
    /**
     * File system path to the rsa key used to sign and encrypt assertions
     * @var string
     */
    public $keyPath;
    /**
     * File system path to the cert used to sign and encrypt assertions
     * @var string
     */
    public $certPath;
    /**
     * @var bool
     */
    public $encryptAssertions = false;
    /**
     * @var bool
     */
    public $signAssertions = true;

    public $httpBinding = true;
    public $postBinding = true;

    protected $entityId;

    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getEntityId()
    {
        if(!$this->entityId)
        {
            $this->entityId = UrlHelper::baseUrl();
        }


        return $this->entityId;
    }

}