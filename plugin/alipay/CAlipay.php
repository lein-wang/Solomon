<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

class CAlipay {

    var $parameter = array();

    //支付类型
    var $payment_type = "1";
    //必填，不能修改
    //服务器异步通知页面路径
    // var $notify_url = "http://www.xxx.com/bill/pay/notify_url.php";
    var $notify_url = "";
    //需http://格式的完整路径，不能加?id=123这类自定义参数

    //页面跳转同步通知页面路径
    // var $return_url = "http://www.xxx.com/create_direct_pay_by_user-PHP-UTF-8/return_url.php";
    var $return_url = "";
    //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

    //卖家支付宝帐户
    var $seller_email = '13584876163@163.com';//$_POST['WIDseller_email'];
    //必填

    //商户订单号
    // $out_trade_no = '57384952384975982347';//$_POST['WIDout_trade_no'];
    //商户网站订单系统中唯一订单号，必填

    //订单名称
    // $subject = '测试';//$_POST['WIDsubject'];
    //必填

    //付款金额
    // $total_fee = 10;//$_POST['WIDtotal_fee'];
    //必填

    //订单描述

    // $body = '这是一个测试';//$_POST['WIDbody'];
    //商品展示地址
    var $show_url = '';//$_POST['WIDshow_url'];
    //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html

    //防钓鱼时间戳
    var $anti_phishing_key = "";
    //若要使用请调用类文件submit中的query_timestamp函数

    //客户端的IP地址
    var $exter_invoke_ip = "58.210.18.172";
    //非局域网的外网IP地址，如：221.0.0.1

    var $_input_charset = 'utf-8';

    var $alipay_config  = array();

    function __construct()
    {
        require_once(dirname(__FILE__)."/alipay.config.php");
        $this->alipay_config = $alipay_config;
    }
    function _make_paramter($parameter)
    {
        $alipay_config = $this->alipay_config;
        $parameter['service']           = 'create_direct_pay_by_user';
        $parameter['partner']           = trim($alipay_config['partner']);
        $parameter['seller_email']      = $this->seller_email;
        $parameter['payment_type']      = $this->payment_type;
        $parameter['anti_phishing_key'] = time();//$this->anti_phishing_key;
        $parameter['exter_invoke_ip']   = $this->exter_invoke_ip;
        $parameter['_input_charset']    = $this->_input_charset;
        if(empty($parameter['notify_url']))$parameter['notify_url'] = $this->notify_url;
        if(empty($parameter['return_url']))$parameter['return_url'] = $this->return_url;
        if(empty($parameter['show_url']))$parameter['show_url']     = $this->show_url;
        
        $this->parameter = $parameter;
    }
    function makePayUrl($parameter)
    {
        // return make_pay_url($this->parameter);
        $this->_make_paramter($parameter);
        $parameter     = $this->parameter;
        $alipay_config = $this->alipay_config;
        $alipaySubmit  = $this->LoadAlipaySubmit();
        $query = $alipaySubmit->buildRequestParaToString($parameter);
        return 'https://mapi.alipay.com/gateway.do?'.$query;
    }
    function getParamters($parameter=array())
    {
        $this->_make_paramter($parameter);
        $AliSubmit = $this->LoadAlipaySubmit();
        $requests  = $AliSubmit->buildRequestPara($parameter);
        return array_merge($this->alipay_config, $this->parameter, $requests);
    }
    function makePayForm($btntext='确认')
    {
        $parameter     = $this->parameter;
        $alipay_config = $this->alipay_config;
        $alipaySubmit  = $this->LoadAlipaySubmit();
        return $alipaySubmit->buildRequestForm($parameter, "get", $btntext);
    }
    function LoadAlipaySubmit()
    {
        $alipay_config = $this->alipay_config;
        require_once(dirname(__FILE__)."/lib/alipay_submit.class.php");
        return new AlipaySubmit($alipay_config);
    }

    function LoadAlipayNotify()
    {
        $alipay_config = $this->alipay_config;
        require_once(dirname(__FILE__)."/lib/alipay_notify.class.php");
        $alipayNotify = new AlipayNotify($alipay_config);
        return $alipayNotify;
    }
};

/**************************请求参数**************************/

// //支付类型
// $payment_type = "1";
// //必填，不能修改
// //服务器异步通知页面路径
// $notify_url = "http://www.xxx.com/create_direct_pay_by_user-PHP-UTF-8/notify_url.php";
// //需http://格式的完整路径，不能加?id=123这类自定义参数

// //页面跳转同步通知页面路径
// $return_url = "http://www.xxx.com/create_direct_pay_by_user-PHP-UTF-8/return_url.php";
// //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

// //卖家支付宝帐户
// $seller_email = $_POST['WIDseller_email'];
// //必填

// //商户订单号
// $out_trade_no = $_POST['WIDout_trade_no'];
// //商户网站订单系统中唯一订单号，必填

// //订单名称
// $subject = $_POST['WIDsubject'];
// //必填

// //付款金额
// $total_fee = $_POST['WIDtotal_fee'];
// //必填

// //订单描述

// $body = $_POST['WIDbody'];
// //商品展示地址
// $show_url = $_POST['WIDshow_url'];
// //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html

// //防钓鱼时间戳
// $anti_phishing_key = "";
// //若要使用请调用类文件submit中的query_timestamp函数

// //客户端的IP地址
// $exter_invoke_ip = "";
// //非局域网的外网IP地址，如：221.0.0.1


/************************************************************/

//构造要请求的参数数组，无需改动
// $parameter = array(
//     "service" => "create_direct_pay_by_user",
//     "partner" => trim($alipay_config['partner']),
//     "payment_type"	=> $payment_type,
//     "notify_url"	=> $notify_url,
//     "return_url"	=> $return_url,
//     "seller_email"	=> $seller_email,
//     "out_trade_no"	=> $out_trade_no,
//     "subject"	=> $subject,
//     "total_fee"	=> $total_fee,
//     "body"	=> $body,
//     "show_url"	=> $show_url,
//     "anti_phishing_key"	=> $anti_phishing_key,
//     "exter_invoke_ip"	=> $exter_invoke_ip,
//     "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
// );

//建立请求

// $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
// echo $html_text;


