<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:37 AM
 */

namespace flipbox\saml\sp\models;

use flipbox\saml\core\helpers\ClaimTypes;
use flipbox\saml\core\models\AbstractSettings;
use flipbox\saml\core\models\SettingsInterface;

class Settings extends AbstractSettings implements SettingsInterface
{
    /**
     * Craft will show the IDP login buttons on the /admin/login page.
     *
     * @var bool
     */
    public $enableCpLoginButtons = true;

    /**
     * Require the Response message to be signed. Messages that aren't signed can be manipulated.
     * RECOMMENDED TO BE SET TO TRUE
     *
     * @var bool
     */
    public $requireResponseToBeSigned = true;
    /**
     * Require the Assertion (inside the Response message) to be signed. Messages that aren't signed can be manipulated.
     * RECOMMENDED TO BE SET TO TRUE
     *
     * @var bool
     */
    public $requireAssertionToBeSigned = true;

    /**
     * When a user logs into the IDP but is not enabled in Craft, the user will
     * be enabled. The IDP should be the authority on whether the user is active
     * or not. Users should be disabled from the IDP if they shouldn't be
     * enabled. If this setting is false, a user exception will be thrown.
     *
     * @var bool
     */
    public $enableUsers = true;

    /**
     *
     * @var bool
     */
    public $signAuthnRequest = true;

    /**
     *
     * @var bool
     */
    public $wantsSignedAssertions = true;

    /**
     * When true, if the user is not found in Craft (after the user logs in successfully), the user will be created.
     * If this is false, a user exception will be thrown.
     *
     * @var bool
     * @deprecated
     */
    public $mergeLocalUsers = true;

    /**
     * When a user logs in successfully but is not found in Craft, that user
     * will be created. If this is false, a user exception will be thrown.
     *
     * @var bool
     */
    public $createUser = true;

    /**
     * Override the use of the NameID to set and looking-up the username. Set this to the name of the assertion
     * attribute you would like.
     *
     * When this is null, the NameID will be used.
     *
     * For example, if your IdP sends the NameID as a uuid and you don't want that as username,
     * set `nameIdAttributeOverride` to another assertion attribute name coming from the IdP (ie,
     * `'nameIdAttributeOverride' => 'Email',` or `'nameIdAttributeOverride' => ClaimTypes::EMAIL_ADDRESS,`).
     *
     * @var null|string
     */
    public $nameIdAttributeOverride;

    /**
     * When a group is found that does not exist in Craft, it will be created.
     *
     * Deprecated due to Craft CMS 3.5 making the project config more prevalent. Since these changes won't be
     * protected from being overwritten on production, we need to remove this feature.
     *
     * @var bool
     * @deprecated
     */
    public $autoCreateGroups = true;

    /**
     * Attempt to sync groups via the groups array map in $groupAttributeNames.
     *
     * @var bool
     */
    public $syncGroups = true;

    /**
     * When true, the plugin will merge the existing groups the user has assigned with the
     * groups found in the SSO/SAML Response.
     * If you want groups to be strictly managed by the plugin, toggle this to false.
     * @var bool
     */
    public $mergeExistingGroups = true;

    /**
     * A list of strings that will be used to look through the attributes xml
     * nodes sent by the IDP to be identified as a group. This is a key to the
     * group name values.
     *
     * @var array
     */
    public $groupAttributeNames = [
        'groups',
    ];

    /**
     * An array of user group ids. Users will automatically be assigned to these group
     * ids.
     *
     * @var array
     */
    public $defaultGroupAssignments = [];

    /**
     * An array map with the Response attribute names as the array keys and the
     * array values as the user element field. The array value can also be a callable.
     *
     * Simple mapping works by matching the Response name in the array with the user's
     * property, and setting what is found in the Response's value to the user element.
     *
     * Take the following example:
     * Here is my responseAttributeMap from the config/saml-sp.php
     * ```php
     * ...
     * 'responseAttributeMap' => [
     *      ClaimTypes::EMAIL_ADDRESS => 'email',
     *      'firstName' => 'firstName',
     * ],
     * ...
     * ```
     *
     * Here is a snippet from the Saml XML Response message:
     * ```xml
     * <Attribute Name="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress">
     *    <AttributeValue>damien@example.com</AttributeValue>
     * </Attribute>
     * <Attribute Name="firstName">
     *    <AttributeValue>Damien</AttributeValue>
     * </Attribute>
     * ```
     *
     * The above would result in the following mapping:
     * ```php
     * // @var $user \craft\elements\User
     * $user->firstName = 'Damien';
     * $user->email = 'damien@example.com';
     * ```
     *
     * With more complex user fields, you can set the array value to a callable.
     * For more on callables: http://php.net/manual/en/language.types.callable.php.
     *
     * Here is my responseAttributeMap with a callable from the config/saml-sp.php
     * ```php
     * 'responseAttributeMap' => [
     *     ClaimTypes::EMAIL_ADDRESS => function(
     *         \craft\elements\User $user,
     *         array $attribute
     *     ) {
     *
     *         // $attribute is key/name value (string/array) pair
     *
     *         // Could be an array
     *         $attributeValue = $attribute['Email'];
     *         if(is_array($attributeValue)){
     *             $attributeValue = $attributeValue[0];
     *         }
     *
     *         $user->email = $attribute['Email'];
     *     }
     * ],
     * ```
     *
     * @var array
     * @deprecated
     */
    public $responseAttributeMap = [
        // "IDP Attribute Name" => "Craft Property Name"
        ClaimTypes::EMAIL_ADDRESS => 'email',
        ClaimTypes::GIVEN_NAME => 'firstName',
        ClaimTypes::SURNAME => 'lastName',

        'email' => 'email',
        'firstName' => 'firstName',
        'lastName' => 'lastName',
    ];

    /**
     *
     * @var string
     */
    public $relayStateOverrideParam = 'RelayState';

    /**
     * Whether to base64_encode the relay state or not
     * @var bool
     */
    public $encodeRelayState = true;
}
