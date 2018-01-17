<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 2:44 PM
 */

namespace flipbox\saml\sp\services;


use craft\base\Component;
use flipbox\saml\sp\exceptions\InvalidIssuer;
use flipbox\saml\sp\Saml;
use LightSaml\Credential\KeyHelper;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Protocol\SamlMessage;

abstract class AbstractHttpBinding extends Component
{}