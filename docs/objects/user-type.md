# User Type

The User Type object is an extension of a native [Active Record] plus the following:

## Public Properties
The following properties are available:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `id`                  | [integer]                             | The organization type's Id
| `handle`              | [string]                              | The organization type's reference name
| `name`                | [string]                              | The organization type's human readable name
| `uid`                 | [string], [null]                      | The unversally unique identifier
| `dateCreated`         | [DateTime], [null]                    | The date the user type was created
| `dateUpdated`         | [DateTime], [null]                    | The date the user type was last updated

[integer]: http://www.php.net/language.types.integer
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[array]: http://www.php.net/language.types.array
[DateTime]: http://php.net/manual/en/class.datetime.php

[Active Record]: https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord "Active Record"