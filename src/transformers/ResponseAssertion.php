<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 2/21/18
 * Time: 9:22 PM
 */

namespace flipbox\saml\sp\transformers;

use craft\elements\User;
use flipbox\saml\sp\Saml;
use Flipbox\Transform\Scope;
use Flipbox\Transform\Transformers\AbstractTransformer;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response;

class ResponseAssertion extends AbstractTransformer
{

    protected $user;

    public function __construct(User $user, array $config = [])
    {
        parent::__construct($config);
        $this->user = $user;
    }

    public function transform(Response $response, User $user)
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

    public function __invoke($data, Scope $scope, string $identifier = null)
    {
        return $this->transform(
            $data,
            $this->user
        );
    }

}