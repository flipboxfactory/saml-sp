# Organization Types

 This service is used to manage one or many [Organization Types].

[[toc]]

### `find( $identifier )`

Returns a [Organization Type] or [null] if not found.

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Organization Type] | A unique [Organization Type] identifier

::: code
```twig
{% set element = craft.organizations.organizationTypes.find(1) %}
{% set element = craft.organizations.organizationTypes.find('ngo') %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getUserTypes()->find(1);
$element = Organizations::getInstance()->getUserTypes()->find('ngo');
```
:::

### `findByCondition( $condition )`
Returns a [Organization Type] or [null] if not found.

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$condition`      | [array]                   | A query 'where' condition

::: code
```twig
{% set element = craft.organizations.organizationTypes.findByCondition({
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
Returns a [Organization Type] or [null] if not found.

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | A query configuration

::: code
```twig
{% set element = craft.organizations.organizationTypes.findByCriteria({
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
Returns an array of [Organization Types].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$condition`      | [array]                   | A query 'where' condition

::: code
```twig
{% set elements = craft.organizations.organizationTypes.findAllByCondition({
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
Returns an array of [Organization Types].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | A query configuration

::: code
```twig
{% set element = craft.organizations.organizationTypes.findAllByCriteria({
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

Returns a [Organization Type Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | A query configuration

::: code
```twig
{% set query = craft.organizations.organizationTypes.getQuery({
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

[Organization Type Query]: ../queries/organization-type.md
[Organization Type]: ../objects/organization-type.md
[Organization Types]: ../objects/organization-type.md