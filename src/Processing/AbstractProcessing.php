<?php


namespace App\Processing;


use App\Log;
use App\Utils;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\PhpFilesCache;

abstract class AbstractProcessing
{
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct()
    {
        $this->cache = new PhpFilesCache();
    }

    /**
     * @param string $method
     * @param string $url
     * @param null   $body
     * @param array  $headers
     * @return mixed
     * @throws \Exception
     */
    protected function curl($method, $url, $body = null, $headers = [])
    {
        return Utils::curl($method, $url, $body, $headers);
    }

    /**
     * @param string   $key
     * @param callable $callable
     * @param int      $ttl
     * @return mixed|null
     */
    protected function cached($key, $callable, $ttl)
    {
        if (!$this->cache->has($key)) {
            $value = $callable();
            $this->cache->set($key, $value, $ttl);
            return $value;
        }
        return $this->cache->get($key);
    }

    /**
     * @param Log $log
     */
    abstract public function process(Log $log);

    /**
     * @return string
     */
    abstract public function getTagName();

}