<?php
/**
* author: cty@20121012
*   desc: 过滤字符串处理类
*
* 
*/
class CFilter {

    /*
    * desc: 整形化
    *       abc123 --->123
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftInt($str)
    {
        if(is_scalar($str)){
            $str = intval($str);
        }
        return $str;
    }
    
    /*
    * desc: 浮点型化
    *       abc123.12 --->123.12
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftFloat($str)
    {
        if(is_scalar($str)){
            $str = floatval($str);
        }
        return $str;
    }
    
    /*
    * desc: 过滤script标签
    *       $str = "abc<script ...></script>123"; ---> $str = "abc123"
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftScript($str)
    {
        return preg_replace("/<script.*?>.*?<.*?script>/si", '', $str);
    }
    
    /*
    * desc: 过滤所有标签
    *       <a href='http://..'>abc</a> ---> abc
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftTag($str)
    {
        return preg_replace("/<.*?>/si", '', $str);
    }

    /*
    * desc: 相当于php的trim函数
    *       <a href='http://..'>abc</a> ---> abc
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftTrim($str)
    {
        return trim($str);
    }

    /*
    * desc: 过滤中文
    *       $str = "abc123_测试abc"; ---> $str = "abc123_abc"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftChinese($str)   //[a-z0-9_]
    {
        if(is_scalar($str)){
            $str = preg_replace("[\x80-\xff]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 过滤字母
    *       abc123 --->123
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftLetter($str)
    {
        if(is_scalar($str)){
            $str = preg_replace("/[a-z]/si", '', $str);
        }
        return $str;
    }
    
    /*
    * desc: 过滤娄字
    *       abc123 --->abc
    *@str --- str 应是一个标量
    *return: string
    */
    static function ftNumber($str)
    {
        if(is_scalar($str)){
            $str = preg_replace("/[0-9]/si", '', $str);
        }
        return $str;
    }

    /*
    * desc: 自定义正则表达式的过滤
    *       $str = "abc123"; ---> $str = "abc"
    *@patt --- string
    *@str  --- 应是一个标量
    *return: string
    */
    static function ftPatt($patt, $str)          //patt
    {
        if(is_scalar($patt)){
            $str = preg_replace("$patt", '', $str);
        }
        return $str;
    }
};
