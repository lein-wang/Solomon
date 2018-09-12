<?php
/**
 * desc: mb-string操作相关
 *
 *
 *
 *
*/
class CMb {

    //mb取子字符串
    static function mbSub($str, $num, $offset=0)
    {
        if(!function_exists("mb_substr")) return $str;
        $encodeArr = array('UTF-8', 'gbk', 'gb2312', 'CP936', 'ascii');
        $encode = mb_detect_encoding($str, $encodeArr);
        $len    = mb_strlen($str, $encode); 
        // if($len <= $num) return $str;
        $substr = mb_substr($str, $offset, $num, $encode);
        return $substr;
    }
    //同mbSub
    static function mbCut($str, $num, $offset=0)
    {
        return self::mbSub($str, $num, $offset);
    }
    //mb取子字符串字符个数
    static function mbLen($str)
    {
        if(!function_exists("mb_substr")) return strlen($str);
        $encodeArr = array('UTF-8', 'gbk', 'gb2312', 'CP936', 'ascii');
        $encode = mb_detect_encoding($str, $encodeArr);
        $len    = mb_strlen($str, $encode); 
        return $len;
    }
    //mb取子字符串字符个数
    static function mbUpper($str)
    {
        if(!function_exists("mb_substr")) return strlen($str);
        $encodeArr = array('UTF-8', 'gbk', 'gb2312', 'CP936', 'ascii');
        $encode = mb_detect_encoding($str, $encodeArr);
        return mb_strtoupper($str, $encode); 
    }
    //mb获取特定字符位置
    static function mbPos($str, $char, $offset=0)
    {
        if(!function_exists("mb_substr")) return strpos($str, $char);
        $pos  = mb_strpos($str, $char, $offset, "utf-8");
        return $pos;
    }
    static function has($str, $substrs)
    {
        if(is_scalar($substrs)){
            $substrs = array($substrs);
        }
        foreach($substrs as $substr){
            $pos = self::mbPos($str, $substr);
            if(false !== $pos) return true;
        }
        return false;
    }
    //全角转换半角
    static function full2half($str)
    {
        $halfArr = array('`','~','!','@','#','$','%','^','&','*','(',')','-','_','=','+',
                        '{','}','[',']','[',']','|','\\',':',';','"','"','\'','\'','<',',','>','.','?','/',' ',
                        '0','1','2','3','4','5','6','7','8','9');
        $fullArr = array('｀','～','！','＠','＃','￥','％','……','＆','＊','（','）','－','——','＝','＋',
                        '｛','｝','［','］','【','】','｜','＼','：','；','“','”','‘','’','《','，','》','。','？','／','　',
                        '０','１','２','３','４','５','６','７','８','９');
        $max = count($halfArr);
        for($i=0; $i<=$max-1; $i++) {
            $str = str_replace($fullArr[$i], $halfArr[$i], $str);
        }
        return $str;
    }
    static function mbremoveChars($str, $ignoreChars='')
    {
        $chars = '`~!@#$%^&*()_+-=[]\\;\',/{}|:"<>?· 　１２３４５６７８９０－＝～！＠＃￥％……＆×（）——＋【】｛｝＼｜；‘：“，。、《》？．「」’” ＂';
        $encodeArr = array('UTF-8', 'gbk','gb2312','CP936','ascii');
        $encode = mb_detect_encoding($chars, $encodeArr);
        $len = mb_strlen($chars, $encode);
        
        for($i=0; $i<=$len-1; $i++) {
            $char = mb_substr($chars, $i, 1, $encode);
            if($ignoreChars && false !== strpos($ignoreChars, $char))continue;
            $str  = str_replace($char, '', $str);
        }
        return $str;
    }
    /*
    *
    *@html --- str html
    *@num  --- int 个数
    *@needRepaired --- 是否需要用tidy_repair_string修复([null]|0|1)
    *                  null:自动,0:不需要,1:修复
    */
    static function htmlCut($html='', $num=46, $needRepaired=null)
    {
        $patt = "/<.+?>/usi";
        $wordArr = preg_split($patt, $html);
        preg_match_all($patt, $html, $tagArr);
        $tagArr = $tagArr[0];
        $len1 = count($wordArr);
        $len2 = count($tagArr);
        $subs = $text = $wordArr[0];
        $len  = self::mbLen($subs);
        if($len >= $num){
            return self::mbSub($subs,$num).'...'; 
        }
        // print_r($wordArr);
        // print_r($tagArr);
        $prex_null = empty($wordArr[0])?1:0; // 记录前面有几个是空的
        for($i=1; $i<$len1; $i++){
            $term  = $wordArr[$i];
            $text .= ($tagArr[$i-1] . $term);
            $_len  = self::mbLen($term);
            $len  += $_len;
            $prex_null += empty($subs)&&empty($term[0])?1:0;
            if($len < $num){
                $subs .= ($tagArr[$i-1] . $term);
            }else{
                $l = $num - ($len - self::mbLen($term)); //还差几个字符到$num个
                $subs .= ($tagArr[$i-1] . self::mbSub($term,$l));
                if(1 == $i%2) $subs .= $tagArr[$i]; //补齐"关闭"标签
                break;
            }
        }
        if((null===$needRepaired && empty($wordArr[0]) && empty($wordArr[1])) || (1===$needRepaired)){
            // $wordArr[0]) && empty($wordArr[1])) //有嵌套标签
            $subs = tidy_repair_string($subs, array('output-xhtml'=>false, 'show-body-only'=>true, 'doctype'=>'strict', 'drop-font-tags'=>false, 'drop-proprietary-attributes'=>true, 'lower-literals'=>false, 'quote-ampersand'=>true, 'wrap'=>0), 'raw');
        }
        return $subs;
        /*
        Array
        (
            [0] => aaa
            [1] => PHP
            [2] => 任意图像
            [3] => 裁剪
            [4] => 成固定大小,做一个首页调用图像,
            [5] => 有时候
            [6] => 往往需要获得固定大小的图像
        )
        Array
        (
            [0] => Array
                (
                    [0] => <em>
                    [1] => </em>
                    [2] => <em>
                    [3] => </em>
                    [4] => <a href="http://baidu.com">
                    [5] => </a>
                )

        )*/
    }

