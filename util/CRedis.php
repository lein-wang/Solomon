<?php
/**
 * desc: php redis class
 *
 * call: $redis = new CRedis();
 *       $redis->set('k1', 'v', 20);
 *
 *
 *
*/
class CRedis extends Redis{

    private $ip   = null;   //[127.0.0.1]
    private $port = null;   //[6379]
    private $auth = null;   //null

    function __construct($ip='127.0.0.1', $port=6379, $auth=null, $db=1)
    {
        $this->ip   = $ip;
        $this->port = $port;
        $this->auth = $auth;
        // return $this->_get_redis();
        parent::connect($this->ip, $this->port);
        if($this->auth){
            parent::auth($auth);
        }
//        if($db){
            $this->select($db);
//        }
    }
    function __destruct()
    {
        parent::__destruct();
        parent::close();
    }

    public function select($db=0)
    {
        parent::select(intval($db));
        return $this;
    }

    /**
     * 设置值  构建一个字符串
     * @param string $key KEY名称
     * @param string $val  设置值
     * @param int $expired 时间  0表示无过期时间
    */
    public function set($key, $val, $expired=0)
    {
        $val = is_array($val)?json_encode($val):$val;
        $result = parent::set($key, $val);
        if ($expired >= 0){
            $this->expire($key, $expired);
        }
        return $result;
    }
    public function get($key)
    {
        $v = parent::get($key);
        $vj = json_decode($v, true);
        return $vj?$vj:$v;
    }

    /*
    * desc: hash
    *@key     --- string KEY名称
    *@field   --- string 设置值
    *@val     --- mix  设置值
    *@expired --- int 0表示无过期时间
    *@isnx    --- bool 是否用hsetnx
    */
    public function hset($key, $field, $val, $expired=0, $isnx=false)
    {
        $val = is_array($val)?json_encode($val):$val;
        if($isnx){
            $result = parent::hSetNx($key, $field, $val);
        }else{
            $result = parent::hSet($key, $field, $val);
        }
        if($expired>0){
            $this->ttl($key, $expired);
        }
        return $result;
    }
    public function hget($key, $field, $jsoned=true)
    {
        $val = parent::hGet($key, $field);
        if($jsoned){
            $val = json_decode($val, true);
        }
        return $val;
    }

    public function hsetnx($key, $field, $val, $expired=0)
    {
        return $this->hset($key, $field, $val, $expired, true);
    }

    public function ttl($key, $expired = -1)
    {
        return $this->expire($key, $expired);
    }
    /**
     * set
     * @param string $key
     * @param string $val
     * @param int $expired
     * @return int
     */
    public function sAdd($key,$val,$expired=60) {
        parent::sAdd($key,$val);
        return $this->expire($key, $expired);
    }
    /**
     * 模糊匹配key，遍历删除
     * @param string $wildkey
     * @return boolean
     */
    public function mdel($wildkey){
        $keys = parent::keys($wildkey);
        if(empty($keys)){
            return false;
        }
        foreach ($keys as $k) {
            parent::del($k);
        }
        return true;
    }
    
};
