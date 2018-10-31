<?php


namespace App;


class LogMetadata
{
    const TAG_PROCESSING = 'processing';
    const TAG_GW2RAIDAR = 'gw2raidar';
    const TAG_GW2RAIDARURL = 'gw2raidarurl';
    const TAG_DPSREPORT = 'dpsreport';
    const TAG_DISABLED = 'disabled';

    const STATUS_KILL = 'kill';
    const STATUS_FAIL = 'fail';
    /**
     * @var string
     */
    private $path;
    /**
     * @var array
     */
    private $data = [
        'tags'          => [],
        'encounterTime' => null,
        'bossId'        => null,
        'boss'          => '',
        'status'        => '',
        'players'       => [],
        'urlRaidar'     => '',
        'urlDpsReport'  => '',
        'CUSTOM'        => [],
    ];
    /**
     * @var \DateTimeImmutable|null
     */
    private $lastModified = null;

    /**
     * LogMetadata constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->load();
    }

    /**
     * @return array
     */
    public function getPlayers()
    {
        return $this->data['players'];
    }

    /**
     * @param $players
     * @return LogMetadata
     */
    public function setPlayers($players)
    {
        $this->data['players'] = $players;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->data['status'];
    }

    /**
     * @param $status
     * @return LogMetadata
     */
    public function setStatus($status)
    {
        $this->data['status'] = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrlDpsReport()
    {
        return $this->data['urlDpsReport'];
    }

    /**
     * @param $url
     * @return LogMetadata
     */
    public function setUrlDpsReport($url)
    {
        $this->data['urlDpsReport'] = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrlRaidar()
    {
        return $this->data['urlRaidar'];
    }

    /**
     * @param $url
     * @return LogMetadata
     */
    public function setUrlRaidar($url)
    {
        $this->data['urlRaidar'] = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoss()
    {
        return $this->data['boss'];
    }

    /**
     * @param $boss
     * @return LogMetadata
     */
    public function setBoss($boss)
    {
        $this->data['boss'] = $boss;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBossId()
    {
        return $this->data['bossId'];
    }

    /**
     * @param $id
     * @return LogMetadata
     */
    public function setBossId($id)
    {
        $this->data['bossId'] = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEncounterTime()
    {
        return $this->data['encounterTime'];
    }

    /**
     * @param $time
     * @return LogMetadata
     */
    public function setEncounterTime($time)
    {
        $this->data['encounterTime'] = $time;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->data['tags'];
    }

    /**
     * @param $tag
     * @return bool
     */
    public function hasTag($tag)
    {
        return in_array($tag, $this->data['tags']);
    }

    /**
     * @param $tag
     * @return LogMetadata
     */
    public function addTag($tag)
    {
        $this->data['tags'][] = $tag;
        $this->data['tags']   = array_unique($this->data['tags']);
        sort($this->data['tags']);
        return $this;
    }

    /**
     * @param $tag
     * @return LogMetadata
     */
    public function removeTag($tag)
    {
        $this->data['tags'] = array_diff($this->data['tags'], [$tag]);
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function encounterTime()
    {
        $time = (string)$this->data['encounterTime'];
        if (ctype_digit($time) && $time > 1e9) {
            return new \DateTimeImmutable($time);
        }
        return $this->lastModified ?: new \DateTimeImmutable();
    }

    /**
     * @return LogMetadata
     */
    public function save()
    {
        Utils::writePhp($this->filename(), $this->data);
        $this->load();
        return $this;
    }

    /**
     * @param string $key
     * @param null   $default
     * @return null
     */
    public function get($key, $default = null)
    {
        return $this->data['CUSTOM'][$key] ?? $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data['CUSTOM'][$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    private function filename()
    {
        return $this->path . '/metadata.php';
    }

    /**
     *
     */
    public function load()
    {
        if (\is_file($this->filename())) {
            $metadata = include($this->filename());
            if (\is_array($metadata)) {
                foreach ($this->data as $key => $value) {
                    if (\array_key_exists($key, $metadata)) {
                        $this->data[$key] = $metadata[$key];
                    }
                }
                $this->lastModified = new \DateTimeImmutable('@' . filemtime($this->filename()));
            }
        }
    }
}