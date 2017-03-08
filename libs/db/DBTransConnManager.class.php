<?php
namespace Libs\DB;

/**
 * beginTransaction()
 * do imprortant stuff
 * call method
 *     beginTransaction()
 *        basic stuff 1
 *        basic stuff 2
 *     commit()
 * do most important stuff
 * commit()
 *
 * Won't work and is dangerous since you could close your transaction too early with the nested
 * commit().
 */
class DBTransConnManager extends DBConnManager {

    public static function getConn($database) {
        static $singletons = array();
        if (isset($singletons[$database])) {
            return $singletons[$database];
        }
        $readRetry = 1;
        $writeRetry = 1;
        $connectRetry = 1;
        $singletons[$database] = new DBTransConnManager($database, $readRetry, $writeRetry, $connectRetry);
        return $singletons[$database];
    }

    protected $transactionCounter = 0;
    protected $myconn = NULL;

    public function beginTransaction() {
        if (!$this->transactionCounter++) {
            $this->myconn = $this->getConnection('MASTER');
            return $this->myconn->beginTransaction();
        }
        return $this->transactionCounter >= 0;
    }

    public function commit() {
        if (!--$this->transactionCounter) {
            return $this->myconn->commit();
        }
        return $this->transactionCounter >= 0;
    }

    public function rollback() {
        if ($this->transactionCounter >= 0) {
            $this->transactionCounter = 0;
            return $this->myconn->rollback();
        }
        $this->transactionCounter = 0;
        return FALSE;
    }
}
