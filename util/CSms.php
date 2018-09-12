<?php

/**
 * author: cty@20120701
 *   desc: 短信发送模块 
 *   url http://sms.webchinese.cn/web_api/?Uid=heqmro&Key=f1979f52d60b18b448d4&smsMob=13913510228&smsText=test
 *   f1979f52d60b18b448d4
 *   heqmro
 *   call: CSms::send(mobile='13912345678,13912345679', content='短信测试')
 *         @content --- string(utf8|unicode)
 */
class CSms {
    # __table__ = 'sms'
    # url   =  http://sms.webchinese.cn/web_api/?Uid=heqmro&Key=f1979f52d60b18b448d4&smsMob=13913510228&smsText=test
    // $url='http://utf8.sms.webchinese.cn/?Uid='.$uid.'&Key='.$key.'&smsMob='.$mobile.'&smsText=验证码：'.$code.'【2050游戏】';
    // static $url  = 'http://222.73.117.158/msg/HttpBatchSendSM?';
//    static $url  = 'http://utf8.sms.webchinese.cn/';
//    static $account  = '潜伏007';
//    static $pswd  = '48d266253b6dac1678fd';
//    
//    static $statusArr = array(
//        0   => '提交成功',
//        101  => '没有该用户账户',
//        102  => '密码错误',
//        103  => '提交过快（提交速度超过流速限制）',
//        104 => '系统忙（因平台侧原因，暂时无法处理提交的短信）',
//        105 => '敏感短信（短信内容包含敏感词）',
//        106  => '消息长度错（>536或<=0）',
//        107 => '包含错误的手机号码',
//        108 => '',
//        109 => '无发送额度（该用户可用短信数已使用完）',
//        110 => '不在发送时间内',
//        111 => '超出该账户当月发送额度限制',
//        112 => '无此产品，用户没有订购该产品',
//        113 => 'extno格式错（非数字或者长度不对）',
//        115 => '自动审核驳回',
//        116 => '签名不合法，未带签名（用户必须带签名的前提下）',
//        117 => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',
//        118 => '用户没有相应的发送权限',
//        119 => '用户已过期',
//    );
//
//    static function send($mobile, $content=null)
//    {
//        // url http://utf8.sms.webchinese.cn/?Uid=潜伏007&Key=48d266253b6dac1678fd&smsMob=13812345678&smsText=test
//        $req =array('sms_status' => false, 'sms_code'=>0, 'sms_message'=>'');
//        // echo "$mobile ==============================\n";
//        if($mobile && $content){
//            $mobile = preg_replace('/[^0-9\,]/', '', $mobile);
//            if(strlen($mobile)>0 && strlen($content)>0){
//                $post_data = array();
//                $post_data['Uid']    = self::$account;
//                $post_data['Key']    = self::$pswd;
//                $post_data['smsMob'] = $mobile;
//                $post_data['smsText']= mb_convert_encoding($content,'UTF-8', 'auto');
//                $url = self::$url;
//
//                $code = intval(self::curlGet($url, $post_data));
//                // var_dump($code);
//                /*$code = explode(',', $code);
//                $req['sms_status']  = $code[1] == 0?true:false;
//                $req['sms_code']    = $code[1];
//                $req['sms_message'] = isset(self::$statusArr[$code[1]]) ? self::$statusArr[$code[1]] : "";*/
//                $req['sms_status'] = 1==$code?true:false;
//            }
//        }
//        return $req;
//    }
//
//    static function curlPost($url, $postArr=array())
//    {
//        $o="";
//        foreach ($postArr as $k=>$v)
//        {
//           $o.= "$k=".urlencode($v)."&";
//        }
//        $post_data=substr($o,0,-1);
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_URL,$url);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
//        return curl_exec($ch);
//    }
//    
//    static function curlGet($url, $paraArr=array(), $exArr=array()) 
//    {
//        $para = is_array($paraArr)&&count($paraArr)>0 ? http_build_query($paraArr): '';
//        if(strlen($para) > 0) {
//            $url .= (strpos($url, '?') === FALSE ? '?' : '&'). $para;
//        }
//        $timeOut = isset($exArr['timeout'])?$exArr['timeout']:5;
//        $ishead  = isset($exArr['ishead'])?$exArr['ishead']:false;
//        $defaults = array( 
//            CURLOPT_URL => $url, 
//            CURLOPT_HEADER => $ishead, //是否将头信息作为数据流输出(HEADER信息)
//            CURLOPT_RETURNTRANSFER => TRUE, 
//            CURLOPT_TIMEOUT => $timeOut
//        );
//        $headers = array(
//            // 'Mozilla/5.0 (Windows NT 6.1; rv:10.0) Gecko/20100101 Firefox/10.0', 
//            // 'Accept-Language: zh-cn,zh;q=0.5',
//            // 'Accept: */*',
//            // 'Connection: keep-alive',
//            // 'Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7', 
//            // 'Cache-Control: max-age=0', 
//        );
//        $ch = curl_init();
//        curl_setopt_array($ch, $defaults);  
//        if(isset($exArr['headers']) && is_array($exArr['headers']) && count($exArr['headers'])>0) {
//            $headers = $headers + $exArr['headers'];
//        }
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        
//        $loops = isset($exArr['loops'])?$exArr['loops']:5;
//        for($i=1; $i<=$loops; $i++) {
//            $result = curl_exec($ch);
//            if(false === $result) { usleep(500000 * $i); continue; }
//            break;
//        }
//        
//        if(isset($result) && !$result) {
//            trigger_error(curl_error($ch));
//        }
//        if(isset($exArr['repArr'])) {
//            $exArr['repArr'] = curl_getinfo($ch);
//        }
//        curl_close($ch);
//        return $result; 
//    }

    static $url = 'http://ym.ihuanfa.com/msg/sendmsg.php';
