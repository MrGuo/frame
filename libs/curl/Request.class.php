<?php
namespace Libs\Curl;

class Request {

    private $url = '';
    private $method = 'GET';
    private $params = array();
    private $opt = array();

    public static function newInstance() {
        return new self();
    }

    public function setApi($server, $apiName) {
        $this->url = ServiceApiInformation::getApiUrl($server, $apiName);
        $this->method = ServiceApiInformation::getApiMethod($server, $apiName);
        $this->opt = ServiceApiInformation::getApiOpt($server, $apiName);
    }

    public function setParam($params) {
        $this->params = $params;
    }

    public function setOptions(Array $opt) {
        foreach ($opt as $key => $value) {
            $this->setopt($key, $value);
        }
    }

    private function setopt($type, $value) {
        switch ($type) {
            case 'timeout':
                $this->opt['timeout'] = (int)$value;
                break;
            case 'connect_timeout':
                $this->opt['connect_timeout'] = (int)$value;
                break;
            case 'timeout_ms':
                $this->opt['timeout_ms'] = (int)$value;
                break;
            case 'connect_timeout_ms':
                $this->opt['connect_timeout_ms'] = (int)$value;
                break;
        }
    }

    public function __get($type) {
        if (isset($this->$type)) {
           return $this->$type;
        }
        else {
            return array();
        }

    }
}
