<?php

// Prepare serverless writable storage paths in /tmp
$dirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/public/thumbnails',
    '/tmp/storage/app/google',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';
putenv('VERCEL=1');

// Delegate directly to Laravel public/index.php
require __DIR__ . '/../public/index.php';
