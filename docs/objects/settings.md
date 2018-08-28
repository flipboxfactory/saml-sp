# Settings
The Organization Settings model supports all [Model] operations plus the following:

## Public Properties
All of the standard [Model](https://www.yiiframework.com/doc/api/2.0/yii-base-model#properties) public properties are available plus the following:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `defaultState`        | [string], [null]                              | The organization's default state
    

## Public Methods
All of the standard [Model](https://www.yiiframework.com/doc/api/2.0/yii-base-model#methods) public methods are available plus the following:

### `getFieldLayoutId()` 

Returns: [integer]

### `getFieldLayout()` 

Returns: [Field Layout]


### `hasStates()` 

Returns: [boolean]


### `getStates()` 

Returns: [string\[\]]

### `isSiteEnabled( int $siteId = null )` 

Returns: [boolean]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$siteId`         | [integer], [null]         | The [Site](https://docs.craftcms.com/v3/sites.html#app) Id.  If null the primary site Id is used.


### `getEnabledSiteIds()` 

Returns: [integer\[\]]

[integer\[\]]: http://www.php.net/language.types.integer
[string\[\]]: http://www.php.net/language.types.string
[boolean]: http://www.php.net/language.types.boolean
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null

[Field Layout]: https://docs.craftcms.com/api/v3/craft-models-fieldlayout.html
[Model]: https://www.yiiframework.com/doc/api/2.0/yii-base-model
