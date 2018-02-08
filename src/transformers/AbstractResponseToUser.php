<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/16/18
 * Time: 9:39 PM
 */

namespace flipbox\saml\core\transformers;


use craft\elements\User;
use Flipbox\Transform\Scope;
use Flipbox\Transform\Transformers\AbstractTransformer;
use LightSaml\Model\Protocol\Response;

abstract class AbstractResponseToUser extends AbstractTransformer
{
    /**
     * @var $user User
     */
    protected $user;
    public function __construct(User $user, array $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    abstract public function transform(Response $response, User $user, Scope $scope, $identifier): User;

    public function __invoke($data, Scope $scope, string $identifier = null)
    {
        return $this->transform(
            $data,
            $this->user,
            $scope,
            $identifier
        );
    }
}