    //匹配两字符串相似度
    static function getAppx($str1, $str2)
    {
        // self::getMatchTimes('中华人民共和国中国人民解放军', '中国人');exit;
        if(0==strlen($str1) || 0==strlen($str2)) return 0;
        $str1 = str_replace("·",'',$str1);
        $str2 = str_replace("·",'',$str2);
        $str1 = self::full2half($str1);
        $str2 = self::full2half($str2);
        $str1 = self::mbUpper($str1);
        $str2 = self::mbUpper($str2);
        $str1 = self::mbremoveChars($str1);
        $str2 = self::mbremoveChars($str2);
        $len1 = self::mbLen($str1);
        $len2 = self::mbLen($str2);

        $strlonger = $len1>$len2?$str1:$str2; //较长的一个字符串
        $strsorter = $len1>$len2?$str2:$str1; //较短的一个字符串
        $lenlonger = $len1>$len2?$len1:$len2; //较长的一个字符串的长度
        $lensorter = $len1>$len2?$len2:$len1; 
        //echo "$strlonger ----- $strsorter<br/><br/><br/>";
        /*
        $matchtimes = 0; //匹配次数
        $offsetlonger = 0;
        for($i=0; $i<=$lensorter-1; $i++) {
            $word = self::mbSub($strsorter, 1, $i);
            $pos  = self::mbPos($strlonger, $word, $offsetlonger);
            if($pos>0 || 0===$pos) {
                $matchtimes++;
                $offsetlonger = $pos;
            }
        }*/
        $matchtimes = self::getMatchTimes($strsorter, $strlonger);
        // echo "($matchtimes)*100/(($lenlonger+$lensorter)/2)\n";
        $appx = ($matchtimes)*100/(($lenlonger+$lensorter)/2);
        $appx = round($appx, 2);
        // echo "$appx,$appx%";
        if($appx >= 100){
            $t = intval($lenlonger/$lensorter);
            $t = $t>=99?99:$t;
            if($t >= 2){
                $appx -= $t;
            }
        }
        
        //str1在str2的中间
        if(strpos($str2, $str1)>1 && strpos($str2, $str1)+strlen($str1)< strlen($str2)) {
            return $appx>40?$appx-15:$appx;
        }
        //str2在str1的中间
        if(strpos($str1, $str2)>1 && strpos($str1, $str2)+strlen($str2)< strlen($str1)) {
            return $appx>40?$appx-15:$appx;
        }
        return $appx;
    }

