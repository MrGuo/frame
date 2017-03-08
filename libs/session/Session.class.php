<?php
namespace Libs\Session;

class Session {

    protected $session = array();
    protected $ticket = '';

    public function __construct() {
        $this->load();
    }

    protected function ticket() {
        return '';
    }

    protected function args($field) {
        return isset($_COOKIE[$field]) ? $_COOKIE[$field] : (isset($_REQUEST[$field]) ? $_REQUEST[$field] : '');
    } 

    public function __get($arg) {
        if (isset($this->session[$arg])) {
            return $this->session[$arg];
        }
        elseif ($arg == 'session') {
            return $this->session;
        }
    }
}
