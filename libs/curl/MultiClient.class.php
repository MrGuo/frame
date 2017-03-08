<?php
namespace Libs\Curl;

class MultiClient {

    private $requestList = array();
    private $responseList = array();
    private $options = array();

    public function call($service, $apiName, $params, $callback, $opt = array()) {
        $request = Request::newInstance();
        $request->setApi($service, $apiName);
        $request->setParam($params);
        $request->setOptions($opt);
        $this->requestList[$callback] = $request;
    }

    public function callData() {
        $this->responseList = Transport::exec($this->requestList, $this->options);
        $this->requestList = array();
        return $this->responseList;
    }

    public function __get($callback) {
        if (isset($this->responseList[$callback])) {
            return $this->responseList[$callback];
        }
        return '';
    }
}
