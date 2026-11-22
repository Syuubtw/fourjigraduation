<?php

// Ensure /tmp storage paths exist for serverless environment
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/public/thumbnails',
    '/tmp/storage/app/google',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';
putenv('VERCEL=1');

// Redirect compiled views and bootstrap cache to writable /tmp
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

// Force safe serverless defaults in case ENV vars are missing
if (!getenv('LOG_CHANNEL') || getenv('LOG_CHANNEL') === 'stack') {
    putenv('LOG_CHANNEL=stderr');
    $_ENV['LOG_CHANNEL'] = 'stderr';
}
if (!getenv('CACHE_STORE') || getenv('CACHE_STORE') === 'database') {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
}
if (!getenv('SESSION_DRIVER') || getenv('SESSION_DRIVER') === 'database') {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
}

// Forward execution to Laravel's public entrypoint
require __DIR__ . '/../public/index.php';
