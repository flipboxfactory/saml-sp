# Organization Type Query

The Organization Type Query supports all [Active Query] operations plus the following:
 
## Params
All of the standard [Active Query](https://www.yiiframework.com/doc/api/2.0/yii-db-activequery#properties) public properties are available plus the following:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `handle`              | [string], [string\[\]], [null]                                                        | The handle that the resulting [organization type(s)] must have
| `fieldLayoutId`       | [integer], [integer\[\]], [null]                                                      | The field layout id that the resulting [organization type(s)] must have
| `organization`        | [string], [string\[\]], [integer], [integer\[\]], [Organization], [Organization\[\]], [null]  | The organization(s) that the resulting [organization type(s)] must be associated to
| `id`                  | [integer], [integer\[\]], [null]                                                      | The id that the resulting [organization type(s)] must have
| `name`                | [string], [string\[\]], [null]                                                        | The name that the resulting [organization type(s)] must have
| `uid`                 | [string], [string\[\]], [null]                                                        | The uid that the resulting [organization type(s)] must have
| `dateCreated`         | [string], [array], [DateTime], [null]                                                 | The creation date that the resulting [organization type(s)] must have
| `dateUpdated`         | [string], [array], [DateTime], [null]                                                 | The updated date that the resulting [organization type(s)] must have

## Chain Setting

All of the params above can be accessed and chained together.  The methods are named the same as the property.

Here is an example:

::: code

```twig
{% set query = craft.organizations.organizationTypes.getQuery() %}
{% do query.handle('foo').name('Bar') %}
```

```php
use flipbox\organizations\Organizations;

$query = Organizations::getInstance()->getOrganizationTypes()->getQuery()
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

[Organization]: ../objects/organization.md
[Organization\[\]]: ../objects/organization.md

[organization type(s)]: ../objects/organization-type.md