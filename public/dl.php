<?php

/*
 * This file is part of the Arnapou gw2logs package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Log;

require __DIR__ . '/../vendor/autoload.php';

try {
    if (!isset($_REQUEST['log'])) {
        throw new Exception('404 File not Found', 404);
    }

    $log = new Log($_REQUEST['log']);
    if (!is_file($log->pathname())) {
        throw new Exception('404 File not Found', 404);
    }

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: public');
    header('Content-Description: File Transfer');
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $log->filename() . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($log->pathname()));
    readfile($log->pathname());
} catch (\Exception $exception) {
    header($exception->getMessage(), true, $exception->getCode());
    echo $exception->getMessage() . "\n";
}
