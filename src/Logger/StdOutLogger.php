<?php


namespace App\Logger;


use Psr\Log\AbstractLogger;

class StdOutLogger extends AbstractLogger
{

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
        echo sprintf("[%s] %-9s %s - %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            implode(' ', $context),
            $message
        );
    }
}