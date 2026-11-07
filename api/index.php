<?php

// Ensure writable directories in /tmp for serverless environment
$tmpDirs = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    error_log("[LARAVEL_FATAL_BOOT] " . get_class($e) . " => " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    throw $e;
}
