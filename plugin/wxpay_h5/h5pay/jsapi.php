<?php 
use Qiniu\json_decode;
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

$type = $_GET['type'] ;//1为充值，2为支付
$redirect_url = urldecode(trim($_GET['redirect_url']));
if(!in_array($type,array(1,2))){
	die('不合理的参数');
}
$redirect_url = !empty($redirect_url)?$redirect_url:'http://test.dafengcheapp.com/wx/index.php';
if(empty($redirect_url)){
	die('不合理的参数');
}
//①、获取用户openid
$tools = new JsApiPay();
$openId = $tools->GetOpenid();

//获取订单参数
$realMoney = $money = number_format($_GET['money'],2);
//请求接口 进行数据交互 生成订单和支付记录的起始状态 
$orderid = intval($_GET['orderid']);
$buyerid = intval($_GET['buyerid']);

if($type==1){//充值
	if($money<=0){
		die('充值金额必须大于0');
	}
	$url = "http://test.dafengcheapp.com/pay/wxpay/setH5PaymentChargeOrder";
	$post_data = array (
			"buyerid" => $buyerid,
			"amount" => $money,
			"type"   =>$type,
	);
}else{//支付
	if($orderid==0){
		die('非法订单');
	}
	$url = "http://test.dafengcheapp.com/pay/wxpay/setH5PaymentPrepayOrder";
	$post_data = array (
			"buyerid" => $buyerid,
			"orderid" => $orderid,//支付订单号  即publishid
			"type"   =>$type,
	);
}


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$output = curl_exec($ch);
curl_close($ch);
$data = json_decode($output,true);
if(isset($data['data']['orderid']) && isset($data['data']['paymentid']) && $data['status']==1 ){
	$outTradeNo = $data['data']['paymentid'];
	$realMoney = $money = $type==1?$money:$data['data']['amount'];
}else{
	if($data['status']==0){
		die($data['message']);
	}else{
		die('生成订单出错，请重新再试');
	}
	
}
$money = intval(number_format($money,2)*100);//微信支付需要*100
//print_r($money);


//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("微信充值");
$input->SetAttach("微信充值");
$input->SetOut_trade_no($outTradeNo);
$input->SetTotal_fee($money);
$input->SetProduct_id("-1");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("微信充值");
$input->SetNotify_url("http://test.dafengcheapp.com/wx/h5pay/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
//print_r($order);echo $openId;
echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();


//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付-支付</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				if(res.err_msg == 'get_brand_wcpay_request:ok'){
					alert('支付成功，订单待处理逻辑');
					setTimeout("window.location.href='<?php echo $redirect_url;?>'",3); 
					//跳转详情页面；前端详情页面；
				}else{
					alert('支付失败');
				}
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
				
				//alert(value1 + value2 + value3 + value4 + ":" + tel);
			}
		);
	}
	
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress); 
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			editAddress();
		}
		//document.getElementById("target").click();
	};
	
	</script>
</head>
<body>
    <br/>
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px"><?php echo $realMoney;?>元</span></b></font><br/><br/>
	<div align="center">
		<button id='target' style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>
</body>
</html>