# User

::: tip Notice
We have attached an extra `organizations` attribute to the native [User Element].  You can utilize this attribute on any [User Element] to get all of the associated [Organizations].
:::

## Public Properties
All of the standard [User Element](https://docs.craftcms.com/api/v3/craft-elements-user.html#public-properties) public properties are available plus the following:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `organization`        | [array] , [Organization Query]        | [Organization Query] criteria


## Public Methods
All of the standard [User Element](https://docs.craftcms.com/api/v3/craft-elements-user.html#public-methods) public methods are available plus the following:

### `getOrganizations( $criteria = [] )` 

Returns: [Organization Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | [Organization Query] criteria

::: code
```twig
{% set organizations = currentUser.getOrganizations({limit: 10}).all() %}
<ul>
{% for organization in organizations %}
    <li>{{ organization.id }} - {{ organization.title }}</li>
{% endfor %}
</ul>
```

```php
use Craft;

$currentUser = Craft::$app->getUser()->getIdentity();
$organizations = $currentUser->getOrganizations([
    'limit' => 10
]);

foreach ($organizations as $organization) {
    // $organization
}
```
:::

[integer]: http://www.php.net/language.types.integer
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[array]: http://www.php.net/language.types.array

[Active Record]: https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord "Active Record"
[User Element]: https://docs.craftcms.com/api/v3/craft-elements-user.html "User Element"

[Organization Query]: ../queries/organization.md
[Organizations]: organization.md