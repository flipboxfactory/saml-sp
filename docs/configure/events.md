# Events

There are events within the plugin that developers can hook into.

## List

- `\flipbox\saml\sp\services\messages\AuthnRequest::EVENT_AFTER_MESSAGE_CREATED`
    - Use to modify AuthNRequest Message
- `\flipbox\saml\sp\services\Login::EVENT_BEFORE_RESPONSE_TO_USER`
    - Use to modify user or response before user is synced with Saml response attributes and saved
- `\flipbox\saml\sp\services\Login::EVENT_AFTER_RESPONSE_TO_USER`
    - User to modify user after the user has been synced with Saml Response attributes and saved.
    
## Examples

### Assign User to a User Group Based on a Property

```php
Event::on(
            \flipbox\saml\sp\services\Login::class,
            \flipbox\saml\sp\services\Login::EVENT_AFTER_RESPONSE_TO_USER,
            function (\flipbox\saml\sp\events\UserLogin $event) {

                /** @var \craft\elements\User $user */
                $user = $event->user;

                /**
                 * get existing groups
                 */
                $groups = [];
                foreach ($user->getGroups() as $group) {
                    $groups[$group->id] = $group;
                }
                
                /**
                 * Logic to Determine Is Admin
                 * (return if they aren't admin)
                 */
                if(! MyUserHelper::isAdminUser($user, $response)){
                    return;
                }

                /**
                 * get the default group by handle
                 */
                $group = \Craft::$app->getUserGroups()->getGroupByHandle('myAdminGroup');

                /**
                 * add it to the group array
                 */
                $groups[$group->id] = $group;

                /**
                 * get an array of ids
                 */
                $groupIds = array_map(
                    function ($group) {
                        return $group->id;
                    },
                    $groups
                );

                /**
                 * Assign them to the user
                 */
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

