<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。
	const APPID = 'wx3d9781a534d9eac9';
	//受理商ID，身份标识
	const MCHID = '1229011702';
	//商户支付密钥Key。
	const KEY = '40527470ff7582abbe798752b54940d8';
	//公众平台开启开发模式后可查看
	const APPSECRET = '09b03cae0863a4791b7be6fbc2a900e1';
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://pys.mo2.cn/wxpay/jsapi';
	const RED_API_CALL_URL = 'http://pys.mo2.cn/wxpay/RedPackage';
	
	//=======【证书路径设置】=====================================
	const SSLCERT_PATH = __DIR__ . '/cacert/apiclient_cert.pem';
	const SSLKEY_PATH = __DIR__ . '/cacert/apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = 'http://pys.mo2.cn/wxpay/notify';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
	
?>