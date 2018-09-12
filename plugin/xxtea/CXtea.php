<?php

require_once(__DIR__.'/xxtea.php');

class CXtea{
    private static $encryptkey = 'knW0knW0knW0knW0';

    static function encrypt($plain, $tosp=true)
    {
        $cypher = base64_encode(xxtea_encrypt($plain, self::$encryptkey));
        if($tosp){
            $cypher = str_replace('+', '-', $cypher);
            $cypher = str_replace('/', '_', $cypher);
            $cypher = str_replace('=', '.', $cypher);
        }
        return $cypher;
    }

    static function decrypt($base64, $hassp=true)
    {
        if($hassp){
            $base64 = str_replace('-', '+', $base64);
            $base64 = str_replace('_', '/', $base64);
            $base64 = str_replace('.', '=', $base64);
        }
        return xxtea_decrypt(base64_decode($base64), self::$encryptkey);
    }
}