<?php


/**
 * 微信支付类
 * Created on Mar 27, 2015
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once dirname(__FILE__) . '/WxPayPubHelper.php';
class WxPay {
	/**
	 * 微信JSAPI支付
	 * $code 获取opneid
	 * $dataArr 提交参数内容
	 * return $jsApiParameters   
	 */
	function WxJsPayInterface($code, $dataArr = array ()) {
		$jsApi = new JsApi_pub();
		//=========步骤1：网页授权获取用户openid============
		//通过code获得openid
		if (empty($code)) {
			//触发微信返回code码
			$pay_url = isset($dataArr['payUrl']) ? $dataArr['payUrl'] : WxPayConf_pub :: JS_API_CALL_URL  ;
			$url = $jsApi->createOauthUrlForCode($pay_url);
			Header("Location: $url");
		} else {
			//获取code码，以获取openid
			//$code = $_GET['code'];
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();
		}
		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
		$unifiedOrder = new UnifiedOrder_pub();
		$unifiedOrder->setParameter("openid", "$openid"); //openid
		$unifiedOrder->setParameter("body", $dataArr['title']); //商品描述
		$timeStamp = time();
		$out_trade_no = WxPayConf_pub :: APPID . "$timeStamp";
		$unifiedOrder->setParameter("out_trade_no", $dataArr['out_trade_no']); //商户订单号 
		$unifiedOrder->setParameter("total_fee", $dataArr['money']); //总金额
		$notify_url = isset($dataArr['notify_url']) ? $dataArr['notify_url'] : WxPayConf_pub :: NOTIFY_URL;
		$unifiedOrder->setParameter("notify_url", $notify_url); //通知地址 
		$unifiedOrder->setParameter("trade_type", "JSAPI"); //交易类型
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
		return $jsApiParameters;
	}
	/**
	 * 动态模式支付   生成二维码
	 * $dataArr 提交参数内容
	 */
	function NativeDynamicInterface($dataArr = array ()) {
		$unifiedOrder = new UnifiedOrder_pub();
		$unifiedOrder->setParameter("body", $dataArr['body']); //商品描述
		$timeStamp = time();
		$out_trade_no = WxPayConf_pub :: APPID . "$timeStamp";
		$unifiedOrder->setParameter("out_trade_no", "$out_trade_no"); //商户订单号 
		$unifiedOrder->setParameter("total_fee", $dataArr['total_fee']); //总金额
		$unifiedOrder->setParameter("notify_url", WxPayConf_pub :: NOTIFY_URL); //通知地址 
		$unifiedOrder->setParameter("trade_type", "NATIVE"); //交易类型

		//获取统一支付接口结果
		$unifiedOrderResult = $unifiedOrder->getResult();

		//商户根据实际情况设置相应的处理流程
		if ($unifiedOrderResult["return_code"] == "FAIL") {
			//商户自行增加处理流程
			echo "通信出错：" . $unifiedOrderResult['return_msg'] . "<br>";
		}
		elseif ($unifiedOrderResult["result_code"] == "FAIL") {
			//商户自行增加处理流程
			echo "错误代码：" . $unifiedOrderResult['err_code'] . "<br>";
			echo "错误代码描述：" . $unifiedOrderResult['err_code_des'] . "<br>";
		}
		elseif ($unifiedOrderResult["code_url"] != NULL) {
			//从统一支付接口获取到code_url
			$code_url = $unifiedOrderResult["code_url"];
			//商户自行增加处理流程
			//......
		}
		return $code_url;
	}

	/**
	 * 微信红包发放
	 * $dataArr 提交参数内容
	 */
	function RedPackage($code, $dataArr = array ()) {
		$hongbao = new RedPackage();
		$jsApi = new JsApi_pub();
		if (!isset ($code)) {
			//触发微信返回code码
			$url = $jsApi->createOauthUrlForCode(WxPayConf_pub :: RED_API_CALL_URL);
			Header("Location: $url");
		} else {
			//获取code码，以获取openid
			//$code = $_GET['code'];
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();
		}
		$hongbao->setParameter("mch_billno", $dataArr['mch_billno']); //微信订单号
		$hongbao->setParameter("nick_name", $dataArr['nick_name']); //商户退款单号
		$hongbao->setParameter("send_name", $dataArr['send_name']); //总金额
		$hongbao->setParameter("re_openid", $openid); //退款金额
		$hongbao->setParameter("total_amount", $dataArr['total_amount']); //退款金额
		$hongbao->setParameter("min_value", $dataArr['min_value']); //退款金额
		$hongbao->setParameter("max_value", $dataArr['max_value']); //退款金额
		$hongbao->setParameter("total_num", $dataArr['total_num']); //退款金额
		$hongbao->setParameter("wishing", $dataArr['wishing']); //退款金额
		$hongbao->setParameter("client_ip", $dataArr['client_ip']); //退款金额
		$hongbao->setParameter("act_name", $dataArr['act_name']); //退款金额
		$hongbao->setParameter("remark", $dataArr['remark']); //退款金额
		//调用结果
		$hongbaoResult = $hongbao->getResult();
		echo "错误代码：" . $hongbaoResult['err_code'] . "<br>";
		echo "错误代码描述：" . $hongbaoResult['err_code_des'] . "<br>";

	}
	/**
	 * 申请退款
	 * $dataArr 提交参数内容
	 */
	function WxRefundInterface($dataArr = array ()) {
		$refund = new Refund_pub();
		$refund->setParameter("transaction_id", $dataArr['transaction_id']); //微信订单号
		$refund->setParameter("out_refund_no", $dataArr['out_refund_no']); //商户退款单号
		$refund->setParameter("total_fee", $dataArr['total_fee']); //总金额
		$refund->setParameter("refund_fee", $dataArr['refund_fee']); //退款金额
		$refund->setParameter("op_user_id", WxPayConf_pub :: MCHID); //操作员
		//调用结果
		$refundResult = $refund->getResult();
		if ($refundResult["return_code"] == "FAIL") {
			echo "通信出错：".$refundResult['return_msg']."<br>";
			return false;
		}
		else{
			echo "业务结果：".$refundResult['result_code']."<br>";
			echo "签名：".$refundResult['sign']."<br>";
			if($refundResult['result_code'] == "SUCCESS")
				return true;
			else
				return false;
		}
		
	}
	/**
	 * 静态支付
	 * return  $product_url  二维码链接
	 */
	function NativeStaticInterface() {
		$nativeLink = new NativeLink_pub();
		$nativeLink->setParameter("product_id", WxPayConf_pub :: APPID . "static"); //商品id
		//获取链接
		$product_url = $nativeLink->getUrl();
		$shortUrl = new ShortUrl_pub();
		$shortUrl->setParameter("long_url", "$product_url"); //URL链接
		$codeUrl = $shortUrl->getShortUrl();
		return $product_url;
	}
	/**
		 * 回调内容
		 * $dataArr 提交参数内容
		 */
	function CallbackInterface($dataArr = array ()) {
		
		//使用native通知接口
		$nativeCall = new NativeCall_pub();

		

		if ($nativeCall->checkSign() == FALSE) {
			$nativeCall->setReturnParameter("return_code", "FAIL"); //返回状态码
			$nativeCall->setReturnParameter("return_msg", "签名失败"); //返回信息
		} else {
			//提取product_id
			$product_id = $nativeCall->getProductId();
			//使用统一支付接口
			$unifiedOrder = new UnifiedOrder_pub();

			//根据不同的$product_id设定对应的下单参数，此处只举例一种
			switch ($product_id) {
				case WxPayConf_pub :: APPID . "static" : //与native_call_qrcode.php中的静态链接二维码对应
					$unifiedOrder->setParameter("body", $dataArr['body']); //商品描述
					//自定义订单号，此处仅作举例

					$unifiedOrder->setParameter("out_trade_no", $dataArr['out_trade_no']); //商户订单号 			$unifiedOrder->setParameter("product_id","$product_id");//商品ID
					$unifiedOrder->setParameter("total_fee", $dataArr['total_fee']); //总金额
					$unifiedOrder->setParameter("notify_url", WxPayConf_pub :: NOTIFY_URL); //通知地址 
					$unifiedOrder->setParameter("trade_type", "NATIVE"); //交易类型
					$unifiedOrder->setParameter("product_id", "$product_id"); //用户标识

					//获取prepay_id
					$prepay_id = $unifiedOrder->getPrepayId();
					$nativeCall->setReturnParameter("return_code", "SUCCESS"); //返回状态码
					$nativeCall->setReturnParameter("result_code", "SUCCESS"); //业务结果
					$nativeCall->setReturnParameter("prepay_id", "$prepay_id"); //预支付ID

					break;
				default :
					$nativeCall->setReturnParameter("return_code", "SUCCESS"); //返回状态码
					$nativeCall->setReturnParameter("result_code", "FAIL"); //业务结果
					$nativeCall->setReturnParameter("err_code_des", "此商品无效"); //业务结果
					break;
			}

		}


	}
	//支付回调
	function NotifyInterface() {
		$code = array();
		$notify = new Notify_pub();
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
		$notify->saveData($xml);
		if ($notify->checkSign() == FALSE) {
			$notify->setReturnParameter("return_code", "FAIL"); //返回状态码
			$notify->setReturnParameter("return_msg", "签名失败"); //返回信息
		} else {
			$notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
		}
		$returnXml = $notify->returnXml();
		echo $returnXml;
		if ($notify->checkSign() == TRUE) {
			if ($notify->data["return_code"] == "FAIL") {
				 $code = array('state'=>-1,'xml'=>$xml);
			}
			elseif ($notify->data["result_code"] == "FAIL") {
				$code = array('state'=>-2,'xml'=>$xml);
			} 
			else {
				 $code = array('state'=>1,'xml'=>$xml);
			}
		}
		return  $code;

	}
}
?>
