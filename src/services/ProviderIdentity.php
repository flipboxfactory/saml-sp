<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\sp\services;

use craft\elements\User;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\services\AbstractProviderIdentityService;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\login\AssertionTrait;
use flipbox\saml\sp\traits\SamlPluginEnsured;
use SAML2\Assertion;
use SAML2\Response as SamlResponse;
use yii\base\UserException;

/**
 * Class ProviderIdentity
 *
 * @package flipbox\saml\sp\services
 */
class ProviderIdentity extends AbstractProviderIdentityService
{

    use SamlPluginEnsured, AssertionTrait;

    /**
     * ACS Methods
     */

    /**
     * @param User $user
     * @param SamlResponse $response
     * @return ProviderIdentityRecord
     * @throws InvalidMessage
     * @throws UserException
     */
    public function getByUserAndResponse(
        User $user,
        SamlResponse $response,
        ProviderRecord $serviceProvider,
        ProviderRecord $idpProvider
    ) {

        $firstAssertion = $this->getFirstAssertion($response, $serviceProvider);

        // Get Identity
        $identity = $this->forceGet(
            $firstAssertion->getNameID()->getValue(),
            $idpProvider
        );

        // Get Session
        $sessionIndex = $firstAssertion->getSessionIndex();

        // Set Identity Properties
        $identity->userId = $user->id;
        $identity->enabled = true;
        $identity->sessionId = $sessionIndex;
        return $identity;
    }


    /**
     * @param string $nameId
     * @param ProviderInterface $provider
     * @return ProviderIdentityRecord
     * @throws UserException
     */
    protected function forceGet($nameId, ProviderInterface $provider)
    {
        // @var \flipbox\saml\sp\records\ProviderIdentityRecord $identity
        if (! $identity = $this->findByNameId(
            $nameId,
            $provider
        )->one()
        ) {

            /**
             * Create the new identity if one wasn't found above.
             * Since we now have the user id, and we might not have above,
             * do this last.
             */
            $identity = new ProviderIdentityRecord(
                [
                    'providerId' => $provider->id,
                    'nameId' => $nameId,
                ]
            );
        }

        return $identity;
    }
}
