$(function(){
/*左侧的菜单管理特效start*/

    /*这个效果不起作用，需要放在frame框中才有效*/
    var choose=$("#middle .left");

    choose.children('h4').click(function(){

        /*有这个样式就在点击执行后合起子项*/
        if($(this).find('i').hasClass('ioff')){
            $("#middle .left h4 i").addClass('ioff');
            choose.children('ul').slideUp(500);
            $(this).next().slideDown(500);
            $(this).find('i').removeClass("ioff");
        }else{
            $(this).next().slideUp(500);
            $(this).find('i').addClass('ioff');
        }
    });

/*    var jishu=choose.children('h4').length;
    var any = Math.floor(Math.random()*jishu);*/

    /*默认有任意一个开启*/
    // choose.find('h4').trigger('click');
//    choose.find('h4').eq(daohang).trigger('click');
//daohang这个变量不需要了，跟着url走 --by wl
choose.find('a.on').closest('ul').prev('h4:first').trigger('click');

/*左侧的菜单管理特效end*/


/*<!-- 时间插件 -->
*/
$('.default_datetimepicker').datetimepicker({
  formatTime:'H:i',
  formatDate:'d.m.Y',
  //defaultDate:'8.12.1986', // it's my birthday
  // defaultDate:'+03.01.1970', // it's my birthday
  defaultTime:'00:00',
  timepickerScrollbar:false
});


})