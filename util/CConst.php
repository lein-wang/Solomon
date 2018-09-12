<?php
/**
 * desc: 最常用的常量
 *       要文件主要存放一些比较小的变量，不要存放大数据
 *
 *
 *
*/
class CConst {

    static $WX_APPID = "wx38b6c079b1d9f044";
    static $WX_APPSECRET = "247bceb3b417aa2f71ba2d927ddf7017";

    static $ERR_CODE_ACCOUNT_NO_LOGIN = -1;
    static $ERR_CODE_ACCOUNT_LOGIN_FAIL = 1000;
    static $ERR_CODE_ACCOUNT_INVALID = 1001;
    static $ERR_CODE_ACCOUNT_NLOGIN_REG = 1002;
    static $ERR_CODE_ACCOUNT_NLOGIN_LOGIN = 1003;
    static $ERROR_CODE = [
        -1=>'未能找到该账号，请重新登录',
        1000=>'账号或密码错误',
        1001=>'账号尚未激活',
        1002=>'普通账号密码注册',
        1003=>'普通账号密码登录',
    ];

    static $ACTION_TYPE = [//操作类型: 1-评论，2-赞， 3-回复
        1 => "评论",
        2 => "赞",
        3 => "回复",
    ];
    static $ACTION_TYPE_COMMENT = 1;
    static $ACTION_TYPE_PRAISE = 2;
    static $ACTION_TYPE_REPLY = 3;

    static $TARGET_TYPE = [//目标类型: 1-话题，2-动态，3-话题评论，4-动态评论
        1 => "话题",
        2 => "动态",
        3 => "话题评论",
        4 => "动态评论",
    ];
    static $TARGET_TYPE_TOPIC = 1;
    static $TARGET_TYPE_ACTIVE = 2;
    static $TARGET_TYPE_TOPIC_COMMENT = 3;
    static $TARGET_TYPE_ACTIVE_COMMENT = 4;

    static $LOGIN_EXPIRE = -1;//3600 * 24 * 30;

    static $IOS = 1;
    static $ANDRIOD = 2;
    static $PLATFORM = array(
        1 => "ios",
        2 => "android",
    );

    //帖子类型：1--普通，2--置顶，3--公告，4--攻略
    static $TOPIC_TYPE = [
        1=>'普通',
        2=>'置顶',
        3=>'公告',
        4=>'攻略',
    ];
    //帖子类型：0--未认证,1--正在审核，2--通过，-1--失败
    static $ID_STATUS = [
        0=>'未认证',
        1=>'正在审核',
        2=>'通过',
        3=>'失败',
    ];

    static $URL_TYPE = array(
        1 => "app界面",
        2 => "网页",
        3 => "启动游戏",
    );


    //账号类型： 1-游客，2-手机号，3-qq, 4-微信，5-微博，6-百度，
    static $ACCOUNT_TYPE_YOUKE = 1;
    static $ACCOUNT_TYPE_SHOUJI = 2;
    static $ACCOUNT_TYPE_QQ = 3;
    static $ACCOUNT_TYPE_WEIXIN = 4;
    static $ACCOUNT_TYPE_WEIBO = 5;
    static $ACCOUNT_TYPE_BAIDU = 6;
    static $ACCOUNT_TYPE = array(
        1 => "游客",
        2 => "手机号",
        3 => "QQ",
        4 => "微信",
        5 => "微博",
        6 => "百度",
        7 => "理发店虚拟账号",
    );
    //分享平台
    static $SHARE_PLATFORM_WX = 1;
    static $SHARE_PLATFORM_QQ = 2;
    static $SHARE_PLATFORM_FRIEND = 3;
    static $SHARE_PLATFORM_WEIBO = 4;
    static $SHARE_PLATFORM = array(
        //1-微信好友、2-qq空间、3-朋友圈、4-微博
        1=>"微信好友",
        2=>"qq空间",
        3=>"朋友圈",
        4=>"微博",
    );
    //安卓渠道
    //运营会提供渠道列表
    static $ANDROID_CHANNEL = array(
        -1=>"其他渠道",
        0=>"其他渠道",
        1=>"阿里",
        2=>"官网",
        3=>"渠道3",
        4=>"UC",
        5=>"360",
        6=>"应用宝",
        7=>"百度",
        8=>"小米",
        9=>"华为",
        10=>"OPPO",
        11=>"金立",
        12=>"酷派",
        13=>"魅族",
        14=>"VIVO",
        15=>"联想",
        16=>"酷安",
        17=>"安智",
    );



    //发送验证码的场景
    static $SMS_REG = 1;
    static $SMS_FORGET = 2;
    static $SMS_SHOP = 3; //店铺修改密码+修改绑定手机号
    
