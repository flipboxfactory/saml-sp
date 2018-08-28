# User Types

This service is used to manage one or many [User Types].

[[toc]]

### `find( $identifier )`

Returns a [User Type] or [null] if not found.

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [User Type] | A unique [User Type] identifier

::: code
```twig
{% set element = craft.organizations.userTypes.find(1) %}
{% set element = craft.organizations.userTypes.find('president') %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUserTypes()->find(1);
$element = Organizations::getInstance()->getUserTypes()->find('president');
```
:::

### `findByCondition( $condition )`
Returns a [User Type] or [null] if not found.

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$condition`      | [array]                   | A query 'where' condition

::: code
```twig
{% set element = craft.organizations.userTypes.findByCondition({
    handle: 'foo'
}) %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUserTypes()->findByCondition([
    'handle' => 'foo'
]);
```
:::

### `findByCriteria( $criteria )`
Returns a [User Type] or [null] if not found.

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | A query configuration

::: code
```twig
{% set element = craft.organizations.userTypes.findByCriteria({
    id: 1,
    indexBy: 'id'
}) %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUserTypes()->findByCriteria([
    'id' => 1,
    'indexBy' => 'id'
]);
```
:::

### `findAllByCondition( $condition )`
Returns an array of [User Types].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$condition`      | [array]                   | A query 'where' condition

::: code
```twig
{% set elements = craft.organizations.userTypes.findAllByCondition({
    name: 'Foo'
}) %}
```

```php
use flipbox\organizations\Organizations;

$elements = Organizations::getInstance()->getUserTypes()->findAllByCondition([
    'name' => 'Foo'
]);
```
:::


### `findAllByCriteria( $criteria )`
Returns an array of [User Types].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | A query configuration

::: code
```twig
{% set element = craft.organizations.userTypes.findAllByCriteria({
    name: 'Foo',
    indexBy: 'id'
}) %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUserTypes()->findAllByCriteria([
    'name' => 'Foo',
    'indexBy' => 'id'
]);
```
:::

### `getQuery( $criteria )`

Returns a [User Type Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | A query configuration

::: code
```twig
{% set query = craft.organizations.userTypes.getQuery({
    name: 'Foo',
    indexBy: 'id'
}) %}
```

```php
use flipbox\organizations\Organizations;

$query = Organizations::getInstance()->getUserTypes()->getQuery([
    'name' => 'Foo',
    'indexBy' => 'id'
]);
```
:::

[integer]: http://www.php.net/language.types.integer
[integer\[\]]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[string\[\]]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null

[User Type Query]: ../queries/user-type.md
[User Type]: ../objects/user-type.md
[User Types]: ../objects/user-type.md