<?php
/**
 * desc: 一系列的小工具
 *       一个函数就是一个独立的功能
 *
 *
*/
class CTool {

    //get unique id
    static function getUnid($type=1)
    {
        $str = '~!@#$%^&*()_+={}[]|;:?,./abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($str);
        $randChars = '';
        for($i=0; $i<30; $i++){
            $n = rand(0, $len-1);
            $c = $str[$n];
            $randChars .= $c;
        }
        $randChars .= microtime() . uniqid();
        switch($type){
            case 0:
                return preg_replace('/\s+/s', '', $randChars);
            case 1:
                return sprintf("%u",crc32($randChars));
            case 2:
                return md5($randChars);
            case 3:
                return base64_encode($randChars);
            case 4:
                return rawurlencode($randChars);
            case 5:
                return sha1($randChars);
            default:
                return md5($randChars);
        }
    }
    /*
    * desc: 唯一id生成(理论上)
    *
    *
    */    
    static function uniqueId($len=12)
    {
        $id = uniqid(mt_rand(1000000,9999999), true);
        $id = hexdec($id);
        $id = number_format($id, 0, '', '');
        $id = str_shuffle($id);
        $id = number_format($id, 0, '', '');
        $id = preg_replace("/^0+/", '', $id);
        $id = substr($id, 0, $len);
        while(strlen($id) < $len) {
            $id .= mt_rand(1, 9);
        }
        return number_format($id, 0, '', '');
    }

    /*
    * desc: 按照时间来生成一个唯一id
    *
    *
    */
    static function realId($mlen=18, $prex=null, $truncated=false)
    {
        $id  = mt_rand(1000,9999).str_shuffle(str_replace('.', '', microtime(true)));
        if($mlen){
            $len = strlen($id);
            if($len<$mlen){
                for($i=0; $i<$mlen-$len; $i++){
                    $id .= mt_rand(0,9);
                }
            }
            if($truncated && $mlen<$len){
                $id = substr($id, 0, $mlen);
            }
        }
        if($prex && is_string($prex)){
            $id .= $prex;
        }
        return $id;
    }

    //将html中的相对路径转换为绝对路径
    static function relative2absolute($html, $domain)
    {
        preg_match('/(http|https|ftp):\/\//', $domain, $protocol); 
        $server_url = preg_replace("/(http|https|ftp|news):\/\//", "", $domain); 
        $server_url = preg_replace("/\/.*/", "", $server_url); 
        if ($server_url == '') { 
            return $html; 
        }
        if (isset($protocol[0])) { 
            $new_html = preg_replace('/href="\//', 'href="'.$protocol[0].$server_url.'/', $html); 
            $new_html = preg_replace('/src="\//', 'src="'.$protocol[0].$server_url.'/', $new_html); 
        }else { 
            $new_html = $html; 
        }
        return $new_html;
    }
    