//    static $url = 'http://139.129.244.213/msg/sendmsg.php';
    //static $url  = 'http://47.90.122.61:8081/msg/sendmsg.php';

    /**
     * 加密
     * @param type $str
     * @return type
     */
    static function encode_xor($str) {
        $mixByte = mt_rand(0, 127);
        $binString = '';

        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $binString .= chr($mixByte ^ ord($str[$i]/* substr($str, $i, 1) */));
        }
        return base64_encode(chr($mixByte) . $binString);
    }
    
    /**
     * 发送
     * 返回内容为json格式， 例如：['status'=>0, 'msg'=>'手机号不能为空']  status为1则是发送成功  msg是消息内容
     * @param type $code
     * @param type $mobiles
     * @return type
     */
    static function send($code, $mobiles) {//【焕发APP】 这个签名要删掉，否则短信发送会失败
        // type为1是短信验证  2 是语音验证   content是短信内容  如果是语音短信则内容只能是4到6位数字  mobiles  手机号，可同时传多个
        $content = ['type' => 1, 'content' => $code, 'mobiles' => $mobiles];
        $data['content'] = self::encode_xor(json_encode($content));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);  //5秒超时
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return json_decode($file_contents, TRUE);
    }
    
    /**
     * 
     * @param string $mobiles 电话号码,可以以逗号隔开批量发送
     * @param int $type 类型 1验证码
     * @param string $code 验证码、或者其他变量的值 8\9时是数组
     * @return 
     */
    static function sendSms($mobiles,$type=1,$code='') {//【焕发APP】 这个签名要删掉，否则短信发送会失败
        $logs[] = '请求';
        $logs[] = array(
                'mobiles'=>$mobiles,
                'type'=>$type,
                'code'=>$code
                );
        $accessKeyId = "LTAIIj1rmSkryAiv";
        $accessKeySecret = "MSdSlxwL2aacDkJuEnPLZRszgkv0t5";
        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = is_array($mobiles)?implode(',', $mobiles):$mobiles;
        if($type ==1){//验证码
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_130922970";
            $params['TemplateParam'] = Array (
                    "code" => $code,
            );
        }elseif($type ==2){//店铺审核-通过
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_130922948";
            $params['TemplateParam'] = Array (
                    "code" => $code,
            );
        }elseif($type ==3){//店铺审核-不通过
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_130923012";
            $params['TemplateParam'] = Array (
                    "remark" => $code,
            );
        }elseif($type ==4){//造型师认证-审核通过
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_130917987";
            $params['TemplateParam'] = Array (
                    "code" => $code,
            );
        }elseif($type ==5){//造型师认证-审核不通过
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_130922947";
            $params['TemplateParam'] = Array (
                    "remark" => $code,
            );
        }elseif($type ==6){//提现-审核通过
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_134314136";
            $params['TemplateParam'] = Array (
                    "money" => $code,
            );
        }elseif($type ==7){//提现-审核不通过
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_134314143";
            $params['TemplateParam'] = Array (
                    "money" => $code,
            );
        }elseif($type ==8){//提现时审核-核实过程-通知
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_135800159";
            $params['TemplateParam'] = Array (
                    'code'=>$code
                    );
        }elseif($type ==9){//用户账户-充值短信充值
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_135805343";
            $params['TemplateParam'] = Array (
                    'money'=>$code['money'],
                    'remain'=>$code['remain'],
                    );
        }elseif($type ==10){//用户账户-充值红包
            $params["SignName"] = "焕发APP";
            $params["TemplateCode"] = "SMS_135805345";
            $params['TemplateParam'] = Array (
                    'money'=>$code['money'],
                    'remain'=>$code['remain'],
                    );
        }else{
            die('失败');
        }
        $params['TemplateParam']= json_encode($params['TemplateParam']);
        require_once dirname(dirname(__FILE__))."/plugin/third/aliyun-dysms-php-sdk-lite/SignatureHelper.php";
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                        "RegionId" => "cn-hangzhou",
                        "Action" => "SendSms",
                        "Version" => "2017-05-25",
                ))
                // fixme 选填: 启用https
                // ,true
        );
        $logs[] = '响应';
        $logs[] = (array)$content;
        $dir = dirname(dirname(__FILE__)).'/logs';
        $filename = $dir.'/'."send-sms-".date("Ymd").'.log';
        $time = date("Y-m-d.H:i:s");
        $logconent = ''
                . "\n>>>>>>>>>>>>>>>>>>>>({$time})\n"
                . print_r($logs,true)
                . "\n<<<<<<<<<<<<<<<<<<<<({$time})\n";
        file_put_contents($filename, $logconent, FILE_APPEND);
        $res = (array)$content;
        if(is_array($res) && isset($res['Code'])){
            if($res['Code']=='OK'){
                return array('status'=>1,'msg'=>'ok');
            }else{
                return array('status'=>0,'msg'=>$res['Message']);
            }
        }else{
            return array('status'=>0,'msg'=>'no');
        }
        //return json_encode($content);
    }

}

// CSms::send('15950001413', 'test');