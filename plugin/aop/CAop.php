<?php
function classLoaderAop($class)
{
    // $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/request/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoaderAop');

require_once 'AopClient.php';

class CAop {
	
	function mkAppParamters($params=array())
	{
        if(!isset($params['out_trade_no'])) return '';
        if(!isset($params['total_amount'])) return '';
        
		$aop = new AopClient;
		
		//type=1 用户订单 type=3店铺订单  根据身份选择不同的支付宝appid
		if(isset($params['user_type']) && in_array($params['user_type'],array(1,3))){
		    $aop->appId = "2018012402057849";
		}else{
		    return '';
		}
		$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        //$aop->appId = "2017032106325352";
        // $aop->partner = "2088621158807114";

        //rsa_private_key_pkcs8.pem
        $aop->rsaPrivateKey = file_get_contents(__DIR__.'/pem/rsa_private_key_pkcs8.pem');
        $aop->rsaPrivateKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $aop->rsaPrivateKey);
        $aop->rsaPrivateKey = trim($aop->rsaPrivateKey);
        $aop->rsaPrivateKey = str_replace(array("\n","\r"), '', $aop->rsaPrivateKey);

        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = file_get_contents(__DIR__.'/pem/rsa_public_key.pem');
        $aop->alipayrsaPublicKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $aop->alipayrsaPublicKey);
        $aop->alipayrsaPublicKey = trim($aop->alipayrsaPublicKey);
        $aop->alipayrsaPublicKey = str_replace(array("\n","\r"), '', $aop->alipayrsaPublicKey);

        $request = new AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $body = isset($params['body'])?$params['body']:'';
        $subject = isset($params['subject'])?$params['subject']:'';
        $bizcontent = "{\"body\":\"{$body}\","
                        . "\"subject\": \"{$subject}\","
                        . "\"out_trade_no\": \"{$params['out_trade_no']}\","
                        . "\"timeout_express\": \"30m\","
                        . "\"total_amount\": \"{$params['total_amount']}\","
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}";
        $request->setNotifyUrl($params['notify_url']);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        return $response = $aop->sdkExecute($request);
        // echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
	}
    function verifyNotify()
    {
        /*if(!isset($params['sign'])) return false;
        if(!isset($params['out_trade_no']) ||
            !isset($params['sign_type']) ||
            !isset($params['notify_id']) ||
            !isset($params['trade_no']) ||
            !isset($params['seller_email'])
        ){
            return false;
        }
        if('13584876163@163.com' != $params['seller_email']){
            return false;
        }
        
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['sign_type']);
        ksort($params);*/

        $aop = new AopClient;

        $alipayrsaPublicKey = file_get_contents(__DIR__.'/pem/rsa_public_key.pem');
        $alipayrsaPublicKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $alipayrsaPublicKey);
        $alipayrsaPublicKey = trim($alipayrsaPublicKey);
        $alipayrsaPublicKey = str_replace(array("\n","\r"), '', $alipayrsaPublicKey);

        $aop->alipayrsaPublicKey = $alipayrsaPublicKey;
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        
        return $flag?true:false;
    }

    function mkTradeParamters($params=array())
    {
       
        exit;
        /*if(!isset($params['out_trade_no'])) return '';
        if(!isset($params['total_amount'])) return '';
        $aop = new AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2017032106325352";
        // $aop->partner = "2088621158807114";

        //rsa_private_key_pkcs8.pem
        $aop->rsaPrivateKey = file_get_contents(__DIR__.'/pem/rsa_private_key_pkcs8.pem');
        $aop->rsaPrivateKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $aop->rsaPrivateKey);
        $aop->rsaPrivateKey = trim($aop->rsaPrivateKey);
        $aop->rsaPrivateKey = str_replace(array("\n","\r"), '', $aop->rsaPrivateKey);

        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA";
        $aop->alipayrsaPublicKey = file_get_contents(__DIR__.'/pem/rsa_public_key.pem');
        $aop->alipayrsaPublicKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $aop->alipayrsaPublicKey);
        $aop->alipayrsaPublicKey = trim($aop->alipayrsaPublicKey);
        $aop->alipayrsaPublicKey = str_replace(array("\n","\r"), '', $aop->alipayrsaPublicKey);

        $request = new AlipayFundTransToaccountTransferRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $body = isset($params['body'])?$params['body']:'';
        $subject = isset($params['subject'])?$params['subject']:'';
        $bizcontent = "{\"remark\":\"转帐测试\","
                        . "\"subject\": \"转帐测试\","
                        . "\"out_biz_no\": \"{$params['out_biz_no']}\","
                        . "\"timeout_express\": \"30m\","
                        . "\"total_amount\": \"{$params['total_amount']}\","
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}";
        $request->getNotifyUrl($params['notify_url']);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        return $response = $aop->sdkExecute($request);*/
    }
    
    public function makeWithdrawCash($param){
        $aop = new AopClient;
        $aop->rsaPrivateKey = file_get_contents(__DIR__.'/pem/rsa_private_key_pkcs8.pem');
        $aop->rsaPrivateKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $aop->rsaPrivateKey);
        $aop->rsaPrivateKey = trim($aop->rsaPrivateKey);
        $aop->rsaPrivateKey = str_replace(array("\n","\r"), '', $aop->rsaPrivateKey);
        
        
        $aop->alipayrsaPublicKey = file_get_contents(__DIR__.'/pem/rsa_public_key.pem');
        $aop->alipayrsaPublicKey = preg_replace("/\-\-\-\-\-.+[\n\r]{1,2}/", '', $aop->alipayrsaPublicKey);
        $aop->alipayrsaPublicKey = trim($aop->alipayrsaPublicKey);
        $aop->alipayrsaPublicKey = str_replace(array("\n","\r"), '', $aop->alipayrsaPublicKey);
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018012402057849';
        
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        //$cashInfo['deal_no'] = mt_rand(10000000, 999999999);
        $request = new AlipayFundTransToaccountTransferRequest();
        $request->setBizContent("{" .
                "\"out_biz_no\":\"".$param['deal_no']."\"," .
                "\"payee_type\":\"ALIPAY_LOGONID\"," .
                "\"payee_account\":\"".$param['account']."\"," .
                "\"amount\":\"".($param['amount'])."\"," .
                "\"payer_show_name\":\"".$param['deal_name']."\"," .
                "\"payee_real_name\":\"".$param['username']."\"," .
                "\"remark\":\"\"" .
                "  }");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
         
        $resultCode = $result->$responseNode->code;
        
        if(!empty($resultCode)&&$resultCode == 10000){
            return array('status'=>1,'message'=>'ok','code'=>10000);
        } else {
            return array('status'=>0,'message'=>@$result->$responseNode->sub_msg,'code'=>@$result->$responseNode->code);
        }
    }
}