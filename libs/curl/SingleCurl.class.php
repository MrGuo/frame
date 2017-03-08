<?php
namespace Libs\Curl;

class SingleCurl {

    const DefaultTimeout = 2;  //默认接口超时时间
    const DefaultTimoutConn = 1; //默认连接时间

    private static $instance = NULL;
    private $file = "singlecurl";

    public static function instance() {
        is_null(self::$instance) && self::$instance = new self();
        return self::$instance;
    }

    private $useragent = 'Retailerp';
    private $curlHandle = NULL;

    public function open() {
        $this->curlHandle = curl_init();
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->curlHandle, CURLOPT_HEADER, FALSE);
        curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, 1);
    }

    private function getHeaders() {
        $headers = RetailerpHeaderCreator::getHeaders();
        $headerArr = array('Auth:' . $headers['Auth']);
        return $headerArr;
    }

    public function send($request) {
        $params = http_build_query($request->params);
        $url = $request->url;
        $method = $request->method;
        switch ($method) {
            case 'POST':
                curl_setopt($this->curlHandle, CURLOPT_POST, TRUE);
                curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $params);
                break;
            case 'GET':
                curl_setopt($this->curlHandle, CURLOPT_HTTPGET, TRUE);
                $url .= '?' . $params;
                break;
        }
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);

        $this->setopt($request);
    }

    public function setopt($request) {
        $options = $request->opt;
        if (empty($options['timeout'])) {
            $options['timeout'] = self::DefaultTimeout;
        }
        if (empty($options['connect_timeout'])) {
            $options['connect_timeout'] = self::DefaultTimoutConn;
        }
        foreach ($options as $type => $value) {
            switch ($type) {
                case 'timeout':
                    curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $value);
                    break;
                case 'connect_timeout':
                    curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, $value);
                    break;
                case 'timeout_ms':
                    curl_setopt($this->curlHandle, CURLOPT_TIMEOUT_MS, $value);
                    break;
                case 'connect_timeout_ms':
                    curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT_MS, $value);
                    break;
            }
        }
    }

    public function exec() {
        $response = curl_exec($this->curlHandle);
        $res = array();
        $res['httpcode'] = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        $res['content'] = json_decode($response, TRUE);
        return $res;
    }

    public function close() {
        curl_close($this->curlHandle);
        $this->curlHandle = NULL;
    }
}
