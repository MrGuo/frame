<?php
namespace Libs\Curl;

class ApiList {

    public static function get($server, $api) {
        $apiInfo = array('service' => 'wallet', 'method' => 'POST');
        if (isset(static::$apiList[$api])) {
            $apiInfo = static::$apiList[$api];
        }
        return $apiInfo;
    }
}
