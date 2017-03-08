<?php

namespace Libs\Util;

class Utilities {

    public static function DataToArray($dbData, $keyword, $allowEmpty = FALSE) {
        $retArray = array ();
        if (is_array ( $dbData ) == false or empty ( $dbData )) {
            return $retArray;
        }
        foreach ( $dbData as $oneData ) {
            if (isset ( $oneData [$keyword] ) and empty ( $oneData [$keyword] ) == false or $allowEmpty) {
                $retArray [] = $oneData [$keyword];
            }
        }
        return $retArray;
    }


    public static function changeHashKey($data, $hashKey, $isMulti = false)
    {
        $retHash = array();
        foreach ($data as $value) {
            if ($isMulti) {
                $retHash[$value[$hashKey]][] = $value;
            } else {
                $retHash[$value[$hashKey]] = $value;
            }
        }
        return $retHash;
    }

    public static function changeDataKeys($data, $keyName, $toLowerCase=false) {
        $resArr = array ();
        if(empty($data)){
            return false;
        }
        foreach ( $data as $v ) {
            $k = $v [$keyName];
            if( $toLowerCase === true ) {
                $k = strtolower($k);
            }
            if(empty($k)) {
                continue;
            }
            $resArr [$k] = $v;
        }
        return $resArr;
    }
	
    public static function sortArray($array, $order_by, $order_type = 'ASC') {
        if (!is_array($array)) {
            return array();
        }
        $order_type = strtoupper($order_type);
        if ($order_type != 'DESC') {
            $order_type = SORT_ASC;
        } else {
            $order_type = SORT_DESC;
        }

        $order_by_array = array ();
        foreach ( $array as $k => $v ) {
            $order_by_array [] = $array [$k] [$order_by];
        }
        array_multisort($order_by_array, $order_type, $array);
        return $array;
    }

    public static function nginx_userid_decode($str) {
        $str_unpacked = unpack('h*', base64_decode(str_replace(' ', '+', $str)));
        $str_split = str_split(current($str_unpacked), 8);
        $str_map = array_map('strrev', $str_split);
        $str_dedoded = strtoupper(implode('', $str_map));

        return $str_dedoded;
    }
	
	/**
	 * Get the real remote client's IP
	 *
	 * @return string
	 */
	public static function getClientIP() {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) && $_SERVER ['HTTP_X_FORWARDED_FOR'] != '127.0.0.1') {
			$ips = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			$ip = $ips [0];
		} elseif (isset ( $_SERVER ['HTTP_X_REAL_IP'] )) {
			$ip = $_SERVER ['HTTP_X_REAL_IP'];
		} elseif (isset ( $_SERVER ['HTTP_CLIENTIP'] )) {
			$ip = $_SERVER ['HTTP_CLIENTIP'];
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = '127.0.0.1';
		}
		
		$pos = strpos ( $ip, ',' );
		if ($pos > 0) {
			$ip = substr ( $ip, 0, $pos );
		}
		
		$pos = strpos($ip, ':');
	        if($pos > 0){
	            $ip = substr ($ip, 0, $pos);
	        }
		
		return trim ( $ip );
	}

    public static function xml2Array($xml) {
        if (!$xml) {
            return array();
        }
        libxml_disable_entity_loader(TRUE);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);
    }

}
