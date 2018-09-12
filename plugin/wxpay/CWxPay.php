<?php
/**
 * 微信支付类
 * Created on Mar 27, 2015
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
*/
// require_once dirname(__FILE__) . '/WxPayPubHelper/WxPayPubHelper.php';
require_once __DIR__.'/v3/WxPay.php';

class CWxPay {

	private function MakingUnifiedOrderInstance($params, $notify_url=null)
	{
	    $user_type = isset($params['user_type'])?$params['user_type']:1;
	    //$user_type = 3;
	    //1正常用户 3店铺
		$wxpay = new WxPay($user_type);

		if(!isset($params['out_trade_no'])) return false;
		$total_fee = isset($params['total_fee'])?$params['total_fee']:0; //订单总金额，单位为分
		$total_fee = floatval($total_fee);
		if($total_fee <= 0) return false;
		$wxpay->total_fee = $total_fee*100;//订单的金额(分)

		// $wxpay->out_trade_no = date('YmdHis') . substr(time(), - 5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));//订单号
		$out_trade_no = $params['out_trade_no'];//.'-'.time();//不能重复
		$wxpay->out_trade_no = $out_trade_no;//订单号

		$wxpay->body = isset($params['body'])?$params['body']:'购买...';//支付描述信息
		$wxpay->time_expire = date('YmdHis', time()+86400);//订单支付的过期时间(eg:一天过期)

		if(isset($params['trade_type'])){//默认为APP
			$wxpay->trade_type = $params['trade_type'];
		}
		if(isset($params['product_id'])){
			$wxpay->product_id = $params['product_id'];
		}
		$wxpay->notify_url = $notify_url;//异步通知URL(更改支付状态)

		return $wxpay;
		//=========================================

		/*//使用统一支付接口
		$unifiedOrder = new UnifiedOrder_pub();
		//设置统一支付接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//spbill_create_ip已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$unifiedOrder->setParameter("body","支付测试");//商品描述
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
		$unifiedOrder->setParameter("total_fee", 1);//总金额
		$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
		$unifiedOrder->setParameter("trade_type","APP");//交易类型
		//非必填参数，商户可根据实际情况选填
		//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
		//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
		//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
		//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
		//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
		//$unifiedOrder->setParameter("openid","XXXX");//用户标识
		//$unifiedOrder->setParameter("product_id","XXXX");//商品ID
		*/
		/*********************这里是用户自己的参数************************/
		/*if(isset($paraArr['body'])){
			$unifiedOrder->setParameter("body", $paraArr['body']);
		}
		if(isset($paraArr['out_trade_no'])){
			$unifiedOrder->setParameter("out_trade_no", $paraArr['out_trade_no']);
		}
		if(isset($paraArr['total_fee'])){
			$unifiedOrder->setParameter("total_fee", $paraArr['total_fee']);
		}
		if($notify_url){
			$unifiedOrder->setParameter("notify_url", $notify_url);
		}
		if(isset($paraArr['sub_mch_id'])){
			$unifiedOrder->setParameter("sub_mch_id", $paraArr['sub_mch_id']);
		}
		if(isset($paraArr['device_info'])){
			$unifiedOrder->setParameter("device_info", $paraArr['device_info']);
		}
		if(isset($paraArr['attach'])){
			$unifiedOrder->setParameter("attach", $paraArr['attach']);
		}
		if(isset($paraArr['time_start'])){
			$unifiedOrder->setParameter("time_start", $paraArr['time_start']);
		}
		if(isset($paraArr['time_expire'])){
			$unifiedOrder->setParameter("time_expire", $paraArr['time_expire']);
		}
		if(isset($paraArr['goods_tag'])){
			$unifiedOrder->setParameter("goods_tag", $paraArr['goods_tag']);
		}
		if(isset($paraArr['openid'])){
			$unifiedOrder->setParameter("openid", $paraArr['openid']);
		}
		if(isset($paraArr['product_id'])){
			$unifiedOrder->setParameter("product_id", $paraArr['product_id']);
		}*/
		/*********************这里是用户自己的参数 end********************/

		/*return $unifiedOrder;*/
	}
	
