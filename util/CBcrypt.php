<?php
/**
 * desc: php crypt加密
 * 
 * call:
 *      1,echo $salt = CBcrypt::hashPassword("123456");
 *      2,CBcrypt::verifyPassword('123456', $salt);
 * 
 * 
*/

class CBcrypt {

    private static $_identifier = '2y';
    
    /*
    * desc: 哈希密码
    *
    */
    public static function hashPassword($password)
    {
        $salt = self::createSalt();
        return crypt($password, $salt);
    }
    /*
    * desc: 校验密码
    *@password 
    */
    public static function verifyPassword($password, $passwordcypher)
    {
        $checkHash = crypt($password, $passwordcypher);
        return ($checkHash === $passwordcypher);
    }
    /*
    * desc: 生成salt
    *
    */
    public static function createSalt()
    {
        $input = openssl_random_pseudo_bytes(16);
        $salt  = '$' . self::$_identifier . '$';
        $salt .= str_pad(12, 2, '0', STR_PAD_LEFT);
        $salt .= '$';
        $salt .= substr(strtr(base64_encode($input), '+', '.'), 0, 22);
        return $salt;
    }
}