    //消息类型
    static $MSG_TYPE = array(
        1=>"banner",
        2=>"今日推荐",
        3=>"活动",
        4=>"系统消息",
        5=>"公告通知",
        6=>"店铺端banner",
        7=>"用户端比赛banner",
        8=>"一卡通banner",
        9=>"弹窗",
    );
    
//    //操作类型: 1-评论，2-赞， 3-关注， 4-取消关注
//    static $ACTION_TYPE_PL = 1;
//    static $ACTION_TYPE_ZAN = 2;
//    static $ACTION_TYPE_GZ = 3;
//    static $ACTION_TYPE_QXGZ = 4;
//    static $ACTION_TYPE_GZLFD = 5;
//    static $ACTION_TYPE= array(
//        1=>"评论",
//        2=>"赞",
//        3=>"关注",
//        4=>"取消关注",
//        5=>"关注理发店",
//    );
//    //target类型: 1-动态，2-作品，3-主打,4-点评
//    static $TARGET_TYPE_DT = 1;
//    static $TARGET_TYPE_ZP = 2;
//    static $TARGET_TYPE_ZD = 3;
//    static $TARGET_TYPE_DP = 4;
//    static $TARGET_TYPE_USHOW = 5;
//    static $TARGET_TYPE= array(
//        1=>"动态",
//        2=>"作品",
//        3=>"主打",
//        4=>"点评",
//        5=>"USHOW",
//    );
    
    // 1-banner，2-热门发型师，3-每日主打,4-精选店铺 ,5-店铺专题
    static $REC_TYPE_BANNER = 1;
    static $REC_TYPE_HOT = 2;
    static $REC_TYPE_MAIN = 3;
    static $REC_TYPE_JXDP = 4;
    static $REC_TYPE_DPZT = 5;
    static $REC_TYPE_USHOW = 6;
    static $REC_TYPE_USHOW_BOARD = 7;
    static $REC_TYPE = array(
        1=>"banner",
        2=>"热门造型师",
        3=>"热门发型",
        4=>"同城好店",
//        5=>"店铺专题",
        6=>"USHOW封面",
        7=>"USHOW排行榜封面",
    );
    //6-焕发拍照界面社区入口按钮, 7-焕发结束界面社区入口按钮,8-换发滤镜界面保存按钮,9-社区发现界面
    static $UVPV_TYPE = array(
        1=>"banner",
//        2=>"热门发型师",
        3=>"每日主打",
        4=>"精选店铺",
        5=>"店铺专题",
        6=>"焕发拍照界面社区入口按钮",
        7=>"焕发结束界面社区入口按钮",
        8=>"换发滤镜界面保存按钮",
        9=>"社区发现界面",
    );
    
    // 1-店长，2-技术总监，3-创意总监,4-首席发型师 ,5-资深发型师
    static $STYLIST_DEGREE_DIANZHANG = 1;
    static $STYLIST_DEGREE_JISHUZONGJIAN = 2;
    static $STYLIST_DEGREE_CHUANGYIZONGJIAN = 3;
    static $STYLIST_DEGREE_SHOUXIFAXINGSHI = 4;
    static $STYLIST_DEGREE_ZISHENGFAXINGSHI = 5;
    static $STYLIST_DEGREE = array(
        1=>"店长",
        2=>"技术总监",
        3=>"创意总监",
        4=>"首席发型师",
        5=>"资深发型师",
    );
    static $STYLIST_MAINAPPLY_PASS = 1;
    static $STYLIST_MAINAPPLY_PASSANDWEAR = 2;
    static $STYLIST_MAINAPPLY_REJECT = -1;
    static $STYLIST_MAINAPPLY_INPROCESS = 0;
    static $STYLIST_MAINAPPLY_STATUS = array(
        -1=>"驳回",
        0=>"申请中",
        1=>"通过展示",
        2=>"通过展示并试戴",
    );
    
    /**
     * 评分等级
     * @var type 
     */
    static $STYLIST_SCORE_DEGREE = array(
//        1=>"很差",
//        2=>"较差",
//        3=>"一般",
//        4=>"较好",
//        5=>"非常好",
        1=>"1星",
        2=>"2星",
        3=>"3星",
        4=>"4星",
        5=>"5星",
    );
    /**
     * 店铺标签
     * @var type 
     */
    static $STYLIST_SHOP_TAG = array(
        1=>"潮流",
        2=>"时尚",
        3=>"环境好",
        4=>"服务热情",
        5=>"种类多",
        6=>"效果好",
    );
    /**
     * 服务类型
     * @var type 
     */
    static $STYLIST_SHOP_SERVICE = array(
        0=>"其他",
        1=>"剪发",
        2=>"烫发",
        3=>"染发",
        4=>"护理",
        5=>"接发",
        6=>"洗色",
    );
    //提现手续费-对普通用户（注销一卡通）
    static $WITHDRAW_CASH_TAX = 0.03;
    //订单过期退款比例
    static $ORDER_EXPIRE_REFUND_TAX = 0.75;
    //每笔订单用户的收益
    static $ORDER_SHOP_BENEFIT_PERCENT = 0.8;
    
