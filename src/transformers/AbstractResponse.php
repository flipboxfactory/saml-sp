<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 2/21/18
 * Time: 9:22 PM
 */

namespace flipbox\saml\sp\transformers;

use craft\elements\User;
use Flipbox\Transform\Scope;
use Flipbox\Transform\Transformers\AbstractTransformer;
use LightSaml\Model\Protocol\Response;

/**
 * Class AbstractResponse
 * @package flipbox\saml\sp\transformers
 */
abstract class AbstractResponse extends AbstractTransformer
{

    protected $user;

    public function __construct(User $user, array $config = [])
    {
        parent::__construct($config);
        $this->user = $user;
    }

    abstract public function transform(Response $response, User $user);

    public function __invoke($data, Scope $scope, string $identifier = null)
    {
        return $this->transform(
            $data,
            $this->user
        );
    }

}