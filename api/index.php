<?php

// Ensure writable directories in /tmp for serverless environment
$tmpDirs = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';