	/**
	 * JS_API支付demo
	 * ====================================================
	 * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
	 * 成功调起支付需要三个步骤：
	 * 步骤1：网页授权获取用户openid
	 * 步骤2：使用统一支付接口，获取prepay_id
	 * 步骤3：使用jsapi调起支付
	 *return bool(失败false)|string(失败要跳转的url)|array(成功的参数)
	*/
	public function jsPay($code, $paraArr, $js_api_call_url=null)
	{
		if(!$js_api_call_url){
			$js_api_call_url = WxPayConf_pub::JS_API_CALL_URL;
		}

		//使用jsapi接口
		$jsApi = new JsApi_pub();

		//=========步骤1：网页授权获取用户openid============
		//通过code获得openid
		if(empty($code)){
			//触发微信返回code码
			$url = $jsApi->createOauthUrlForCode($js_api_call_url);
			// Header("Location: $url");
			return $url;
		}else{
			//获取code码，以获取openid
		    // $code = $_GET['code'];
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();
		}
		
		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
		$unifiedOrder = new UnifiedOrder_pub();
		
		//设置统一支付接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//spbill_create_ip已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$unifiedOrder->setParameter("openid","$openid");//商品描述
		$unifiedOrder->setParameter("body","贡献一分钱");//商品描述
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
		$unifiedOrder->setParameter("total_fee","1");//总金额
		$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		//非必填参数，商户可根据实际情况选填
		//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
		//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
		//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
		//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
		//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
		//$unifiedOrder->setParameter("openid","XXXX");//用户标识
		//$unifiedOrder->setParameter("product_id","XXXX");//商品ID

		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);

		$jsApiParameters = $jsApi->getParameters();
		//echo $jsApiParameters;

