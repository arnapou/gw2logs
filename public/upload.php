<?php

use App\Log;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($_FILES[UPLOAD_PARAMETER_NAME])) {
    header('500 Internal Server Error', true, 500);
    echo "500 Internal Server Error\n";
    exit;
}

$log = Log::upload($_FILES[UPLOAD_PARAMETER_NAME]);

echo "OK " . $log->filename() . "\n";
