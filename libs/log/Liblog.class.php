<?php
namespace Libs\Log;

class Liblog
{

    const LOG_PATH = '/data/wwwlogs/log/';
    const SPERATE = '|';
    const REPLACE = '_';
    const APP = 'default';

    /**
     * debug临时日志
     * @param $msg
     * @param array $extData
     * @param string $application
     */
    static public function trace($msg, $extData = array(), $application = self::APP)
    {
        self::log('trace', $msg, $application, $extData);
    }

    /**
     * 记录紧急错误日志
     * @param $msg
     * @param array $extData
     * @param string $application
     */
    static public function error($msg, $extData = array(), $application = self::APP)
    {
        self::log('error', $msg, $application, $extData);
    }

    /**
     * 记录错误日志
     * @param $msg
     * @param array $extData
     * @param string $application
     */
    static public function warning($msg, $extData = array(), $application = self::APP)
    {
        self::log('warning', $msg, $application, $extData);
    }

    /**
     * 记录一些警告日志
     * @param $msg
     * @param array $extData
     * @param string $application
     */
    static public function notice($msg, $extData = array(), $application = self::APP)
    {
        self::log('notice', $msg, $application, $extData);
    }

    /**
     * 记录流水日志／一般
     * @param $msg
     * @param array $extData
     * @param string $application
     */
    static public function info($msg, $extData = array(), $application = self::APP)
    {
        self::log('info', $msg, $application, $extData);
    }

    /**
     * log方法
     * @param $logMode
     * @param $str
     * @param $application
     * @param $extData
     * @return bool
     */
    static protected function log($logMode, $str, $application, $extData)
    {
        $path = self::LOG_PATH . $logMode . '/' . $application;
        if (empty($str) || !is_string($str)) {
            return false;
        }
        if (!self::createPath($path)) {
            return false;
        }
        $extData = serialize($extData);
        $file = $path . '/' . date("YmdH") . '.log';
        $msg = array(
            'time' => date("Y-m-d H:i:s"),
            'msg' => str_replace(self::SPERATE, self::REPLACE, $str),
            'ext_data' => str_replace(self::SPERATE, self::REPLACE, $extData),
            'pid' => getmypid(),
        );
        if ($logMode == 'error' || $logMode == 'warning') {
            $msg['msg'] = nl2br($msg['msg']);
        }
        @file_put_contents($file, implode(' ' . self::SPERATE . ' ', $msg) . "\n", FILE_APPEND);
        return true;
    }

    /**
     * 创建路径
     * @param $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    static protected function createPath($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        try {
            if (!mkdir($path, $mode, $recursive)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}