    //每笔订单一卡通支付 优惠额度
    static $ORDER_CARD_DISCOUNT = 0.85;
    //每笔订单 设计师收益比例 
    static $ORDER_DESIGNER_BENEFIT_PERCENT = 0.05;
    
    //订单中的用户角色
    static $PAY_USER_TYPE = array(
            1=>'用户',
            2=>'设计师',
            3=>'店铺',
    );
    //支付类型
    static $RECHARGE_TYPE = array(
            1=>'一卡通',
            2=>'支付宝',
            3=>'微信',
            4=>'平台充值'
    );
    //对账单数据记录 中type的类型
    static $CASH_CHECK_LIST_TYPE = array(
            1=>'支付',
            2=>'收益',
            3=>'提现',
            4=>'充值',
            5=>'充值赠送',
            6=>'系统充值',
            7=>'订单取消退款',
            8=>'提现驳回退款',
    );
    static $CASH_STATUS =array(
            1=>'申请中',
           // 2=>'审核通过-待打款',
            3=>'审核通过-已打款',
           // -2=>'审核通过-打款失败',
            -1=>'驳回',
    );
    static $CARD_STATUS = array(
            1=>'正常',
            -1=>'未激活',
            -2=>'注销申请',
            -3=>'已注销',
    );
    //订单状态
    static $ORDER_STATUS = array(
            10=>'待付款',
            50=>'已付款',
            100=>'已完成',
            110=>'完成评价',
            99=>'支付取消',
            -1=>'未支付取消'
    );
    static $HAIR_STATUS = array(
            1=>'店铺下架',
            2=>'店铺上架',
            3=>'用户下架',
            4=>'用户上架',
    );
    
    //每月限制提现次数
    static $WITHDRAWCASH_TIMES_PER_MONTH = 2;
    
    //热度来源：1-邀请,2-点击，3-试戴，4-分享，5-收藏，6-点赞，7-评论
    static $STYLIST_GAME_HOT_INVITE = 1;
    static $STYLIST_GAME_HOT_CLICK = 2;
    static $STYLIST_GAME_HOT_WEAR = 3;
    static $STYLIST_GAME_HOT_SHARE = 4;
    static $STYLIST_GAME_HOT_COLLECT = 5;
    static $STYLIST_GAME_HOT_PRAISE = 6;
    static $STYLIST_GAME_HOT_COMMENT = 7;
    static $STYLIST_GAME_HOT = array(
        1=>"邀请",
        2=>"点击",
        3=>"试戴",
        4=>"分享",
        5=>"收藏",
        6=>"点赞",
        7=>"评论",
    );
    //比赛标签的id
    static $STYLIST_GAME_TAG = 4;
    //比赛奖项配置
    static $STYLIST_GAME_AWARDS = array(
      ['type'=>1,'name'=>'冠军','field'=>'hot_total','rank'=>1],  
      ['type'=>2,'name'=>'最佳风尚奖','field'=>'hot_share','rank'=>1],  
      ['type'=>3,'name'=>'最佳新锐奖','field'=>'hot_wear','rank'=>1],  
      ['type'=>4,'name'=>'最佳口碑奖','field'=>'hot_praise','rank'=>1],  
    );
    static $SECRET_KEY = 'HF123456';
    
    //开通城市
    static $STYLIST_OPEN_CITY = [
        '北京市'
    ];
    //直辖市
    static $ZXS = [
        '北京市'=>11,
        '天津市'=>12,
        '上海市'=>31,
        '重庆市'=>50,
        '北京市'=>11,
    ];
    //评论类型
    static $COMMENT_TYPE= array(
        1=>"动态",
        2=>"作品",
        3=>"主打",
        4=>"点评",
        5=>"USHOW",
    );

    static $TAOCAN_TYPE = array(
        1 => "团购套餐",
        2 => "定制爆款",
    );
    static $TAOCAN_PROJECT_USE_TYPE = array(
        1 => "全部可用",
        2 => "多选1",
    );
    static $LIANXING = array(
        1 => "方形脸",
        2 => "菱形脸",
        3 => "三角形脸",
        4 => "椭圆形脸",
        5 => "心形脸",
        6 => "圆形脸",
        7 => "长方形脸",
        8 => "长形脸",
    );

}
