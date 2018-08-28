# User Query

::: tip Notice
We have attached an extra `organization` filter criteria to the native [User Query].  You can utilize this param on any [User Query]
:::

## Params

All of the standard [User Query] params are available plus the following:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `organization`        | [string], [string\[\]], [integer], [integer\[\]], [Organization], [Organization\[\]], [null] | The organization criteria that the resulting users must have

::: tip Note
The organization criteria (above) can optionally accept three individual criteria:
```php
{
    id: 1, // The organization identifier(s)
    organizationType: [1,2], // The organization type identifier(s)
    userType: [1,2] // The user type identifier(s)
}
```
:::


## Chain Setting

All of the params above can be accessed and chained together.  The methods are named the same as the property.

Here is an example:

::: code

```twig
{% set query = craft.organizations.users.getQuery() %}
{% do query.organization(1).firstName('Foo') %}
```

```php
use flipbox\organizations\Organizations;

$query = Organizations::getInstance()->getUsers()->getQuery()
    ->organization(1)
    ->firstName('Foo');
```
:::

[integer]: http://www.php.net/language.types.integer
[integer\[\]]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[string\[\]]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null

[Organization]: ../objects/organization.md
[Organization\[\]]: ../objects/organization.md

[User Query]: https://docs.craftcms.com/v3/element-query-params/user-query-params.html "User Query"
