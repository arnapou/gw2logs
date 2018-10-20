<?php

namespace App;


use CallbackFilterIterator;
use InvalidArgumentException;
use RuntimeException;

class Log
{
    const FILENAME_REGEXP = '!^(20[0-9]{6})-([0-9]{6}).+\.zip$!';
    /**
     * @var
     */
    private $filename;
    /**
     * @var string
     */
    private $date;
    /**
     * @var string
     */
    private $time;
    /**
     * @var LogMetadata
     */
    private $metadata;
    /**
     * @var bool
     */
    private $deleted = false;

    /**
     * Log constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        if (!preg_match(self::FILENAME_REGEXP, $filename, $matches)) {
            throw new InvalidArgumentException('Filename is not valid');
        }
        $this->date     = $matches[1];
        $this->time     = $matches[2];
        $this->metadata = new LogMetadata($this->path());
    }

    /**
     * @param array $filtres
     * @return bool
     */
    private function match(array $filtres)
    {
        foreach ($filtres as $text) {
            if ($text && !$this->matchOne($text)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $text
     * @return bool
     */
    private function matchOne($text)
    {
        $found = stripos($this->filename, $text) !== false
            || stripos($this->metadata->getBoss(), $text) !== false
            || stripos($this->metadata->getUrlRaidar(), $text) !== false
            || stripos($this->metadata->getUrlDpsReport(), $text) !== false
            || stripos($this->metadata->getStatus(), $text) !== false
            || stripos(implode(' ', $this->metadata->getTags()), $text) !== false;
        if ($found) {
            return true;
        }
        foreach ($this->metadata->getPlayers() as $player) {
            if (stripos($player['display_name'], $text) !== false || stripos($player['character_name'], $text) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $filename
     * @return bool
     */
    public function isSameDateTimeAs($filename)
    {
        return strpos($filename, $this->date . '-' . $this->time) === 0;
    }

    /**
     * @return string
     */
    public function path()
    {
        return __DIR__ . '/../logs/' . substr($this->date, 0, 6) . '/' . substr($this->date, 6) . '-' . $this->time;
    }

    /**
     * @return int
     */
    public function size()
    {
        return is_file($this->pathname()) ? filesize($this->pathname()) : 0;
    }

    /**
     * @return mixed
     */
    public function filename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function pathname()
    {
        return $this->path() . '/' . $this->filename;
    }

    /**
     * @return LogMetadata
     */
    public function metadata()
    {
        return $this->metadata;
    }

    /**
     * @return string
     */
    public function datetime()
    {
        return substr($this->date, 0, 4) . '-' . substr($this->date, 4, 2) . '-' . substr($this->date, 6)
            . ' ' . substr($this->time, 0, 2) . ':' . substr($this->time, 2, 2);
    }

    /**
     *
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->deleted) {
            $files = glob($this->path() . '/*');
            foreach (($files ?: []) as $file) {
                unlink($file);
            }
            rmdir($this->path());
        }
    }

    /**
     * @param array $info
     * @return Log
     */
    static public function upload(array $info)
    {
        $log = new self($info['name']);
        if ($info['error'] !== UPLOAD_ERR_OK) {
            $errMap = [
                UPLOAD_ERR_INI_SIZE   => 'ERR_INI_SIZE',
                UPLOAD_ERR_FORM_SIZE  => 'ERR_FORM_SIZE',
                UPLOAD_ERR_PARTIAL    => 'ERR_PARTIAL',
                UPLOAD_ERR_NO_FILE    => 'ERR_NO_FILE',
                UPLOAD_ERR_NO_TMP_DIR => 'ERR_NO_TMP_DIR',
                UPLOAD_ERR_CANT_WRITE => 'ERR_CANT_WRITE',
                UPLOAD_ERR_EXTENSION  => 'ERR_EXTENSION',
            ];
            throw new RuntimeException('Upload error ' . ($errMap[$info['error']] ?? 'UNKOWN'));
        }
        if ($info['size'] == 0) {
            throw new RuntimeException('Upload size error');
        }
        if (is_file($log->pathname())) {
            throw new RuntimeException('File was already uploaded');
        }
        if (!is_dir($log->path())) {
            mkdir($log->path(), 0777, true);
            chmod($log->path(), 0777);
        }
        if (!move_uploaded_file($info['tmp_name'], $log->pathname())) {
            throw new RuntimeException('Upload move error');
        }
        chmod($log->pathname(), 0777);
        return $log;
    }

    /**
     * @param array $filtres
     * @param int   $offset
     * @param int   $length
     * @return LogList
     */
    static function all($filtres = [], $offset = 0, $length = 100)
    {
        $offset    = Utils::validInteger($offset, 0, 0);
        $length    = Utils::validInteger($length, 0, 100);
        $filtres   = is_array($filtres) ? $filtres : [];
        $directory = new \RecursiveDirectoryIterator(__DIR__ . '/../logs', \FilesystemIterator::SKIP_DOTS);
        $iterator  = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::LEAVES_ONLY);
        $logs      = iterator_to_array(
            new CallbackFilterIterator(
                $iterator,
                function (\SplFileInfo &$current, $key, $iterator) use ($filtres) {
                    if (preg_match(self::FILENAME_REGEXP, $current->getBasename())) {
                        $log = new Log($current->getBasename());
                        if ($log->match($filtres)) {
                            $current = $log;
                            return true;
                        }
                    }
                    return false;
                }
            )
        );
        usort($logs, function (Log $a, Log $b) {
            return -strcmp($a->filename(), $b->filename());
        });
        return new LogList($logs, $offset, $length, $filtres);
    }

}