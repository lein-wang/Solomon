<?php
class CUrl {
    /**
    * desc: send http request by curl 
    * @param string   -- $url target url
    * @param array    -- $postArr post paramters key-val pairs
    * @upArr arr|null -- array('key'=>本地文件路径),es中上传文件基本不用,所以置于最末端
    * return: array, info
    */
    static function curlSend($url, $postArr=null, $extArr=array(), $upArr=null)
    {
        // if(is_array($extArr)) {
        $method = isset($extArr['method'])?$extArr['method']:'POST';
        $format = isset($extArr['format'])?$extArr['format']:'body'; //body格式(如些需要json格式),在es中默认为json格式
        // }else {
        // $method = $extArr; //extArr如果是字符串则表示http方法
        // $format = 'json';
        // }
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);//是否将头信息作为数据输出(HEADER信息)  
    
        $methodArr = array('POST'=>1, 'PUT'=>1);
        $postjsons = '';
        if(is_array($upArr) && count($upArr)>0) { //文件上传
            $isCURLFile = class_exists('CURLFile',false);
            foreach($upArr as $key => $file){
                if($isCURLFile){
                    $postArr[$key] = new CURLFile(realpath($file));
                }else{
                    $postArr[$key] = '@' . realpath($file);
                }
            }
            // 'pic'=>'@'.realpath($path).";type=".$type.";filename=".$filename
            // print_r($postArr);
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
            $headArr[] = 'Expect: ';
        }else {
            if(is_array($postArr) && count($postArr)>0) {
                if('json' == $format) {
                    $postjsons = json_encode($postArr, JSON_UNESCAPED_UNICODE);
                }else {
                    $postjsons = http_build_query($postArr);
                }
            }else if(is_string($postArr)) {
                $postjsons = $postArr;
            }
            isset($methodArr[$method]) && curl_setopt($ch, CURLOPT_POSTFIELDS, $postjsons);
        }
        
        if(isset($headArr) && is_array($headArr)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headArr);
        }
        if((false !== strpos(strtolower($url),'https')) || (isset($extArr['https']) && $extArr['https'])){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        for($i=1; $i<=10; $i++) {
            $result = curl_exec($ch);
            if(false === $result) {
                // echo $postjsons."\n";
                // echo 'curlerror:'.curl_error($ch)."[$i]\n";
                // trigger_error(curl_error($ch));
                usleep(500000 * $i);
                continue;
            }
            break;
        }
        if(false === $result) return false;
        $repArr = curl_getinfo($ch);
        curl_close($ch); 
        if(200 != $repArr) {
            $repArr['status'] = 0;
        }else {
            $repArr['status'] = 1;
        }
        $repArr['text'] = $result;
        // print_r($repArr);
        return $repArr; 
    }
    
    
    /**
    * curl get请求
    *@url     string --- 请求uri
    *@paraArr array  --- 附加参数
    *         [ get     array --- url参数
    *           headers array --- 请求头数组,
    *           proxy   array --- 代理信息数组,
    *           timeout int   --- 超时时间(s)
    *           ishead  bool  --- 是否将头文件的信息作为数据流输出[false]
    *           &repArr array --- 转储应答信息
    *           loops   int   --- 连接出错时，重复连接的次数
    *         ]
    */
    static function curlGet($url, $paraArr=array()) 
    {
        $get  = isset($paraArr['get'])?$paraArr['get']:null;
        $para = is_array($get)&&count($get)>0 ? http_build_query($get): '';
        if(strlen($para) > 0) {
        $url .= (strpos($url, '?') === FALSE ? '?' : '&'). $para;
        }
        $timeOut = isset($paraArr['timeout'])?$paraArr['timeout']:5;
        $ishead  = isset($paraArr['ishead'])?$paraArr['ishead']:false;
        $defaults = array( 
            CURLOPT_URL => $url, 
            CURLOPT_HEADER => $ishead, //是否将头信息作为数据流输出(HEADER信息)
            CURLOPT_RETURNTRANSFER => TRUE, 
            CURLOPT_TIMEOUT => $timeOut
        );
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:10.0) Gecko/20100101 Firefox/10.0', 
            'Accept-Language: zh-cn,zh;q=0.5',
            'Accept: */*',
            'Connection: keep-alive',
            'Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7', 
            'Cache-Control: max-age=0', 
        );
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);  
        if(isset($paraArr['headers']) && is_array($paraArr['headers']) && count($paraArr['headers'])>0) {
            $headers = $headers + $paraArr['headers'];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if((false !== strpos(strtolower($url),'https')) || (isset($extArr['https']) && $extArr['https'])){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        
        if(isset($paraArr['proxy']) && is_array($paraArr['proxy']) && count($paraArr['proxy'])>0) {
            $pxyArr = $paraArr['proxy'];
            if(isset($pxyArr['pxyHost'])) {
                $pxyPort = isset($pxyArr['pxyPort'])?$pxyArr['pxyPort']:80;
                $pxyType = (isset($pxyArr['pxyType'])&&'SOCKS5'==$pxyArr['pxyType'])?CURLPROXY_SOCKS5:CURLPROXY_HTTP; //(CURLPROXY_SOCKS5)
                curl_setopt($ch, CURLOPT_PROXYTYPE, $pxyType);  
                curl_setopt($ch, CURLOPT_PROXY,     $pxyArr['pxyHost']);
                curl_setopt($ch, CURLOPT_PROXYPORT, $pxyPort);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    //启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
                //授权信息
                if(isset($pxyArr['auth']) && is_array($pxyArr['auth'])) {
                    $authArr = $pxyArr['auth'];
                    if(isset($authArr['user']) && is_array($authArr['pswd'])) {
                        $user = $authArr['user'];
                        $pswd = $authArr['pswd'];
                        $proxyAuthType = CURLAUTH_BASIC; //(CURLAUTH_NTLM)
                        curl_setopt($ch, CURLOPT_PROXYAUTH, $proxyAuthType);  
                        $authinfo = "[{$user}]:[{$pswd}]";
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $authinfo);
                    }
                }
            }
        }
        $loops = isset($paraArr['loops'])?$paraArr['loops']:5;
        for($i=1; $i<=$loops; $i++) {
            $result = curl_exec($ch);
            if(false === $result) { usleep(500000 * $i); continue; }
            break;
        }
        
        if(isset($result) && !$result) {
            // trigger_error(curl_error($ch));
        }
        if(isset($paraArr['repArr'])) {
            $paraArr['repArr'] = curl_getinfo($ch);
        }
        curl_close($ch);
        return $result; 
    }
    
};