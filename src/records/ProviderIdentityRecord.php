<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;

use craft\elements\User;
use flipbox\saml\core\records\AbstractProviderIdentity;

/**
 * Class ProviderIdentityRecord
 * @package flipbox\saml\sp\records
 * @property int $userId
 * @property bool $enabled
 * @property string $sessionId
 */
class ProviderIdentityRecord extends AbstractProviderIdentity
{

    const TABLE_ALIAS = 'saml_sp_provider_identity';

    /**
     * @inheritdoc
     */
    public function getProvider()
    {
        return $this->hasOne(
            ProviderRecord::class,
            [
                'id' => 'providerId',
            ]
        );
    }

}
