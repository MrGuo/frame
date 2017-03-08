<?php
namespace Libs\Redis;

use Libs\Log\Liblog;

class Redis {

    /**
     * Holds initialized Redis connections.
     *
     * @var array
     */
    protected static $connections = array();
    protected static $timeout = 0.2;
    protected static $config = NULL;

    /**
     * By default the prefix of Redis key is the same as class name. But it
     * can be specified manually.
     *
     * @var string
     */
    protected static $prefix = NULL;
    //TODO-refactor
    protected static $debug = 1;

    /**
     * the server_id of the Redis Cluster
     *
     * @var int
     */

    protected static function setTimeout($timeout) {
        self::$timeout = $timeout;
    }

    /**
     * Initialize a Redis connection.
     */
    protected static function connect($host, $port, $timeout = 0.2, $auth = '') {
        $redis = new \Redis();
        $redis->connect($host, $port, $timeout);
        if (!empty($auth)) {
            $redis->auth($auth);
        }
        if ($redis->isConnected()) {
            $redis->setOption(\Redis::OPT_READ_TIMEOUT, $timeout);
        }
        ////alias $redis->GetReadTimeout(); 
        //$ret = $redis->getOption(\Redis::OPT_READ_TIMEOUT);
        return $redis;
    }

    /**
     * Get an initialized Redis connection according to the key.
     */
    public static function getRedis($key = NULL, $reconnect = FALSE) {
        static $config = NULL;
        $class = get_called_class();
        // is_null($config) && $config = array('servers' => array(array('host' => '127.0.0.1', 'port' => 6379)));
        $config = self::loadConfig();

        $servers = $config['servers'];
        $count = count($servers);
        $server_id = is_null($key) ? 0 : (hexdec(substr(md5($key), 0, 2)) % $count);
        $host = $servers[$server_id]['host'];
        $port = $servers[$server_id]['port'];
        $auth = isset($servers[$server_id]['auth']) ? $servers[$server_id]['auth'] : '';
        $connect_index = $host . ':' . $port;
        $is_connected = TRUE;

        if (!isset(self::$connections[$connect_index])) {
            self::$connections[$connect_index] = self::connect($host, $port, $class::$timeout, $auth);
            $is_connected = self::$connections[$connect_index]->isConnected();
            if (!$is_connected && !$reconnect) {
                unset(self::$connections[$connect_index]);
                return self::getRedis($key, TRUE);
            }
        }

        if (!$is_connected) {
            LibLog::error("connect redis failure host:{$host},port:{$port}");
            throw new \Exception ("Redis server went away");
        }
        return self::$connections[$connect_index];
    }

    protected static function loadConfig() {
        is_null(self::$config) && self::$config = RedisConfig::instance()->loadConfig();
        return self::$config;
    }

    protected static function getPrefix() {
        $class = get_called_class();
        if (!is_null($class::$prefix)) {
            return $class::$prefix;
        }
        return get_called_class();
    }


    private static $transMethod = array(
        'multi' => 1, 'exec' => 1, 'unwatch' => 1, 'discard' => 1,
    );

    protected static $multiProcesser = NULL;

    public static function exec() {
        $ret = self::$multiProcesser->exec();
        self::$multiProcesser = null;
        return $ret;
    }

    public static function __callStatic($method, $args) {
        $class = get_called_class();
        $name = trim($args[0]);
        $key = self::_getKey($name);
        $args[0] = $key;
        try {
            $timer = array();
            $start = microtime(1);
            $redis = $class::getRedis($key); 
            $timer['connect'] = round(microtime(1) - $start, 3) * 1000;
            $start = microtime(1);
            $result = call_user_func_array(array($redis, $method), $args);
            Liblog::trace("redis exec method:{$method};args" . json_encode($args));
            $timer['exec'] = round(microtime(1) - $start, 3) * 1000;
            if ($timer['connect'] > 100 || $timer['exec'] > 100) {
                Liblog::warning('redis timer warning param=' . json_encode($timer) . ";method:$method;args" . json_encode($args));
            }
        }
        catch (\Exception $e) {
            Liblog::error('redis proc error param=' . "method:$method;args" . json_encode($args) . ";" . $e->getMessage());
            $result = NULL;
        }
        return $result;
    }

    public static function _getKey($name) {
        $class = get_called_class ();
        $prefix = $class::getPrefix ();
        $key = "{$prefix}{$name}";
        return $key;
    }

}


