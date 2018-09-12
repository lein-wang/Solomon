;(function($,win,doc){
    var html_ldd = '<div class="ldd" style="position:absolute;z-index:2000;outline:1px solid #ccc;background-color:#fff;display:none;overflow-x:hidden;">' +
    '<div class="ldd-wrapper">' +
    '<div class="searching-box" style="padding-right:18px;min-width:60px;">' +
    '<form onsubmit="return false;" ><span style="display:inline-block;height:28px;line-height:28px;overflow:hidden;border:1px solid #ddd;border-radius:15px;margin-bottom:4px;width:100%;white-space:nowrap;"><input type="text" name="q" autocomplete="off" placeholder="输入关键词..." style="padding:6px;outline:none;border:0;border-radius:15px;width:90%;box-shadow:none;" /><input type="submit" value="Q" style="margin-top:-2px;margin-top:-4px\\0;border-radius:15px;border:0;width:12px;background-color:transparent;cursor:pointer;outline:none;" /></span></form>' + 
    '<a style="position:absolute;cursor:pointer;top:2px;right:4px;padding:10px 4px;" ldd-close="1">×</a>' +
    '</div>' +
    '<div class="ldd-result"></div>' +
    '</div></div>';
    var style_ldd = '<style>.ldd{top:0;left:0;opacity:1;}.ldd .ldd-wrapper{position:relative;padding:8px;}.ldd .ldd-result{position:relative;}.ldd .ldd-result a{display:block;padding:8px 2px;border-top:1px solid #eee;cursor:pointer;margin-top:1px;font-size:13px;overflow:hidden;text-overflow:ellipsis;}.ldd .ldd-result a.active{background-color:#f6f6f6;}.ldd .ldd-result a:hover{color:#80abE6;}</style>';

    function isMobile(userAgent){
        userAgent = userAgent || navigator.userAgent;
        return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent));
    }
    function keyValue2object(str){    
        str = str.replace(/&/g,"','");    
        str = str.replace(/=/g,"':'");    
        str = "({'"+str +"'})";    
        return eval(str);     
    }

    $.fn.extend({
        Ldd: function(settings){
            settings = settings || {};
            var self  = this;
                self.mutex = true;
                self.settings = settings;

            var ctrls = $(this);
            var selector = ctrls.selector; //
            self.selector = selector;
            // console.log(ctrls, ctrls.length, selector, "a");
            
            self._create_panel(self); //创建面板

            //加入事件委托==================================
            $('body').delegate(selector, 'click keyup', function(){
                var jCtrl = $(this);
                // console.log(jCtrl, jCtrl.length);
                jCtrl.css({display:'inline-block'});
                self._add_show_event(self, jCtrl);      //显示面板事件
                self._handle_item_event(self, jCtrl);   //item点击事件

                if('function' == typeof(settings.click)){
                    settings.click(jCtrl);
                }
            });
            if(win.triggertimes && win.triggertimes > 1){}else{
                if(self.settings.init){
                    self.force_hide_panel = true;
                    $(self.selector).trigger('click');
                    self.force_hide_panel = false;
                    win.triggertimes = 2;
                }
            }
            // $(selector).trigger('click');
            //加入事件委托===============================end
            
            return self;
        },

        _create_panel: function(self, jCtrls){
            if(self.dom_ldd) return;
            var _k_ = '_dom_ldd_';// + self.settings.flag;
            if($('body').data(_k_)){//防止重复加载
                self.dom_ldd = $('body').data(_k_);
                return;
            }else{
                self.dom_ldd = $(html_ldd);
                $('body').data(_k_,self.dom_ldd).append(style_ldd).append(self.dom_ldd);
            }
            //清空数据=============================
            self._empty_result(self);
            //提示项===============================
            self._append_result(self, '在上面输入关键词...', '', '');
            //加入表单事件=========================
            self._add_submit_event(self);
            //添加关闭事件=========================
            self._add_close_event(self);
        },
        __get_to_offset: function(self, jCtrl){
            var offset = jCtrl.offset();
            
            var to_left  = offset.left + 1;
            var to_right = self.dom_ldd.width()+offset.left;
            if(to_right > $(doc.body).width()){
                to_left = offset.left - self.dom_ldd.outerWidth() + jCtrl.outerWidth();
            }
            var to_offsefs = {left:to_left,top:offset.top+jCtrl.outerHeight()};
            // console.log($(doc.body).width(),self.dom_ldd.width()+offset.left);
            if(isMobile()){
                to_offsefs.left  = 0;
                to_offsefs.right = 0;
            }
            return to_offsefs;
        },
        _handle_item_event: function(self, jCtrl, jCtrls){
            self.dom_ldd.undelegate('a[ldd-value]','click').delegate('a[ldd-value]', 'click', function(){
                // jCtrl.val($(this).attr('ldd-value'));
                if('checkbox'==jCtrl.attr('ldd-chaeck-type')) $(this).toggleClass('active');
                if('function' == typeof(self.settings.selected)){
                    self.settings.selected(jCtrl, $(this), $(this).data('item-data'), $(this).attr('ldd-value'));
                }
                return false;
            });

            self.settings.ajaxurl = jCtrl.attr('ldd-ajax-url') || (self.settings.ajax || '');
            self.settings.kname   = jCtrl.attr('ldd-kname') || (self.settings.kname || 'name');
            self.settings.key     = jCtrl.attr('ldd-key')  || null;
            self.settings.val     = jCtrl.attr('ldd-val') || null;
            // self.dom_ldd.find("form").trigger('submit');
            // if(!win.triggertimes)
            if(self.last_ajaxurl != self.settings.ajaxurl){
                self._add_submit_event(self, function(){
                    // alert(self.force_hide_panel);
                    if(self.force_hide_panel) self._set_selecteds(self, jCtrl);
                    self._set_actives(self, jCtrl);
                }, 'submit');
            }else{
                self._set_actives(self, jCtrl);
            }
            self.last_ajaxurl = self.settings.ajaxurl;

        },
        _set_selecteds: function(self, jCtrl){
            // console.log(self.dom_ldd.find('a[ldd-value]').length);
            var selecteds = jCtrl.attr('ldd-selecteds');
            if(selecteds){
                var slelist = selecteds.split(',');
                for(var i=0,len=slelist.length; i<len; i++){
                    self.dom_ldd.find('a[ldd-value="'+slelist[i]+'"]').trigger('click');
                }
            }
            
        },
        _set_actives: function(self, jCtrl){
            // self.dom_ldd.find('a[ldd-value]').removeClass('active');
            var selecteds = jCtrl.attr('ldd-selecteds');
            self.dom_ldd.find('a[ldd-value]').removeClass('active');
            if(selecteds){
                var slelist = selecteds.split(',');
                for(var i=0,len=slelist.length; i<len; i++){
                    self.dom_ldd.find('a[ldd-value="'+slelist[i]+'"]').addClass('active');
                }
            }
        },
        _empty_result: function(self){
            $('.ldd').find('.ldd-result').empty();
        },
        _append_result: function(self, name, val, data){
            var dom_item = $("<a ldd-value='"+val+"'>"+name+"</a>");
            data && dom_item.data('item-data', data);
            $('.ldd').find('.ldd-result').append(dom_item);
            // console.log(dom_item.data('item-data'));
        },
        _append_error_result: function(self, name){
            var dom_item = $("<a >"+name+"</a>").css({color:'red'});
            $('.ldd').find('.ldd-result').append(dom_item);
        },
        _add_show_event: function(self, jCtrl){
            var to_offsefs = self.__get_to_offset(self, jCtrl);
            if(self.force_hide_panel){//强制隐藏面板
                self.dom_ldd.hide();
            }else{
                self.dom_ldd.width(jCtrl.width()>360?360:(jCtrl.width()<80?80:jCtrl.width()));
                if(self.dom_ldd.is(':visible')){
                    self.dom_ldd.animate(to_offsefs);
                }else{
                    self.dom_ldd.show().css(to_offsefs);
                }
                $(win).off('resize').on('resize', function(){
                    var to_offsefs = self.__get_to_offset(self, jCtrl);
                    self.dom_ldd.animate(to_offsefs);
                });
            }

            // self.dom_ldd.find("form").trigger('submit');
            //根据当前单击的控件修改基本设置

            $(doc).off('click keyup').on('click keyup', function(e){
                // $(e.srcElement).css({border:'1px solid #f00'});
                // console.log(jCtrl[0]==(e.srcElement), e,$(e.srcElement), jCtrl);
                
                // console.log(jCtrl[0]);
                // console.log(e);
                // return;
                var src_ele = e.srcElement || e.toElement;
                if(jCtrl[0]== src_ele)return; //自己
                if(0 == self.dom_ldd.has(e.target).length && 0 == jCtrl.has(e.target).length)self.dom_ldd.slideUp(200);
            });
        },
        _add_close_event: function(self){
            self.dom_ldd.find('a[ldd-close]').on('click', function(){
                self.dom_ldd.slideUp(200);
            });
        },
        _add_submit_event: function(self, finished, triggered){
            self.dom_ldd.find("form").off('submit').on('submit', function(){
                var defaults = self.settings.paramters || {};
                if(self.settings.key && self.settings.val){
                    defaults[self.settings.key] = self.settings.val;
                }
                var data = $.extend(defaults, keyValue2object($(this).serialize()));
                var url  = self.settings.ajaxurl || (self.settings.ajax || '');
                // console.log(self.settings, url);
                $.ajax({
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    url: url,
                    async: false,
                    beforeSend: function(xhr){
                        self._empty_result(self);
                        self._append_error_result(self, '正在加载...');
                    },
                    success: function(json){
                        self._empty_result(self);
                        // console.log(json);
                        if(1 == parseInt(json.status)){
                            // self._append_result(self, '加载完成');
                            var results = json.data || {};
                            var kname = self.settings.kname || 'name';
                            for(var i in results){
                                var row = results[i];
                                self._append_result(self, row[kname], row['id'], row);
                            }
                            if('function' == typeof(finished)) finished(json);
                        }else{
                            self._append_error_result(self, json.message||'服务器繁忙，稍候再试');
                        }
                    },
                    error: function(xhr){
                        self._empty_result(self);
                        self._append_error_result(self, '没有搜索到数据');
                    }
                });
            }).trigger(triggered?'submit':'');
        },
        _get_submit_data: function(self, finished){

        }
    })
})(jQuery,window,document);