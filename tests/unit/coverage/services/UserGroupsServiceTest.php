<?php

namespace flipbox\saml\sp\tests\unit\coverage\services;

use Codeception\Test\Unit;
use craft\elements\User as UserElement;
use craft\models\UserGroup;
use flipbox\saml\sp\events\UserGroupAssign;
use flipbox\saml\sp\services\login\UserGroups;

class UserGroupsServiceTest extends Unit
{
    use \ServiceFixtures;

    protected function _before(): void
    {
        $this->setUpFixtures();
        $this->settings([
            'syncGroups' => true,
            'groupAttributeNames' => ['groups'],
            'mergeExistingGroups' => false,
            'defaultGroupAssignments' => [],
        ]);
    }

    protected function _after(): void
    {
        $this->tearDownFixtures();
    }

    public function testAssignsOnlyExistingGroupsNamedInAssertion(): void
    {
        $engineers = $this->group('Saml Engineers');
        $user = $this->savedUser('groups1@example.com');

        $this->sync($user, ['groups' => ['Saml Engineers', 'Saml Unknown Group']]);

        $this->assertSame([$engineers->id], $this->groupIds($user));
        // Groups are never created from assertion values
        $this->assertNull(\Craft::$app->getUserGroups()->getGroupByHandle('samlUnknownGroup'));
    }

    public function testScalarAttributeValueIsAccepted(): void
    {
        $group = $this->group('Saml Scalar');
        $user = $this->savedUser('groups2@example.com');

        $this->sync($user, ['groups' => 'Saml Scalar']);

        $this->assertSame([$group->id], $this->groupIds($user));
    }

    public function testOnlyConfiguredAttributeNamesAreUsed(): void
    {
        $this->group('Saml Admins');
        $user = $this->savedUser('groups3@example.com');

        $this->sync($user, ['roles' => ['Saml Admins']]);

        $this->assertSame([], $this->groupIds($user));
    }

    public function testSyncDisabledAssignsNothing(): void
    {
        $this->group('Saml Disabled');
        $user = $this->savedUser('groups4@example.com');

        $this->sync($user, ['groups' => ['Saml Disabled']], ['syncGroups' => false]);

        $this->assertSame([], $this->groupIds($user));
    }

    public function testAssertionWithoutAttributesAssignsNothing(): void
    {
        $user = $this->savedUser('groups5@example.com');

        $this->sync($user, []);

        $this->assertSame([], $this->groupIds($user));
    }

    public function testGroupsMissingFromAssertionAreRemovedWhenNotMerging(): void
    {
        $old = $this->group('Saml Old');
        $new = $this->group('Saml New');
        $user = $this->savedUser('groups6@example.com');
        \Craft::$app->getUsers()->assignUserToGroups($user->id, [$old->id]);

        $this->sync($user, ['groups' => ['Saml New']], ['mergeExistingGroups' => false]);

        $this->assertSame([$new->id], $this->groupIds($user));
    }

    public function testExistingGroupsAreKeptWhenMerging(): void
    {
        $old = $this->group('Saml Keep');
        $new = $this->group('Saml Added');
        $user = $this->savedUser('groups7@example.com');
        \Craft::$app->getUsers()->assignUserToGroups($user->id, [$old->id]);
        $user = \Craft::$app->getUsers()->getUserById($user->id);

        $this->sync($user, ['groups' => ['Saml Added']], ['mergeExistingGroups' => true]);

        $ids = $this->groupIds($user);
        sort($ids);
        $expected = [$old->id, $new->id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }

    public function testBeforeAssignEventCanChangeAssignment(): void
    {
        $group = $this->group('Saml Vetoed');
        $user = $this->savedUser('groups8@example.com');
        $service = new UserGroups();
        $seen = null;
        $service->on(UserGroups::EVENT_BEFORE_USER_GROUP_ASSIGN, function(UserGroupAssign $event) use (&$seen) {
            $seen = array_map(fn($g) => $g->id, $event->groupsFoundInAssertions);
            $event->groupToBeAssigned = [];
        });

        $this->sync($user, ['groups' => ['Saml Vetoed']], [], $service);

        $this->assertSame([$group->id], $seen);
        $this->assertSame([], $this->groupIds($user));
    }

    public function testDefaultGroupsAreAddedToAssertedGroups(): void
    {
        $default = $this->group('Saml Default');
        $asserted = $this->group('Saml Asserted');
        $user = $this->savedUser('groups9@example.com');

        $this->sync($user, ['groups' => ['Saml Asserted']], ['defaultGroupAssignments' => [$default->id]]);

        $ids = $this->groupIds($user);
        sort($ids);
        $expected = [$default->id, $asserted->id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }

    private function sync(UserElement $user, array $attributes, array $settings = [], ?UserGroups $service = null): void
    {
        $idp = $this->idp();
        $sp = $this->sp();
        $response = $this->signedResponse($idp, $sp);
        $response->getAssertions()[0]->setAttributes($attributes);

        $this->assertTrue(($service ?? new UserGroups())->sync($user, $response, $sp, $this->settings($settings)));
    }

    private function group(string $name): UserGroup
    {
        $handle = lcfirst(str_replace(' ', '', ucwords($name)));
        $group = \Craft::$app->getUserGroups()->getGroupByHandle($handle);
        if (!$group) {
            $group = new UserGroup(['name' => $name, 'handle' => $handle]);
            $this->assertTrue(\Craft::$app->getUserGroups()->saveGroup($group), json_encode($group->getErrors()));
        }

        return $group;
    }

    /**
     * Group ids as stored, not as cached on the element.
     */
    private function groupIds(UserElement $user): array
    {
        return array_map(
            fn(UserGroup $group) => $group->id,
            \Craft::$app->getUserGroups()->getGroupsByUserId($user->id)
        );
    }
}
