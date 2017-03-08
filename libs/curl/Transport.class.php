<?php
namespace Libs\Curl;

class Transport {

    static public $multiRequestType = 'multicurl';

    public static function exec(Array $requestList, Array $opt = array()) {
        if (empty($requestList)) {
            return array();
        }
        foreach ($requestList as $request) {
            $comOpt = self::combinOptions($request->opt, $opt);
            $request->setOptions($comOpt);
        }
        if (count($requestList) > 1) {
            $responseList = self::multiRequest($requestList, self::$multiRequestType);
        }
        else {
            $responseList = self::curlExec($requestList);
        }
        return $responseList;
    }

    private static function combinOptions($opt, $additionOpt) {
        foreach ($additionOpt as $type => $value) {
            if (isset($opt[$type])) {
                $opt[$type] = $value;
            }
        }
        return $opt;
    }

    private static function multiRequest($requestList, $type) {
        if (empty($requestList)) {
            return array();
        }
        return self::multiCurlExec($requestList);
    }

    private static function multiCurlExec($requestList) {
        MultiCurl::instance()->open();
        MultiCurl::instance()->send($requestList);
        $responseList = MultiCurl::instance()->exec();
        MultiCurl::instance()->close();
        return $responseList;
    }

    private static function curlExec($requestList) {
        $responseList = array();
        foreach ($requestList as $key => $request) {
            SingleCurl::instance()->open();
            SingleCurl::instance()->send($request);
            $responseList[$key] = SingleCurl::instance()->exec();
            SingleCurl::instance()->close();
        }
        return $responseList;
    }
}
