<?php

/**
 * Database Configuration.
 *
 * All of your system's database configuration settings go in here.
 * You can see a list of the default settings in craft/app/config/defaults/db.php
 */

return [
    '*' => [
        'tablePrefix' => 'craft',
        'server' => getenv('TEST_DB_SERVER'),
        'database' => getenv('TEST_DB_DATABASE'),
        'user'   => getenv('TEST_DB_USER'),
        'password' => getenv('TEST_DB_PASSWORD'),
    ],
];