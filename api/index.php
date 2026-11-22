<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure writable /tmp storage paths exist for serverless environment
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/public/thumbnails',
    '/tmp/storage/app/google',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';
putenv('VERCEL=1');

// Redirect compiled views to /tmp
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

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

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html');
    echo '<div style="background:#09090b;color:#f87171;padding:24px;font-family:monospace;border-radius:12px;margin:20px;border:1px solid #ef4444;">';
    echo '<h2 style="color:#ef4444;margin-top:0;">Server Error: ' . htmlspecialchars($e->getMessage()) . '</h2>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre style="color:#d4d4d8;background:#18181b;padding:16px;border-radius:8px;overflow-x:auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
    exit(1);
}