    /**
    * author:cty@20111130
    * 获取当前毫秒级的时间
    * 用法:
    * $time_before = getUTime();
    *    中间代码...
    * $time_after = getUTime();
    * $Elapsed = sprintf("%.4f", $time_after - $time_before);
    *
    */
    static function getUTime()
    {
        list($usec, $sec) = explode(' ',microtime());
        $time   = ((float)$usec + (float)$sec);
        return $time;  
    }
    /**
    * desc: 将一个mysql数据库表记录按照某字段组装成tree格式(广度遍历)
    * call: table2tree($dataArr, 'f1,f2')
    *@fields  --- str 目前只支持一到两个字段
    *@dataArr --- array (
    *                       array(id=>1, fid=>10, gid=20, name=>...),
    *                       array(id=>2, fid=>10, gid=30, name=>...),
    *                   )
    *eg.
    *array(10=>array(
    *                   array(id=>1, fid=>10, gid=20, name=>...),
    *                   array(id=>2, fid=>10, gid=30, name=>...),
    *               ))
    *return void
    */
    static function table2tree(&$dataArr, $fields)
    {
        if(!is_array($dataArr)) return;
        $fArr   = explode(',', $fields);
        $field  = $fArr[0]; //当前字段
        unset($fArr[0]);
        $fields = implode(',', $fArr);
        $keyArr = array();
        //dataArr分组后存放到临时数组中(tmpArr)
        $tmpArr = array();
        foreach($dataArr as $row){
            if(!isset($row[$field])) break;
            $key = $row[$field];
            $keyArr[] = $key;
            $tmpArr[$key][] = $row;
        }
        $dataArr = $tmpArr;
        //end dataArr分组后存放到临时数组中(tmpArr)
        $keyArr = array_unique($keyArr);
        if(count($fArr) > 0){
            foreach($keyArr as $key){
                //广度遍历
                $data = &$dataArr[$key];
                self::table2tree($data, $fields);
            }
        }
    }
    /*
    * desc: 按照key打乱数组
    */
    static function kshuffle(&$array)
    {
        if(!is_array($array) || empty($array)) {
            return false;
        }
        $tmp = array();
        foreach($array as $key => $value) {
            $tmp[] = array('k' => $key, 'v' => $value);
        }
        shuffle($tmp);
        $array = array();
        foreach($tmp as $entry) {
            $array[$entry['k']] = $entry['v'];
        }
        return true;
    }
    static public function getMethods($obj)
    {
        if(is_resource($obj)) {
            print_r(get_class_methods($obj));
        }
    }
    //对象转数组
    static function object2array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val){
            $val = (is_array($val) || is_object($val)) ? self::object2array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    //数组转对象
    static function array2object($arr)
    {
        return new ArrayObject($arr);
    }
    /*
    * desc: 按照二维数组的某个键来排序
    *
    */
    static function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC)
    { 
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){ 
                if(is_array($row_array)){ 
                    $key_array[] = $row_array[$sort_key]; 
                }else{ 
                    // return false; 
                } 
            }
        }else{
            return false; 
        }
        array_multisort($key_array,$sort,$multi_array); 
        return $multi_array; 
    }
    
    /*
    * 数组递归合并(覆盖方式, 非array_merge_recursive的追加方式)
    *
    * @param array $arr1   数组一
    * @param array $arr2   数组二
    * @param array $arr..  数组...
    * @return array
    */
    static function array_merge_recursive_overwrite($arr1, $arr2)
    {
        $rs = $arr1;
        foreach(func_get_args() as $arr)
        {
            if(!is_array($arr)){
                return false;
            }
            foreach($arr as $key=>$val){
                $rs[$key] = isset($rs[$key]) ? $rs[$key] : array();
                $rs[$key] = is_array($val) ? self::array_merge_recursive_overwrite($rs[$key], $val) : $val;
            }
        }
        return $rs;
    }

    static function text2hex($text)
    {
        return bin2hex($text);
    }
    static function hex2text($text)
    {
        $plain = '';
        for($i=0,$max=strlen($text);$i<$max;$i++,$i++) {
            $plain .= chr(hexdec(substr($text, $i, 2))); 
        }
        return $plain;
    }

    /**
    * 取得离val最近的,比val大的且能被10或100...整除的那个数
    * @param int $val
    * @param bool $isfive,表示是否以50为模(eg.
    *  (50,100,150...)
    */
    static function UpTrimUnit($val, $isfive=TRUE)
    {
        //取得离val最近的,比val大的且能被10或100...整除的那个数
        $len = strlen(ceil($val));
        $val = ceil($val/(pow(10,$len-1))) * pow(10,$len-1);
        $val = ($val < 10)?10:$val;
        if($isfive) {
            $fiveMod = 5*pow(10,$len-1);
            //echo "fiveMod:$fiveMod($len,$val); <br/>";
            if($val%$fiveMod > 0){
                $val = $val + ($fiveMod - ($val%$fiveMod));
            }
        }
        return $val;
    }

    static function RemoveSlashes($str)
    {
        do{
            $len1 = strlen($str);
            $str  = stripslashes($str);
            $len2 = strlen($str);
        }while($len1 > $len2);
        return $str;
    }
};
