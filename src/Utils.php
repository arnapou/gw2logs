<?php


namespace App;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\PhpFilesCache;

class Utils
{
    /**
     * @var CacheInterface
     */
    private static $cache = null;

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public static function jsonDecode($data)
    {
        $json = trim($data);
        if ($json === '') {
            throw new \Exception('Json empty');
        }
        if ($json[0] !== '{' && $json[0] !== '[' && $json[0] !== '"') {
            throw new \Exception('Json not valid');
        }
        $array         = \json_decode($json, true);
        $jsonLastError = \json_last_error();
        if ($jsonLastError !== JSON_ERROR_NONE) {
            $errors = [
                JSON_ERROR_DEPTH            => 'Max depth reached.',
                JSON_ERROR_STATE_MISMATCH   => 'Mismatch modes or underflow.',
                JSON_ERROR_CTRL_CHAR        => 'Character control error.',
                JSON_ERROR_SYNTAX           => 'Malformed JSON.',
                JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, probably charset problem.',
                JSON_ERROR_RECURSION        => 'Recursion detected.',
                JSON_ERROR_INF_OR_NAN       => 'Inf or NaN',
                JSON_ERROR_UNSUPPORTED_TYPE => 'Unsupported type.',
            ];
            throw new \Exception('Json error : ' . ($errors[$jsonLastError] ?? 'Unknown error'));
        }
        return $array;
    }

    /**
     * @param string $method
     * @param string $url
     * @param null   $body
     * @param array  $headers
     * @return mixed
     */
    public static function curl($method, $url, $body = null, $headers = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!\in_array($method, ['GET', 'POST'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result   = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

//        if ($httpcode != 200) {
        ////            print_r($result);
//            throw new \RuntimeException("Http return code = $httpcode");
//        } else {
        return self::jsonDecode($result);
//        }
    }

    /**
     * @param string   $key
     * @param callable $callable
     * @param int      $ttl
     * @return mixed|null
     */
    public static function cached($key, $callable, $ttl)
    {
        if (!self::cache()->has($key)) {
            $value = $callable();
            self::cache()->set($key, $value, $ttl);
            return $value;
        }
        return self::cache()->get($key);
    }

    /**
     * @return CacheInterface
     */
    protected static function cache()
    {
        if (self::$cache === null) {
            self::$cache = new PhpFilesCache();
        }
        return self::$cache;
    }

    /**
     * @param $filename
     * @param $content
     */
    public static function writeFile($filename, $content)
    {
        file_put_contents($filename, $content, LOCK_EX);
        chmod($filename, 0777);
    }

    /**
     * @param $filename
     * @param $content
     */
    public static function writeJson($filename, $content)
    {
        self::writeFile($filename, json_encode($content, JSON_PRETTY_PRINT));
    }

    /**
     * @param $filename
     * @param $content
     */
    public static function writePhp($filename, $content)
    {
        self::writeFile($filename, '<?php return ' . var_export($content, true) . ";\n");
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $default
     * @return int
     */
    public static function validInteger($value, $min, $default)
    {
        if (!ctype_digit("$value")) {
            return $default;
        }
        if ($value < $min) {
            return $min;
        }
        return $value;
    }
}