    static function getMostSimilarWord($words, $string, $exArr=array())
    {
        $words   = trim($words);
        $words   = preg_replace("/[\s]+/si", ' ', $words);

        $string  = trim($string);
        $string  = preg_replace("/[\s]+/si", ' ', $string);

        $strArr  = explode(' ', $string);
        $simiArr = array();
        $wordArr = explode(' ', $words);
        foreach($wordArr as $word){
            $len_word = self::mbLen($word);
            $len_min  = ceil($len_word / 2);
            $len_max  = $len_word * 2;

            $first_word = self::mbSub($word,1,0); //word的第一个字符
            foreach($strArr as $str){
                $_len = self::mbLen($str);
                for($i=0; $i<=($_len-$len_min); $i++){
                    $_first = self::mbSub($str, 1, $i);
                    if($_first != $first_word)continue;
                    for($l=$len_min; $l<=$len_max; $l++){
                        if($l > $_len)break;
                        $_word = self::mbSub($str, $l, $i);
                        // echo "$_word($l+$i,$len_max)<br/>\n";
                        $_appx = self::getAppx($word, $_word);
                        if($_appx < 40)continue;
                        $_item = array(
                            'appx' => $_appx,
                            'word' => $_word,
                            'len'  => self::mbLen($_word),
                        );
                        $simiArr[] = $_item;
                    }
                }
            }
        }
        // print_r($simiArr);
        $simiArr = CTool::multi_array_sort($simiArr,'appx',SORT_DESC);
        return $simiArr;
    }
    /*
    * desc: 获取str1里的字符在str2中出现的次数(跟顺序有关)
    * 中国人  
    * 中华人民共和国中国人民解放军
    * 说明: 此算法的前提是str1比str2短
    */
    static function getMatchTimes($str1='', $str2='')
    {
        $matchtimes = 0;
        $len1 = self::mbLen($str1);
        $len2 = self::mbLen($str2);
        $str0 = $str2;
        for($i=0; $i<$len1; $i++){
            $char = self::mbSub($str1, 1, $i);//当前字符
            $pos  = self::mbPos($str0, $char);
            $matchtimes += (false !== $pos)?1:0;
            $str0 = self::mbSub($str0, null, $pos+1);
            // echo "$pos1, $pos2, $str0($matchtimes)\n";
            if(strlen($str0) <= 0) break;
        }
        return $matchtimes;
    }
    /**
    * autor: cty@20111201
    * 根据字典获取随机中文单词串
    *@dictArr array 词语数组(因dictArr通常比较大,所以采用引用传递模式)
    *@num     int   选择单词个数
    *
    */
    static function genWords(&$dictArr, $dictNum=null, $num=null)
    { 
        null == $num && $num = rand(4, 16);
        null == $dictNum && $dictNum = count($dictArr);
        $clause = '';
        for($i=1; $i<=$num; $i++) {
            $dict  = $dictArr[rand(0, $dictNum)];
            $clause .= ' '.$dict;
        }
        return $clause;
    }
    
    //获取随机中文字符串
    static function genZhcns($num=null)
    { 
        /*B0-F7，低字节从A1-FE
        \u0391-\uFFE5  4E00 - 9FBF B0A1 - F7FE
        中 文   区 间  常用字 区间
        */
        null == $num && $num = rand(30, 120);
        $chars = '';
        for($i=1; $i<=$num; $i++) {
            $code  = sprintf("%x", rand(0xB0A1, 0xF7FC));
            $jcode = '"\\u'.$code .'"';
            $char  = json_decode($jcode);
            $chars .= $char;
        }
        return $chars;
    }
};

