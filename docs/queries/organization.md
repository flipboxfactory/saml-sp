# Organization Query

The Organization Query supports all [Element Query] operations plus the following:

## Params

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `state`               | [string], [string\[\]], [null]                                                                    | The organization's state (custom defined)
| `user`                | [string], [string\[\]], [integer], [integer\[\]], [User], [User\[\]], [null]                              | The user(s) that result' users must be associated to
| `userGroup`           | [string], [string\[\]], [integer], [integer\[\]], [User Group], [User Group\[\]], [null]                  | The user group(s) that the result's users must be assigned to
| `userType`            | [string], [string\[\]], [integer], [integer\[\]], [User Type], [User Type\[\]], [null]                    | The user type(s) that the result's users must be associated with
| `organizationType`    | [string], [string\[\]], [integer], [integer\[\]], [Organization Type], [Organization Type\[\]], [null]    | The the organization type(s) that the result's must be associated to
| `dateJoined`          | [string], [DateTime], [null]                                                                    | The date the organization joined

## Chain Setting

All of the params above can be accessed and chained together.  The methods are named the same as the property.

Here is an example:

::: code

```twig
{% set query = craft.organizations.elements.getQuery() %}
{% do query.state('foo').user(currentUser) %}
```

```php
use flipbox\organizations\Organizations;

$query = Organizations::getInstance()->getOrganizations()->getQuery()
    ->state('foo')
    ->user(\Craft::$app->getUser()->getIdentity());
```
:::

[integer]: http://www.php.net/language.types.integer "Integer"
[integer\[\]]: http://www.php.net/language.types.integer "Integer"
[array]: http://www.php.net/language.types.array "Array"
[string]: http://www.php.net/language.types.string "String"
[string\[\]]: http://www.php.net/language.types.string "String"
[null]: http://www.php.net/language.types.null "Null"
[DateTime]: http://php.net/manual/en/class.datetime.php

[User]: https://docs.craftcms.com/api/v3/craft-elements-user.html "User"
[User\[\]]: https://docs.craftcms.com/api/v3/craft-elements-user.html "User"
[User Group]: https://docs.craftcms.com/api/v3/craft-models-usergroup.html "User Group"
[User Group\[\]]: https://docs.craftcms.com/api/v3/craft-models-usergroup.html "User Group"

[User Type]: ../objects/user-type.md
[User Type\[\]]: ../objects/user-type.md
[Organization Type]: ../objects/organization-type.md
[Organization Type\[\]]: ../objects/organization-type.md

[Organization]: ../objects/organization.md
[Element Query]: https://docs.craftcms.com/v3/element-queries.html
