<?php
/**
 * desc: 一系列的小函数；
 *       一个函数就是一个独立的功能；
 *
*/
class CKit {

    /*
    * desc: 解析身份证
    * CEnv::parseIdCard('aaaaaaaa')
    *@idcard --- str 15|18位身份证号码
    *@strict --- bool 严格模式检查
    *
    * return array(...)
    */
    static function parseIdCard($idcard, $strict=true)
    {
        $len   = strlen($idcard);
        if(18 == $len){
            $birth = substr($idcard, 6, 8);
            $year  = intval(substr($birth, 0, 4));
            $month = intval(substr($birth, 4, 2));
            $day   = intval(substr($birth, 6, 2));
        }else{
            $birth = substr($idcard, 6, 6);
            $year  = intval(substr($birth, 0, 2));
            $month = intval(substr($birth, 2, 2));
            $day   = intval(substr($birth, 4, 2));
        }
        $age   = intval(date('Y')) - $year;
        if(18 != $len){
            $age = substr($age, -2);
        }
        $sex    = intval(substr($idcard, 18==$len?-2:-1, 1))%2;
        $retArr = array(
            'status'  => true,
            'message' => '',
            'prex'    => substr($idcard, 0, 6),
            'birth'   => $birth,
            'suff'    => substr($idcard, -4),
            'age'     => $age,
            'sex'     => $sex,
        );
        if(!$month || $month>12){
            $retArr['status']  = false;
            $retArr['message'] = '生日月分不合法';
        }elseif(!$day || $day>31 || $age<0){
            $retArr['status']  = false;
            $retArr['message'] = '生日不合法';
            if($age < 0 && $strict){
                $retArr['age'] = 0;
            }
        }elseif(15!=$len && 18!=$len){
            $retArr['status']  = false;
            $retArr['message'] = '无效身份证';
        }
        return $retArr;
    }

    /*
    * desc: change a version string to a numeric
    * "0.1"     => "0.1"
    * "5"       => "5"
    * "5.1.0"   => "5.10"
    * "5.0.2"   => "5.02"
    * "5.11.23" => "5.92995" 
    * "5.9.10"  => "5.9091"
    * "5.9.18"  => "5.9099"
    * "5.22"    => "5.994"
    * "5.1.36"  => "5.994"
    */
    static function version2numeric($version)
    {
        $subArr   = explode('.', $version);
        $main_ver = array_shift($subArr); //主版本
        if(!$subArr)return floatval($main_ver);
        $subverstring = '';
        foreach($subArr as $sub_ver){
            $sub_ver       = intval($sub_ver); //把当前字版本号变为整型数值
            $subverstring .= str_repeat('9',$sub_ver/9); //字符'9'的个数
            $remainder     = $sub_ver%9; //当前字版本号的余数(把它接到后面)
            $subverstring .= (string)$remainder;
        }
        return floatval($main_ver .'.'. $subverstring);
    }
};