<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\sp\services\login;

use craft\elements\User as UserElement;
use craft\helpers\StringHelper;
use craft\models\UserGroup;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Assertion;
use yii\base\UserException;

/**
 * Class UserGroups
 * @package flipbox\saml\sp\services
 */
class UserGroups
{
    /**
     * @param string $groupName
     * @return UserGroup
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    protected function findOrCreate($groupName): UserGroup
    {

        $groupHandle = StringHelper::camelCase($groupName);

        if (! $userGroup = \Craft::$app->getUserGroups()->getGroupByHandle($groupHandle)) {
            if (! \Craft::$app->getUserGroups()->saveGroup(
                $userGroup = new UserGroup(
                    [
                        'name'   => $groupName,
                        'handle' => $groupHandle,
                    ]
                )
            )
            ) {
                throw new UserException("Error saving new group {$groupHandle}");
            }
        }

        return $userGroup;
    }


    /**
     * @param UserElement $user
     * @param Assertion $assertion
     * @return bool
     * @throws UserException
     * @throws \craft\errors\WrongEditionException
     */
    public function syncByAssertion(UserElement $user, Assertion $assertion)
    {
        /**
         * Nothing to do, move on
         */
        if (false === Saml::getInstance()->getSettings()->syncGroups) {
            return true;
        }

        $groupNames = Saml::getInstance()->getSettings()->groupAttributeNames;
        $groups = [];
        /**
         * Make sure there is an attribute statement
         */
        if (! $assertion->getFirstAttributeStatement()) {
            Saml::debug(
                'No attribute statement found, moving on.'
            );
            return true;
        }

        foreach ($assertion->getFirstAttributeStatement()->getAllAttributes() as $attribute) {
            Saml::debug(
                sprintf(
                    'Is attribute group? "%s" in %s',
                    $attribute->getName(),
                    json_encode($groupNames)
                )
            );
            /**
             * Is there a group name match?
             * Match the attribute name to the specified name in the plugin settings
             */
            if (in_array($attribute->getName(), $groupNames)) {
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
                foreach ($attribute->getAllAttributeValues() as $value) {
                    if ($group = $this->findOrCreate($value)) {
                        Saml::debug(
                            sprintf(
                                'Assigning group: %s',
                                $group->name
                            )
                        );
                        $groups[] = $group->id;
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
    public function assignDefaultGroups(\craft\elements\User $user)
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
    public function getDefaultGroups()
    {
        $groups = [];
        foreach (Saml::getInstance()->getSettings()->defaultGroupAssignments as $groupId) {
            $groups[$groupId] = \Craft::$app->getUserGroups()->getGroupById($groupId);
        }

        return $groups;
    }
}
