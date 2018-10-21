<?php


namespace App\Logger;


use Psr\Log\AbstractLogger;

class ProcessLogger extends AbstractLogger
{
    /**
     * @var bool|resource
     */
    private $logfile;

    /**
     * @return string
     */
    static public function getFilename()
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
            $str = str_replace("\t", " ", $str);
        });

        $line = implode("\t", $columns) . "\n";

        echo $line;
        fwrite($this->logfile, $line);
    }
}