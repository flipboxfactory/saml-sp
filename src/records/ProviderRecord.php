<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\sp\records;


use flipbox\ember\records\traits\StateAttribute;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\records\traits\ProviderFields;

class ProviderRecord extends AbstractProvider implements ProviderInterface
{

    use StateAttribute, ProviderFields;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_sp_providers';

}