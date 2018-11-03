# Events

There are events within the plugin that developers can hook into.

## List

- Modify AuthNRequest message.
`\flipbox\saml\sp\services\messages\AuthnRequest::EVENT_AFTER_MESSAGE_CREATED`
 
- Modify user or response before they're synced with Saml response attributes and saved.
`\flipbox\saml\sp\services\Login::EVENT_BEFORE_RESPONSE_TO_USER`

- Modify the user after they've been synced with Saml response attributes and saved.
<br>
 `\flipbox\saml\sp\services\Login::EVENT_AFTER_RESPONSE_TO_USER`
    
## Examples

### Assign User to a User Group Based on a Property

```php
Event::on(
    \flipbox\saml\sp\services\Login::class,
    \flipbox\saml\sp\services\Login::EVENT_AFTER_RESPONSE_TO_USER,
    function (\flipbox\saml\sp\events\UserLogin $event) {
    
        /** @var \craft\elements\User $user */
        $user = $event->user;

        // Get existing groups
        $groups = [];
        foreach ($user->getGroups() as $group) {
            $groups[$group->id] = $group;
        }
            
        // Determine if admin, return if not
        if (! MyUserHelper::isAdminUser($user, $response)){
            return;
        }

        // Get default group by handle
        $group = \Craft::$app->getUserGroups()->getGroupByHandle('myAdminGroup');

        // Add it to the group array
        $groups[$group->id] = $group;

        // Get an array of ids
        $groupIds = array_map(
            function ($group) {
                return $group->id;
            },
            $groups
        );

        // Assign them to the user groups
        if (\Craft::$app->getUsers()->assignUserToGroups($user->id, $groupIds)) {
            /**
             * Set the groups back on the user just in case it's being used after this.
             *
             * This may seem strange because the they do this in the `assignUserToGroups`
             * method but the user they set the groups to isn't *this* user object,
             * so this is needed.
             */
            $user->setGroups($groups);
        }
    }
);
``` 

