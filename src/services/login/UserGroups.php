<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use craft\elements\User as UserElement;
use craft\helpers\StringHelper;
use craft\models\UserGroup;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\Assertion;
use SAML2\Response;
use yii\base\UserException;

/**
 * Class UserGroups
 * @package flipbox\saml\sp\services
 */
class UserGroups
{
    use AssertionTrait;

    /**
     * @param string $groupName
     * @return UserGroup|null
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function find($groupName)
    {

        $groupHandle = StringHelper::camelCase($groupName);

        if (! $userGroup = \Craft::$app->getUserGroups()->getGroupByHandle($groupHandle)) {
            Saml::warning(
                sprintf(
                    "Group handle %s not found.".
                    " This group must be created by an admin users before user can be assigned to it.",
                    $groupHandle
                )
            );
        }

        return $userGroup;
    }

    /**
     * @param UserElement $user
     * @param Response $response
     * @return bool
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    public function sync(UserElement $user, Response $response, ProviderRecord $serviceProvider, Settings $settings)
    {
        foreach ($this->getAssertions($response, $serviceProvider) as $assertion) {
            $this->syncByAssertion(
                $user,
                $assertion,
                $settings
            );
        }

        $this->assignDefaultGroups(
            $user,
            $settings
        );

        return true;
    }

    /**
     * @param UserElement $user
     * @param Assertion $assertion
     * @return bool
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function syncByAssertion(
        UserElement $user,
        Assertion $assertion,
        Settings $settings
    ) {
        /**
         * Nothing to do, move on
         */
        if (false === $settings->syncGroups) {
            return true;
        }

        $groupNames = $settings->groupAttributeNames;
        $groups = [];
        /**
         * Make sure there is an attribute statement
         */
        if (! $assertion->getAttributes()) {
            Saml::debug(
                'No attribute statement found, moving on.'
            );
            return true;
        }

        foreach ($assertion->getAttributes() as $attributeName => $attributeValue) {
            Saml::debug(
                sprintf(
                    'Is attribute group? "%s" in %s',
                    $attributeName,
                    json_encode($groupNames)
                )
            );
            /**
             * Is there a group name match?
             * Match the attribute name to the specified name in the plugin settings
             */
            if (in_array($attributeName, $groupNames)) {
                /**
                 * Loop thru all of the attributes values because they could have multiple values.
                 * Example XML:
                 * <saml2:Attribute Name="groups" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
                 *   <saml2:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema"
                 *           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">
                 *           craft_admin
                 *           </saml2:AttributeValue>
                 *   <saml2:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema"
                 *           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">
                 *           craft_member
                 *           </saml2:AttributeValue>
                 * </saml2:Attribute>
                 */
                if (! is_array($attributeValue)) {
                    $attributeValue = [$attributeValue];
                }

                foreach ($attributeValue as $groupName) {
                    if ($group = $this->find($groupName)) {
                        Saml::debug(
                            sprintf(
                                'Assigning group: %s',
                                $group->name
                            )
                        );
                        $groups[] = $group->id;
                    } else {
                        Saml::debug(
                            sprintf(
                                'Group not found: %s',
                                $groupName
                            )
                        );
                    }
                }
            }
        }
        /**
         * just return if this is empty
         */
        if (empty($groups)) {
            return true;
        }

        /**
         * Get existing groups
         */
        $existingGroupIds = array_map(
            function ($group) {
                return (int)$group->id;
            },
            $user->getGroups()
        );

        return \Craft::$app->getUsers()->assignUserToGroups(
            $user->id,
            array_unique(
                array_merge(
                    $existingGroupIds,
                    $groups
                )
            )
        );
    }

    /**
     * @param UserElement $user
     * @return bool|null
     */
    protected function assignDefaultGroups(\craft\elements\User $user, Settings $settings)
    {
        $groups = array_merge(
            $user->getGroups(),
            $newGroups = $this->getDefaultGroups()
        );

        /**
         * if it's not empty add the groups
         */
        if (! empty($newGroups)) {
            $groupIds = array_map(
                function ($group) {
                    return (int)$group->id;
                },
                $groups
            );

            if (\Craft::$app->getUsers()->assignUserToGroups($user->id, array_unique($groupIds))) {
                $user->setGroups($groups);
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getDefaultGroups()
    {
        $groups = [];
        foreach (Saml::getInstance()->getSettings()->defaultGroupAssignments as $groupId) {
            $groups[$groupId] = \Craft::$app->getUserGroups()->getGroupById($groupId);
        }

        return $groups;
    }
}