		return $jsApiParameters;
	}
	/**
	 * Native（原生）支付-模式二-demo
	 * ====================================================
	 * 商户生成订单，先调用统一支付接口获取到code_url，
	 * 此URL直接生成二维码，用户扫码后调起支付。
	 * 
	*/
	public function dynamicNativePay($params=array(), $notify_url=null)
	{
		$params['trade_type'] = 'NATIVE';
		$UNO = $this->MakingUnifiedOrderInstance($params, $notify_url);
		if(!$UNO) return false;
		return $UNO->doPay();


		/*$unifiedOrder = $this->make_UnifiedOrder_instance($paraArr);
		//获取统一支付接口结果
		$unifiedOrderResult = $unifiedOrder->getResult();

		//商户根据实际情况设置相应的处理流程
		if($unifiedOrderResult["return_code"] == "FAIL"){
			//商户自行增加处理流程
			// echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
			return false;
		}elseif($unifiedOrderResult["result_code"] == "FAIL"){
			//商户自行增加处理流程
			// echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
			// echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
			return false;
		}elseif($unifiedOrderResult["code_url"] != NULL){
			//从统一支付接口获取到code_url
			return $code_url = $unifiedOrderResult["code_url"];
			//商户自行增加处理流程
			//......
		}
		return false;*/
	}

	/*
	* desc: 供app使用的prepay_id
	*
	*/
	public function mkPrepay($params=array(), $notify_url=null)
	{
		$params['trade_type'] = 'APP';
		$UNO = $this->MakingUnifiedOrderInstance($params, $notify_url);
		if(!$UNO) return false;
		return $UNO->doPay();

		//============================================

		/*exit;
		$responseData = array(
		    'notify_url' => $WxPay->notify_url,
		    'app_response' => $WxPay->doPay(),
		);
		$errorCode = 0;
		$errorMsg = 'success';
		print_r($responseData);
		exit;



		include __DIR__ .'/wechatAppPay.class.php';
	    //1.统一下单方法
	    $appid = 'wxb27cfc9f23ff2f11';
	    $mch_id = '1443737002';
	    $key = 'q9jqdajkIkjdakjdaKJKAD9jda976j7H';
	    $wechatAppPay = new wechatAppPay($appid, $mch_id, $notify_url, $key);
	    $params['body'] = '商品描述';                       //商品描述
	    $params['out_trade_no'] = 'O20160617021323-001'.time();    //自定义的订单号
	    $params['total_fee'] = '100';                       //订单金额 只能为整数 单位为分
	    $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP 
	    $result = $wechatAppPay->unifiedOrder( $params );
	    print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
	    //2.创建APP端预支付参数
	    // * @var TYPE_NAME $result 
	    $data = @$wechatAppPay->getAppPayParams( $result['prepay_id'] );
	                // 根据上行取得的支付参数请求支付即可
	    print_r($data);*/

















		// exit;
		$timestamp = time();
		do{
			$unifiedOrder = $this->make_UnifiedOrder_instance($paraArr, $notify_url);
			$prepay_id = $unifiedOrder->getPrepayId();
			usleep(100000);
		}while(!$prepay_id && ($loop=(isset($loop)?++$loop:0))<10);
		$retArr = $unifiedOrder->getParameters();
		$retArr['prepayid'] = $prepay_id;
		$retArr["package"]  = "Sign=WXPay";
		$retArr["timestamp"]  = $timestamp;

		/*$arr = array_intersect_key($retArr, array(
				'appid'=>1, 
				'nonce_str'=>1,
				'package'=>1,
				'mch_id'=>1,
				'prepayid'=>1,
				'timestamp'=>1,
			)
		);*/
		/*  returnMap.put("partnerid", weiXinPartner);
          returnMap.put("prepayid", prepay_id);
          returnMap.put("package", "Sign=WXPay");
          returnMap.put("noncestr", nonce_str);
          returnMap.put("timestamp", String.valueOf(System.currentTimeMillis() / 1000));
          String stringB = AlipayCore.weiXinCreateLinkString(returnMap);
          String stringSignTempB = stringB + "&key=" + weiXinPartnerkey;
          String signB = MD5Util.MD5Encode(stringSignTempB, "utf-8").toUpperCase();
          returnMap.put("sign", signB);*/
		// print_r($retArr);
		// print_r($arr);
        $arr = array(
        	'appid' => $retArr['appid'],
        	'prepayid' => $retArr['prepayid'],
        	'package' => 'Sign=WXPay',
        	'noncestr' => $retArr['nonce_str'],
        	'partnerid' => $retArr['mch_id'],
        	'timeStamp' => $timestamp,
        );
        // appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_id=10000100&nonce_str=ibuaiVcKdpRxkhJA
        //appId="+appId + "&nonceStr="+noncestr + "&package=prepay_id=wx2015041419450958e073ca4a0071648005&signType=MD5&timeStamp=" + timestamp + "&key="+key

		// $sign = $unifiedOrder->getSign($arr);
		// var_dump($sign);
		// print_r($arr);
		$retArr['sign'] = $unifiedOrder->getSign($arr);
		// appid、appkey、noncestr、package(注意:此处应置为 Sign=WXPay)、partnerid、prepayid、timestamp
		// $retArr['prepayid'] = $prepay_id;
		// var_dump($prepay_id);
		return $retArr;
	}
	/*
	* desc: 通知验证
	*
	*/
	public function verifyPayment()
	{
		return (new WxPay())->verifyNotify();









		return true;






		//使用通用通知接口
		$notify = new Notify_pub();

		// if(!isset($GLOBALS['HTTP_RAW_POST_DATA']))return false;
		if(isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		}else{
			$xml = file_get_contents('php://input');
		}
		if(empty($xml))return false;

		//存储微信的回调
		$notify->saveData($xml);
		
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		return $notify->getData(); //数组形式
		/*$returnXml = $notify->returnXml();
		echo $returnXml;
		return $returnXml;
		*/
		
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
		
		//以log文件形式记录回调信息
		/*$log_ = new Log_();
		$log_name="./notify_url.log";//log文件路径
		$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

		if($notify->checkSign() == TRUE)
		{
			if ($notify->data["return_code"] == "FAIL") {
				//此处应该更新一下订单状态，商户自行增删操作
				$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
			}
			elseif($notify->data["result_code"] == "FAIL"){
				//此处应该更新一下订单状态，商户自行增删操作
				$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
			}
			else{
				//此处应该更新一下订单状态，商户自行增删操作
				$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
			}
			//商户自行增加处理流程,
			//例如：更新订单状态
			//例如：数据库操作
			//例如：推送支付完成信息
		}
		*/
	}
};
