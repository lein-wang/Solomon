;(function($,WIN,doc,undef){
    var HTTP_STATUS = {
        400: '服务器不理解请求的语法', 
        401: '请求要求身份验证对于需要登录的网页，服务器可能返回此响应',
        403: '服务器拒绝请求或未登录',
        404: '服务器找不到请求的网页',
        405: '禁用请求中指定的方法',
        406: '无法使用请求的内容特性响应请求的网页。', 
        407: '此状态代码与 401（未授权）类似，但指定请求者应当授权使用代理。',
        408: '服务器等候请求时发生超时',
        409: '服务器在完成请求时发生冲突服务器必须在响应中包含有关冲突的信息',
        410: '如果请求的资源已永久删除，服务器就会返回此响应',
        411: '服务器不接受不含有效内容长度标头字段的请求',
        412: '服务器未满足请求者在请求中设置的其中一个前提条件',
        413: '服务器无法处理请求，因为请求实体过大，超出服务器的处理能力',
        414: '请求的 URI（通常为网址）过长，服务器无法处理',
        415: '请求的格式不受请求页面的支持',
        416: '如果页面无法提供请求的范围，则服务器会返回此状态代码',
        417: '服务器未满足"期望"请求标头字段的要求。',
        500: '服务器遇到错误，无法完成请求',
        501: '服务器不具备完成请求的功能例如，服务器无法识别请求方法时可能会返回此代码。', 
        502: '服务器作为网关或代理，从上游服务器收到无效响应',
        503: '服务器目前无法使用（由于超载或停机维护）通常，这只是暂时状态',
        504: '服务器作为网关或代理，但是没有及时从上游服务器收到请求',
        505: '服务器不支持请求中所用的 HTTP 协议版本'
    };

    //end 表单验证类
    /*
    *@settings ---  {
    *                   iformtype: dialog(对话框模式) | form(表单模式)
    *               }
    *
    */
    $.fn.extend({
        IXhr : function(settings){
            var self = this;
            settings = settings || {};
            settings.datatype = settings.datatype || {}
            self.settings = settings;
            var selector = $(this).selector.replace('[','').replace(']','');
            $(this).each(function(){
                var trigger = $(this); //触发按钮
                
                trigger.click(function(e){
                    var ixhr_ignore_alert   = $(this).attr('ixhr-ignore-alert') || false;
                    var ixhr_title          = $(this).attr('ixhr-title')  || null;
                    var ixhr_reload         = $(this).attr('ixhr-reload') || false;
                    var ixhr_redirect       = $(this).attr('ixhr-redirect') || null;
                    var ixhr_action         = $(this).attr('ixhr-action') || null;
                    var ixhr_formid         = $(this).attr('ixhr-formid') || null;
                    var ixhr_assign         = $(this).attr('ixhr-assign') || '';
                    var ixhr_only_valid     = $(this).attr('ixhr-only-valid') || false;

                    var data   = $(this).attr('ixhr-params') || null;
                    if(settings.params){
                        data = settings.params +'&'+ data;
                    }
                    if(ixhr_formid){
                        var jform = $('#'+ixhr_formid);
                        if(jform.length > 0){
                            if(ixhr_assign){
                                self.assign_values(self, jform, ixhr_assign)
                                return;
                            }
                            data += '&'+jform.serialize();
                        }
                        if(!self.valid_form(self, jform, settings)){
                            // alert('no valided');
                            return false;
                        }
                    }
                    if(ixhr_only_valid) return true;

                    var options = $.extend(self.settings, {action:ixhr_action,ignore_alert:ixhr_ignore_alert,reload:ixhr_reload,redirect:ixhr_redirect,assign:ixhr_assign});

                    if(ixhr_title){
                        // if(!confirm(ixhr_title))return false;
                        self._show_confirm({fcb:function(){
                            self.submit(self, jform, options, data);
                        },msg:ixhr_title}, e);
                    }else{
                        self.submit(self, jform, options, data);
                    }
                });
            });
            return $(this);
        },
        submit : function(self, JForm, options, data){
            $.ajax({url:options.action, type:'post', dataType:'json', data:data,  
                success:function(json){
                    json = json || {};
                    if(1 == parseInt(json.status)) {
                        if('function' == typeof(options.fcbOk)){
                            options.fcbOk(json);
                        }else{
                            if(!options.ignore_alert) {
                                // alert(json.message);
                                self._message(json.message);
                            }
                            if(ixhr_redirect){
                                window.location.href = ixhr_redirect;
                            }else if(ixhr_reload){
                                window.location.reload();
                            }
                        }
                    }else {
                        if('function' == typeof(options.fcbErr)){
                            options.fcbErr(json);
                        }else{
                            if(options.ignore_alert)return false;
                            // alert(json.message);
                            self._message(json.message);
                        }
                    }
                },error:function(data){
                    if('function' == typeof(options.fcbErr)){
                        options.fcbErr(data);
                    }else{
                        if(options.ignore_alert)return false;
                        try{
                            var status  = parseInt(data.status);
                            var message = HTTP_STATUS[status];
                        }catch(e){
                            var message = '服务器错误，请稍候试';
                        }
                        // alert(message);
                        self._message(message);
                    }
                }
            });
        },
        /*
        * desc: 验证表单
        *
        *return: true成功,false失败
        */
        valid_form : function(self, JForm, settings){
            var selector  = 'data-type';
            var allpassed = true;
            JForm.find("["+selector+"]").each(function(){
                var dt = $(this).attr(selector);
                if(!self.valid_ele(self, $(this), dt, JForm)){
                    return allpassed=false;
                }
            });
            return allpassed;
        },
        /*
        * desc: 验证表单元素
        *   data-type规则:
        *       imin-max
        *return: true成功,false失败
        */
        valid_ele : function(self, JEle, dt, JForm){
            var tv;               //临时变量
            dt = dt.toLowerCase();
            var vtype  = dt.slice(0,1); //第一个字符为变量类型
            var val    = JEle.val();    //表单元素的值
            var patt   = '';
            var min,max = 0;            //当vtype为数值是它们表示最小、最大值,当vtype为字符串时它们表示最小、最大长度
            if(min = dt.slice(1)){
                if((tv = dt.indexOf('-')) && (tv > 0)){
                    min = dt.slice(1,tv);
                    max = dt.slice(tv+1);
                }
            }
            min = parseFloat(min) || 0;
            max = parseFloat(max) || 0;
            var passed =  true;
            // self.settings.datatype = self.settings.datatype || {};
            if('function' == typeof(self.settings.datatype[dt])){
                passed = self.settings.datatype[dt](val,JEle,JForm);
            }else{
                switch(vtype){
                    case 'i':
                    case 'f':
                        patt = "[^0-9\-"+('f'==vtype?'\.':'')+"]";
                        var reg = new RegExp(patt, 'ig');
                        if(reg.test(val)){
                            passed = false;
                        }else{
                            val = parseFloat(val) || 0;
                            if(val < min || (max > 0 && val > max)){
                                passed = false;
                            }
                        }
                        break;
                    case '*':
                    default:
                        if(tv = val.length){
                            if(tv < min || (max > 0 && tv > max)){
                                passed = false;
                            }
                        }else{
                            passed = false;
                        }
                }
            }
            if(!passed){
                if(JEle.is(":visible")){
                    var old_bg = JEle.css('background-color');
                    var old_fg = JEle.css('color');
                    JEle.css({'background-color':'#FFC8F5'}).focus();
                    setTimeout(function(){
                        JEle.css({'background-color': old_bg});
                    }, 800);
                }else{
                    if(tv = JEle.attr('ixhr-data-alert')){
                        alert(tv);
                    }
                }
            }
            return passed;
        },
        /*设置默认值
         *
         *@defaults --- json
         *
        */
        assign_values : function(self, JForm, values){
            JForm.get(0).reset(); //清空数据
            var jValue = self._parse_url(values) || {};
            if(jValue){
                JForm.find('input,select,textarea').not('[type="button"]').each(function(){
                    var id      = $(this).attr('id');
                    var name    = $(this).attr('name');
                    var _name   = name?name.replace('[]',''):''; //预防表单数组
                    var tagName = $(this).get(0).tagName;
                    // alert(_name)
                    var val     = jValue[_name];
                    if(val){
                        val     = decodeURIComponent(jValue[_name]);
                        var isArr   = name.indexOf('[]');//表单数组
                        // console.log($(this).get(0).tagName,id,val,name,isArr);
                        if('SELECT' == tagName){
                            JForm.find('#'+id+" option[value='"+val+"']").attr('selected',true);
                        }else{ //input
                            var _type = $(this).attr('type')
                            // alert(_type);
                            if('radio'==_type){
                                JForm.find("[name='"+name+"'][value='"+val+"']").prop('checked',true);
                            }else if('checkbox'==_type){
                                if(isArr > -1){//表示些复选框数组
                                    var vArr = val.split(',');
                                    for(var k in vArr){
                                        var v = vArr[k]
                                        JForm.find("[name='"+_name+"\[\]'][value='"+v+"']").prop('checked',true);
                                        // alert(form.find("[name='city\[\]'][value='456']").attr('checked',true))
                                    }
                                }else{
                                    JForm.find("[name='"+name+"'][value='"+val+"']").prop('checked',true);
                                }
                            }else{
                                JForm.find('[name="'+name+'"]').val(val);
                            }
                        }
                    }
                });
            }
        },
        _message : function(msg) {
            msg = msg || '操作完成';
            var bar = $('<div style="background:#333;padding:9px;position:fixed;top:0;color:#eee;left:32%;right:48%;display:none;white-space:wrap;overflow:hidden;opacity:0.8;z-index:9999999;">__msg</div>'.replace('__msg',msg));
            bar.appendTo(doc.body).slideDown(200,function(){
                setTimeout(function(){bar.slideUp(function(){$(this).remove()})}, 3000);
            });
        },
        _show_confirm : function(settings, e){
            settings  = settings || {};
            var title = settings.title || '消息确认';
            var msg   = settings.msg || '是否继续？';
            var fcbConfirm = settings.fcb;
            //显示对话框
            var pageX = e?e.pageX:0;
            var pageY = e?e.pageY:0;
            var tpl_modal = ""
                + "<div style='display:none;position:absolute;border:1px solid #ddd;padding:1px;background:#fdfdfd;min-width:160px;'>"
                + "    <div style='position:relative;padding:6px;border-bottom:1px solid #eee;'>"
                + "        <h5 style='margin:0;padding:0;white-space:nowrap;padding-right:20px;'>--title--</h5>"
                + "        <a href='javascript:;' data-actoin='close' style='position:absolute;font-family:arial;top:6px;right:6px;'>×</a>"
                + "    </div>"
                + "    <div style='padding:8px;white-space:nowrap;font-size:13px;color:#666;'>--msg--"
                + "    </div>"
                + "    <div style='position:relative;padding:6px;border-top:1px solid #eee;text-align:right;'>"
                + "        <a href='javascript:;' style='font:12px bold arial;' data-actoin='close'>取消</a>"
                + "        <a href='javascript:;' style='font:12px bold arial;' data-actoin='ok'>确定</a>"
                + "    </div>"
                + "</div>";
            tpl_modal = tpl_modal.replace('--msg--',msg).replace('--title--',title);
            // console.log(pageX,pageY,e);
            var jConfirm = $(tpl_modal);
            jConfirm.appendTo(doc.body).show();
            var scroll_top  = $(doc).scrollTop();
            var scroll_left = $(doc).scrollLeft();
            var self_height = jConfirm.height();
            var body_height = $(doc.body).outerHeight();
            var body_width  = $(doc.body).width();
            var self_width  = jConfirm.width();
            jConfirm.css({left: pageX, top:pageY});
            if(e){
                if(pageY + self_height > body_height + scroll_top){
                    jConfirm.css({top: (pageY - self_height - 14)});
                }
                // console.log(pageX,self_width, body_width);
                if(pageX + self_width > body_width){
                    jConfirm.css({left: (pageX - self_width)});
                }
            }
            setTimeout(function(){
                $(doc.body).on('click', function(e){
                    if(jConfirm[0]== e.srcElement)return; //自己
                    if(jConfirm.has(e.srcElement).length > 0) return;
                    jConfirm.remove();
                    $(doc.body).off('click');
                });
            },200);
            if('function' == typeof(fcbConfirm)){
                jConfirm.find('a[data-actoin="ok"]').click(function(){
                    jConfirm.remove();
                    fcbConfirm();
                });
            }
            jConfirm.find('[data-actoin="close"]').click(function(){
                jConfirm.remove();
            });
            //end 显示对话框
        },
        /**
         * @url可以是http://www.baidu.com?id=45，也可以直接是后面的get值，比如id=45&price=98
        */
        _parse_url : function(url) {
            if('string' != typeof(url)) return null;
            var value = url.split("?")[1];
            if(value === undefined) value = url;
            var obj = {};
            if (typeof(value) != 'undefined' && value != '') {
                var temp = value.split("&");
                for(var i = 0; i < temp.length; i++) {
                    var str = temp[i].split("=");
                    obj[str[0]] = str[1];
                }
                return obj;
            }
            return null;
        }
    });
})(jQuery, window, document);
