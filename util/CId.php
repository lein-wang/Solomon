<?php
/**
 * desc: id构造器
 * id艺术
 * 品牌id:10000         - 99999        ( 5位)
 * 系列id:10000         - 99999        ( 5位)
 * 商家id:1000000       - 9999999      ( 7位)
 * 产品id:100000000     - 999999999    ( 9位)不属于任何商家
 * 商品id:10000000000   - 99999999999  (11位)属于某一商家
 * 订单id:1000000000000 - 4999999999999(13位)
 * 交易id:5000000000000 - 9999999999999(13位)
 * 分类id:
 *     系统分类
 *      10-99:一级
 *          1000-9999:二级
 *              10^(level-1)-(10^(level)-1):N级
 *     品牌分类
 *      自增
 *
*/
class CId {
    static function MakeID($n=10)
    {
        $min = pow(10, $n - 1);
        $max = pow(10, $n) - 1;
        return number_format(mt_rand($min, $max),0,'','');
    }
    //商品
    static function MakeGoodsID()
    {
        return self::MakeID(9); //9位
    }
    //商家
    static function MakeStoreID()
    {
        return self::MakeID(7);; //7位
    }
    //品牌
    static function MakeBrandID()
    {
        return self::MakeID(5);   //5位
    }
    static function MakeSerieID()
    {
        return self::MakeID(10); //10位
    }
    //品牌下的分类
    static function MakeBrandCateID()
    {
        return self::MakeID(7);   //7位
    }
};
