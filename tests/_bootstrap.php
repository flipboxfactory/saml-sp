<?php

define('CRAFT_ENVIRONMENT', 'test');
define('YII_ENV', 'test');
define('YII_DEBUG', true);

// Set path constants
define('CRAFT_BASE_PATH', __DIR__.'/_craft');
define('CRAFT_STORAGE_PATH', __DIR__.'/_craft/storage');
define('CRAFT_TEMPLATES_PATH', __DIR__.'/_craft/templates');
define('CRAFT_CONFIG_PATH', __DIR__.'/_craft/config');
define('CRAFT_VENDOR_PATH', __DIR__.'/../vendor');

// Load Composer's autoloader

//require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
//require_once __DIR__ . '/../vendor/craftcms/cms/src/Craft.php';
require_once CRAFT_VENDOR_PATH.'/autoload.php';