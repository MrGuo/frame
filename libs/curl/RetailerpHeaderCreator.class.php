<?php
namespace Libs\Curl;

class RetailerpHeaderCreator {

    private static $header = array('Auth' => 'merchant:0;account:0');
    private static $info = array('merchant' => 0, 'account' => 0);

    public static function getHeaders() {
        self::$header = array(
            'Auth' => 
                "merchant:" . self::$info['merchant'] . 
                ";account:" . self::$info['account'],
        );
        return self::$header;
    }

    public static function setInfos($info) {
        self::$info = $info;
    }

}
