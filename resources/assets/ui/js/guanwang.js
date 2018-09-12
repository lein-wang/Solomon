
        var is_weixin = (function(){return navigator.userAgent.toLowerCase().indexOf('micromessenger') !== -1;})();
        var is_weibo = (function(){return navigator.userAgent.toLowerCase().indexOf('weibo') !== -1;})();

        $(function(){
            // 微信浏览器
            if(is_weixin||is_weibo)
            {
                $('.btn').click(function(){
                    // obj.css("display")=="none"
                    if($('.popup').is(':hidden'))
                    {
                        $(".popup").show();
                    }
                    else
                    {
                        $(".popup").hide();
                    }
                });

                $('.popup img').click(function(){
                    $('.popup').hide();
                });

            }
            // 其他浏览器
            else
            {
//                $('.btn').click(function(){
                $('#android').click(function(){
                    alert('敬请期待');return false;
                });
                $("#android").attr('href',"http://www.baidu.com"); 
//                $("#ios").attr('href',"http://www.baidu.com"); 
            }


            //根据不同客户端显示高亮按钮 
            var ua = navigator.userAgent.toLowerCase(); 
            if (/iphone|ipad|ipod/.test(ua)) {
                $("#ios").addClass("btn-primary"); 
                // alert("iphone");      
            } else if (/android/.test(ua)) {
                $("#android").addClass("btn-primary"); 
                // alert("android");   
            }


        })