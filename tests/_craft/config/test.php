<?php

use craft\helpers\ArrayHelper;
use craft\services\Config;

// Paths
$basePath = dirname(__DIR__, 3);
$vendorPath = $basePath . '/vendor';
$craftSrcPath = $vendorPath . '/craftcms/cms/src';
$craftTestPath = dirname(__DIR__);

// The environment variable
$environment = 'TEST';

// Create config service
$configService = new Config(
    [
        'env' => $environment,
        'configDir' => $craftTestPath . '/config',
        'appDefaultsDir' => $craftTestPath . '/config/defaults'
    ]
);

// Set config component
$components = [
    'config' => $configService,
];

return ArrayHelper::merge(
    [
        'vendorPath' => $vendorPath,
        'env' => $environment,
        'components' => $components,
    ],
    require $craftSrcPath . "/config/app.php",
    require $craftSrcPath . "/config/app.web.php",
    $configService->getConfigFromFile('app'),
    $configService->getConfigFromFile('app.web')
);