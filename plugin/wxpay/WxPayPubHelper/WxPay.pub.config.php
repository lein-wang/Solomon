<?php
/**
* 	配置账号信息
*/
/*
const APPID = 'wxb27cfc9f23ff2f11';
	const MCHID = '1443737002';
	const KEY = 'q9jqdajkIkjdakjdaKJKAD9jda976j7H';
	const APPSECRET = '01c6d59a3f9024db6336662ac95c8e74';
*/
define('CURLOP_TIMEOUT', 10);
class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wxb27cfc9f23ff2f11';
	//受理商ID，身份标识
	const MCHID = '1443737002';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = 'acbd6b0fd997a47eb8ab0b23e5b61bd6';//acbd6b0fd997a47eb8ab0b23e5b61bd6
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = 'e221d9462f7b4e6091decd656c25c7ec';
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://www.xxxxxx.com/demo/js_api_call.php';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = '/xxx/xxx/xxxx/WxPayPubHelper/cacert/apiclient_cert.pem';
	const SSLKEY_PATH = '/xxx/xxx/xxxx/WxPayPubHelper/cacert/apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = 'http://www.xxxxxx.com/demo/notify_url.php';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = CURLOP_TIMEOUT;
}
	
?>