# Events
The majority of events are triggered at the object level through first-party record or element events.

## Objects 
* [Organization Element](https://docs.craftcms.com/api/v3/craft-base-element.html#events)
* [Organization Type](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events)
* [Organization Type Site Settings](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events)
* [Settings](https://www.yiiframework.com/doc/api/2.0/yii-base-model#events)
* [User Type](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events)

## Services

### Organization State Change
The Organization state is a [configurable](/configure/) option set which assists in managing complex organization statuses or workflows.  The following events are triggered when the state value is altered:

#### `EVENT_BEFORE_STATE_CHANGE`
Triggered *before* an organization state change occurs.

```php
    Event::on(
        \flipbox\organizations\services\Records::class,
        \flipbox\organizations\services\Records::EVENT_BEFORE_STATE_CHANGE,
        function (\flipbox\organizations\events\ChangeStateEvent $e) {
            // Changed state from $e->organization->state to $e->to
    );
```

#### `EVENT_AFTER_STATE_CHANGE`
Triggered *after* an organization state change occurs.

```php
    Event::on(
        \flipbox\organizations\services\Records::class,
        \flipbox\organizations\services\Records::EVENT_AFTER_STATE_CHANGE,
        function (\flipbox\organizations\events\ChangeStateEvent $e) {
            // Changed state to $e->to
    );
```

## Views

### Organization Actions

#### `EVENT_REGISTER_ORGANIZATION_ACTIONS`
Triggered when available actions are being registered on the organization detail view

```php
    Event::on(
        \flipbox\organizations\cp\controllers\view\OrganizationsController::class,
        \flipbox\organizations\cp\controllers\view\OrganizationsController::EVENT_REGISTER_ORGANIZATION_ACTIONS,
        function (\flipbox\organizations\events\RegisterOrganizationActionsEvent $e) {
            // Manage `$e->destructiveActions` and `$e->miscActions`
    );
```
