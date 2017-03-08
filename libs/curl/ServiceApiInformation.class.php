<?php
namespace Libs\Curl;

class ServiceApiInformation {

    private static function getApiInfo($server, $api = '') {
        $api = strtolower($api);
        $className = __NAMESPACE__ . '\\' . ucfirst($server) . 'ApiList';
        if (class_exists($className)) {
            return call_user_func_array(array($className, 'get'), array($server, $api));
        }
        return array();
    }
    
    public static function getApiMethod($server, $api = '') {
        $apiInfo = self::getApiInfo($server, $api);
        return strtoupper($apiInfo['method']);
    }

    public static function getApiUrl($server, $api) {
        return self::getServiceHost($server) . $api;
    }

    public static function getApiOpt($server, $api = '') {
        $apiInfo = self::getApiInfo($server, $api);
        return isset($apiInfo)&& isset($apiInfo['opt'])?$apiInfo['opt']:'';
    }

    private static function getServiceHost($remote) {
        // $config = \common\phplib\Config::load('Remote');
        // $hosts = $config->$remote;
        $hosts = array(
            // 'http://demo.dev/'
            'http://121.42.188.44:8050/'
        );
        if(is_array($hosts)) {
            return $hosts[array_rand($hosts)];
        }
        else {
            return $hosts;
        }
    }
}
