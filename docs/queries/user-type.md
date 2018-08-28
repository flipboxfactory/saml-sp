# User Type Query

The User Type Query supports all [Active Query] operations plus the following:
 
## Params
All of the standard [Active Query](https://www.yiiframework.com/doc/api/2.0/yii-db-activequery#properties) public properties are available plus the following:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `handle`              | [string], [string\[\]], [null]                                                        | The handle that the resulting user type(s) must have
| `user`                | [string], [string\[\]], [integer], [integer\[\]], [User], [User\[\]], [null]          | The user(s) that the resulting user type(s) must be associated to
| `id`                  | [integer], [integer\[\]], [null]                                                      | The id that the resulting user type(s) must have
| `name`                | [string], [string\[\]], [null]                                                        | The name that the resulting user type(s) must have
| `uid`                 | [string], [string\[\]], [null]                                                        | The uid that the resulting user type(s) must have
| `dateCreated`         | [string], [array], [DateTime], [null]                                                 | The creation date that the resulting user type(s) must have
| `dateUpdated`         | [string], [array], [DateTime], [null]                                                 | The updated date that the resulting user type(s) must have


## Chain Setting

All of the params above can be accessed and chained together.  The methods are named the same as the property.

Here is an example:

::: code

```twig
{% set query = craft.organizations.userTypes.getQuery() %}
{% do query.handle('foo').name('Bar') %}
```

```php
use flipbox\organizations\Organizations;

$query = Organizations::getInstance()->getUserTypes()->getQuery()
    ->handle('foo')
    ->name('Bar');
```
:::

[integer]: http://www.php.net/language.types.integer
[integer\[\]]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[string\[\]]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[DateTime]: http://php.net/manual/en/class.datetime.php

[Active Query]: https://www.yiiframework.com/doc/api/2.0/yii-db-activequery


[User]: ../objects/user.md
[User\[\]]: ../objects/user.md
[user type(s)]: ../objects/user-type.md

[User Query]: https://docs.craftcms.com/v3/element-query-params/user-query-params.html "User Query"
