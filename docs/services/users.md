# User Elements

This service is used to manage one or many [User Elements].

[[toc]]

### `find( $identifier )`
Returns an [User Element]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [User] | A unique [User] identifier such as username, email or id.


::: code

```twig
{% set element = craft.organizations.users.find(1) %}
{% set element = craft.organizations.users.find('foo@bar.com') %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUsers()->find(1);
$element = Organizations::getInstance()->getUsers()->find('nateiler');
```
:::

### `getQuery( $criteria )`

Returns a [User Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | An array of [User Query] criteria.


::: code
```twig
{% set query = craft.organizations.users.getQuery({
    id: 1
}) %}
```

```php
use flipbox\organizations\Organizations;

$quey = Organizations::getInstance()->getUsers()->getQuery([
    'id' => 1
]);
```
:::

### `create( $config = [] )`

Returns a new [User Element] (but does not save).

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$config`         | [array]                   | An user configuration

::: code
```twig
{% set element = craft.organizations.users.create({
    firstName: 'Nate',
    lastName: 'Iler'
}) %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUsers()->create([
    firstName: 'Nate',
    lastName: 'Iler'
]);
```
:::

[integer]: http://www.php.net/language.types.integer
[integer\[\]]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[string\[\]]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null

[User Query]: ../queries/user.md
[User]: ../objects/user.md
[User Element]: ../objects/user.md
[User Elements]: ../objects/user.md