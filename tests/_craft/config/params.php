<?php

// Project root path
$root = dirname(__DIR__, 3);

// .env
if (file_exists($root.'/.env')) {
    $dotenv = new Dotenv\Dotenv($root);
    $dotenv->load();
}

return [];