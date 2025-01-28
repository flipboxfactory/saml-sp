<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use craft\base\Component;
use craft\elements\User as UserElement;
use craft\helpers\StringHelper;
use craft\models\UserGroup;
use flipbox\saml\sp\events\UserGroupAssign;
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
class UserGroups extends Component
{
    use AssertionTrait;
    public const EVENT_BEFORE_USER_GROUP_ASSIGN = 'eventBeforeUserGroupAssign';

    /**
     * @param string $groupName
     * @return UserGroup|null
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function find($groupName)
    {
        $groupHandle = StringHelper::camelCase($groupName);
        Saml::debug("GROUP LOOKUP ${groupName}");

        if (!$userGroup = \Craft::$app->getUserGroups()->getGroupByHandle($groupHandle)) {
            Saml::warning(
                sprintf(
                    "Group handle %s not found." .
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
        $groups = [];
        foreach ($this->getAssertions($response, $serviceProvider) as $assertion) {
            // add all of the groups from assertions to the groups array
            $groups = array_merge(
                $groups,
                $this->getGroupsByAssertion(
                    $assertion,
                    $settings
                )
            );
        }

        // sync the groups
        $this->syncGroups(
            $user,
            $response,
            $groups,
            $settings
        );

        $this->assignDefaultGroups(
            $user,
            $settings
        );

        return true;
    }

    /**
     * @param Assertion $assertion
     * @param Settings $settings
     * @return array
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function getGroupsByAssertion(
        Assertion $assertion,
        Settings $settings,
    ) {
        /**
         * Nothing to do, move on
         */
        if (false === $settings->syncGroups) {
            return [];
        }

        $groupNames = $settings->groupAttributeNames;
        $groups = [];
        /**
         * Make sure there is an attribute statement
         */
        if (!$assertion->getAttributes()) {
            Saml::debug(
                'No attribute statement found, moving on.'
            );
            return [];
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
                if (!is_array($attributeValue)) {
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
                        $groups[] = $group;
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
            return [];
        }
        return $groups;
    }

    /**
     * @param UserElement $user
     * @param Response $response
     * @param UserGroup[] $groups
     * @param Settings $settings
     * @throws \Throwable
     */
    protected function syncGroups(
        UserElement $user,
        Response $response,
        array $groups,
        Settings $settings,
    ) {
        $event = new UserGroupAssign();
        $event->user = $user;
        $event->response = $response;
        $event->existingGroups = $user->getGroups();
        $event->groupsFoundInAssertions = $groups;
        $event->groupToBeAssigned = array_merge(
            $settings->mergeExistingGroups ? $user->getGroups() : [],
            $groups
        );

        $this->trigger(
            static::EVENT_BEFORE_USER_GROUP_ASSIGN,
            $event
        );

        if (\Craft::$app->getUsers()->assignUserToGroups(
            $user->id,
            // pass the list of unique ids
            array_unique(
                array_map(
                    function($group) {
                        return (int)$group->id;
                    },
                    $event->groupToBeAssigned
                )
            )
        )) {
            $user->setGroups(
                $event->groupToBeAssigned
            );
        }
        return;
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
        if (!empty($newGroups)) {
            $groupIds = array_map(
                function($group) {
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
