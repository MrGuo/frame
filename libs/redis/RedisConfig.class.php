<?php
namespace Libs\Redis;

class RedisConfig {
    private $configs;

    public static function instance() {
        static $cf = NULL;
        is_null($cf) && $cf = new RedisConfig();
        return $cf;
    }

    private function __construct() {
        $this->configs = \Frame\ConfigFilter::instance()->getConfig('redis');
    }

    public function loadConfig() {
        return $this->configs;
    }
}
