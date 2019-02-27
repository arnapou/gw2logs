<?php

/*
 * This file is part of the Arnapou gw2logs package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Logger;

use Psr\Log\AbstractLogger;

class ProcessLogger extends AbstractLogger
{
    const CODE_NOTICE = 100;
    /**
     * @var bool|resource
     */
    private $logfile;

    /**
     * @return string
     */
    public static function getFilename()
    {
        return __DIR__ . '/../../logs/process.log';
    }

    /**
     * ProcessLogger constructor.
     */
    public function __construct()
    {
        $this->logfile = fopen(self::getFilename(), 'a+');
        chmod(self::getFilename(), 0777);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $columns   = [date('Y-m-d H:i:s'), strtoupper($level)];
        $columns   = array_merge($columns, $context);
        $columns[] = $message;

        array_walk($columns, function (&$str) {
            $str = str_replace("\t", ' ', $str);
        });

        $line = implode("\t", $columns) . "\n";

        echo $line;
        fwrite($this->logfile, $line);
    }
}
