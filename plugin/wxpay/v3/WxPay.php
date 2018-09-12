<?php
/**
 * @desc 微信APP支付类
 * @author shangheguang@yeah.net
 * @date 2015-08-24
 */

class WxPay {
	
	//参数配置
	public $config;
	
	//服务器异步通知页面路径(必填)
	public $notify_url = '';
	
	//商户订单号(必填，商户网站订单系统中唯一订单号)
	public $out_trade_no = '';

	//商品描述(必填，不填则为商品名称)
	public $body = '';
	
	//付款金额(必填)
	public $total_fee = 0;
	
	//自定义超时(选填，支持dhmc)
	public $time_expire = '';

	//交易类型
	public $trade_type = 'APP';//APP,NATIVE,JSAPI
	
	//产品id
	public $product_id = 0;
	
	private $WxPayHelper;
	
	function __construct($user_type=1) 
	{
		require_once __DIR__.'/config.php';
		require_once __DIR__.'/WxPayHelper.php';
		//1正常用户 3店铺
		if($user_type==1){
		    $this->config  	   = $config;
		}elseif($user_type==3){
		    $this->config  	   = $config2;
		}else{
		    echo json_encode(array('status'=>0,'message'=>'参数有误'));exit;
		}
		
		$this->WxPayHelper = new WxPayHelper();
	}
	
	public function chkParam() 
	{
		//用户网站订单号
		if (empty($this->out_trade_no)) {
			die('out_trade_no error');
		}	
		//商品描述
		if (empty($this->body)) {
			die('body error');
		}
		if (empty($this->time_expire)){
			die('time_expire error');
		}
		//检测支付金额
		if (empty($this->total_fee) || !is_numeric($this->total_fee)) {
			die('total_fee error');
		}
		//异步通知URL
		if (empty($this->notify_url)) {
			die('notify_url error');
		}
		if (!preg_match("#^http:\/\/#i", $this->notify_url)) {
			$this->notify_url = "http://" . $_SERVER['HTTP_HOST'] . $this->notify_url;
		}
		return true;
	}
	
	/**
	 * 生成支付(返回给APP)
	 * @return boolean|mixed
	 */
	public function doPay() {
	    //检测构造参数
	    $this->chkParam();
	    return $this->createAppJsNativeParams();
	}
	
    /**
     * APP统一下单
     */
	private  function createAppJsNativeParams($params=array()) 
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
        $data["appid"] 		      = $this->config['appid'];//微信开放平台审核通过的应用APPID
        $data["body"] 		      = $this->body;//商品或支付单简要描述
        $data["mch_id"] 	      = $this->config['mch_id'];//商户号
        $data["nonce_str"] 	      = $this->WxPayHelper->getRandChar(32);//随机字符串
        $data["notify_url"]       = $this->notify_url;//通知地址
        $data["out_trade_no"]     = $this->out_trade_no;//商户订单号
        $data["spbill_create_ip"] = $this->WxPayHelper->get_client_ip();//终端IP
        $data["total_fee"]        = $this->total_fee;//总金额
        $data["time_expire"]	  = $this->time_expire;//交易结束时间
        
        $data["trade_type"]   	  = $this->trade_type;//交易类型
        if($this->product_id){
        	$data["product_id"]   = $this->product_id;
        }

        $data["sign"] 			  = $this->WxPayHelper->getSign($data, $this->config['api_key']);//签名

        $xml 		= $this->WxPayHelper->arrayToXml($data);
        $response 	= $this->WxPayHelper->postXmlCurl($xml, $url);

        //将微信返回的结果xml转成数组
        $responseArr = $this->WxPayHelper->xmlToArray($response);
        //print_r($responseArr);
        if(!is_array($responseArr) || count($responseArr)<=0){
        	return false;
        }
        if('APP' != $this->trade_type){
        	if(!isset($responseArr['code_url'])) return false;
        	return $responseArr['code_url'];
        }
        if(!isset($responseArr['prepay_id'])) return false;
        
        return 	$this->getOrder($responseArr['prepay_id']);
	}
	
	/**
	 * 执行第二次签名，才能返回给客户端使用
	 * @param int $prepayId:预支付交易会话标识
	 * @return array
	 */
	public function getOrder($prepayId)
	{
		$data["appid"] 		= $this->config['appid'];
		$data["noncestr"] 	= $this->WxPayHelper->getRandChar(32);
		$data["package"] 	= "Sign=WXPay";
		$data["partnerid"] 	= $this->config['mch_id'];
		$data["prepayid"] 	= $prepayId;
		$data["timestamp"] 	= time();
		$data["sign"] 		= $this->WxPayHelper->getSign($data, $this->config['api_key']);
		// $data["packagestr"] = "Sign=WXPay";
		return $data;
	}
	
	/**
	 * 异步通知信息验证
	 * @return boolean|mixed
	 */
	public function verifyNotify()
	{
		$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
		if(!$xml){
			return false;
		}
		$wx_back = $this->WxPayHelper->xmlToArray($xml);
		if(empty($wx_back)){
			return false;
		}
		$checkSign = $this->WxPayHelper->getVerifySign($wx_back, $this->config['api_key']);		
		if($checkSign==$wx_back['sign']){
			return $wx_back;
		}
		return false;
	}
	
	function __destruct() {
		
	}
	
}

