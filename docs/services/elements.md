# Organization Elements

This service is used to manage one or many [Organization Elements].

[[toc]]

### `find( $identifier, int $siteId = null )`

Returns an [Organization Element]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Organization] | A unique [Organization] identifier
| `$siteId`         | [integer], [null]         | The [Site] Id that the [Organization] must belong to

::: code
```twig
{% set element = craft.organizations.elements.find(1) %}
{% set element = craft.organizations.elements.find('flipbox') %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->find(1);
$element = Organizations::getInstance()->getOrganizations()->find('flipbox');
```
:::

### `getQuery( $criteria )`

Returns a [Organization Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | An array of [Organization Query] criteria.


::: code
```twig
{% set query = craft.organizations.elements.getQuery({
    id: 1
}) %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->getQuery([
    'id' => 1
]);
```
:::


### `create( $config = [] )`

Returns a new [Organization Element] (but does not save).

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$config`         | [array]                   | An organization configuration

::: code
```twig
{% set element = craft.organizations.elements.create({
    title: 'Flipbox Digital'
}) %}
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->create([
    title: 'Flipbox Digital'
]);
```
:::


[integer]: http://www.php.net/language.types.integer
[integer\[\]]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[string\[\]]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null


[Site]: https://docs.craftcms.com/api/v3/craft-models-site.html

[Organization Query]: ../queries/organization.md "Organization Query"
[Organization]: ../objects/organization.md "Organization Element"
[Organization Element]: ../objects/organization.md "Organization Element"
[Organization Elements]: ../objects/organization.md "Organization Element"