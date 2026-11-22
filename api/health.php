<?php
header('Content-Type: text/plain');

echo "=== VERCEL PHP DIAGNOSTIC ===\n\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server OS: " . PHP_OS . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Loaded Extensions: " . implode(', ', get_loaded_extensions()) . "\n\n";

echo "=== ENVIRONMENT VARIABLES ===\n";
echo "APP_KEY: " . (getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? 'NOT SET')) . "\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'NOT SET')) . "\n";
echo "DB_CONNECTION: " . (getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'NOT SET')) . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'NOT SET')) . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 'NOT SET')) . "\n";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'NOT SET')) . "\n";
echo "DB_USERNAME: " . (getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'NOT SET')) . "\n\n";

echo "=== STORAGE & WRITABILITY ===\n";
$tmp = sys_get_temp_dir();
echo "Temp Dir: {$tmp}\n";
echo "Is Temp Writable: " . (is_writable($tmp) ? 'YES' : 'NO') . "\n";
echo "Vendor autoload exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "Public index exists: " . (file_exists(__DIR__ . '/../public/index.php') ? 'YES' : 'NO') . "\n";
echo "Bootstrap app exists: " . (file_exists(__DIR__ . '/../bootstrap/app.php') ? 'YES' : 'NO') . "\n\n";

// Test Laravel bootstrap
echo "=== LARAVEL BOOTSTRAP TEST ===\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "Autoload: SUCCESS\n";
    
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "App Bootstrap: SUCCESS (" . get_class($app) . ")\n";
    
    echo "Storage Path: " . $app->storagePath() . "\n";
} catch (\Throwable $e) {
    echo "BOOTSTRAP ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
