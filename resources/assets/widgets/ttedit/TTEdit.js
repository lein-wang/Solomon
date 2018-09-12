function getCaretPosition(ctrl){
    //获取光标位置函数 
    var CaretPos = 0; // IE Support 
    if (document.selection) {
        ctrl.focus(); 
        var Sel = document.selection.createRange (); 
        Sel.moveStart ('character', -ctrl.value.length); 
        CaretPos = Sel.text.length; 
    }else if (ctrl.selectionStart || ctrl.selectionStart == '0') {
        // Firefox support 
        CaretPos = ctrl.selectionStart;
    }
    return CaretPos; 
}
function setCaretPosition(ctrl, pos){//设置光标位置函数 
    if(ctrl.setSelectionRange){
        ctrl.focus();
        ctrl.setSelectionRange(pos,pos);
    }else if(ctrl.createTextRange){
        var range = ctrl.createTextRange(); 
        range.collapse(true); 
        range.moveEnd('character', pos); 
        range.moveStart('character', pos); 
        range.select(); 
    } 
}
;(function($,win){
    var TTEditor = function(element, settings){
        this.jEditor  = element;
        this.settings = settings;

        this.selected_index = -1;
        this.mutex = true;
        // this.resortNumber();
        //添加事件================================
        this._insert_tr_event();
        this._remove_tr_event();
        this._keyboard_listen_event();
    };
    TTEditor.prototype = {
        appendRow: function(index, callback, during){
            if('number' != typeof(index)){
                index = this.getRowCount() - 1;
            }
            return this.insertRow(index, callback, during, false);
        },
        insertRow: function(index, callback, during, isbefore){
            var that    = this;
            var jEditor = this.jEditor;
            var tr      = this._get_row(index);
            var columns = (jEditor.find('thead th').length || 4) - 1;
            new_tr = tr.clone();
            new_tr.find('[data-no-copy]').val('').attr('value','').empty();
            // alert(isbefore);
            if(that.settings.cleanatts){
                var _L = that.settings.cleanatts.split(',');
                for(var k in _L){
                    new_tr.find("["+_L[k]+"]").attr(_L[k], '');
                }
            }
            // console.log(new_tr.html());
            isbefore = 'boolean'==typeof(isbefore) ? isbefore : true;
            during = 'number'==typeof(during) ? during : 200;
            
            isbefore ? new_tr.insertBefore(tr) : new_tr.insertAfter(tr);
            that.resortNumber();
            if('function' == typeof(callback)){
                callback(new_tr);
            }
            if('function' == typeof(self.settings.changed)){
                that.settings.changed(new_tr,jEditor);
            }
            if(during < 1)return new_tr;
            new_tr.find('td,th').wrapInner('<div style="display: none;" />').parent().find('td > div,th > div').slideDown(during, function(){
                    var $set = $(this);
                    $set.replaceWith($set.contents());
            });
            return new_tr;
        },
        removeRow: function(index, callback, during){
            var tr = this._get_row(index);
            var cnt = this.getRowCount();
            // console.log(index,cnt);
            if(cnt <= 1) {//如果只剩一行了则清空数据
                tr.find("input").val('');
                tr.find('[data-no-copy]').empty();
                return;
            }
            this.jEditor.find('input').blur();
            var that = this;
            during = 'number'==typeof(during) ? during : 300;
            if(during < 1){
                tr.remove();
                that.resortNumber();
            }else{
                tr.find('th,td').animate({'paddingTop':0,'paddingBottom':0,height:0}).wrapInner('<div style="display: block;" />').parent().find('div').slideUp(during, function(){
                    tr.remove();
                    // self._syn_to_recvbox(self, jTable); //self
                    that.resortNumber();
                    if('function' == typeof(callback)){
                        callback();
                    }
                });
            }
        },
        removeAll: function(callback, during){
            var cnt = this.getRowCount();
            for(var i=cnt-1; i>=0; i--){
                this.removeRow(i, callback, during);
            }
        },
        //整理编号
        resortNumber: function(){
            var index = 0;
            this.jEditor.find("tbody tr").each(function(){
                index = $(this).index() + 1;
                if(index > 0){
                    $(this).find('th').eq(0).html(index);
                }
            });
            return index;
        },
        checkingRepeat: function(field){
            field = field || 'name';
            this.jEditor.find("input[name='"+field+"']").css('color', '');
            var colorlist = new Array('#DC143C','#008000','#FFA500','#B22222');
            var cnt = this.getRowCount();
            for(var i=0; i<cnt; i++){
                var tr = this._get_row(i);
                var input = tr.find("input[name='"+field+"']").eq(0);
                if(input.length < 1) continue;
                var v1 = input.val();
                var a=1;
                tr.siblings().each(function(){
                    var trX = $(this);
                    var inputX = trX.find("input[name='"+field+"']").eq(0);
                    if(inputX.length < 1) return;
                    var vX = inputX.val();
                    if(vX == v1){
                        inputX.css({color:colorlist[i%colorlist.length]});
                        input.css({color:colorlist[i%colorlist.length]});
                    }
                });
            }
        },
        //获取一行(实为获取一tr)
        _get_row: function(index){
            var index = index||(this.selected_index||0);
            index = index > this.getRowCount()-1 ? index-1 : index;
            var tr = this.jEditor.find("tbody tr").eq(index);
            if(0 == tr.length)
                tr = this.jEditor.find("tbody tr").last();
            return tr;
        },
        getRowCount: function(){
            return this.jEditor.find("tbody tr").length;
        },
        //===================以下是关于dom操作的内容=======================
        _insert_tr_event: function(){
            var that    = this;
            var jEditor = this.jEditor;
            jEditor.delegate("[data-insert-row]", 'click', function(){
                var tr = jEditor.find("tr:last");
                that.appendRow(null, function(new_tr){
                    new_tr.find("input[type='text']:first").trigger('click').focus();
                });
            })
        },
        _remove_tr_event: function(){
            var that    = this;
            var jEditor = that.jEditor;
            jEditor.delegate("[data-remove-row]", 'click', function(){
                var _cnt = that.getRowCount();
                var index   = that.selected_index > -1 ? that.selected_index : _cnt-1;
                if(index >= _cnt-1) index = _cnt-1;
                that.removeRow(index);
            })
        },
        _keyboard_listen_event: function(){
            var that    = this;
            var jEditor = that.jEditor;
            jEditor.delegate("tbody td", 'keydown click', function(e){
                var td = $(this);
                var tr = td.parent();
                var index = tr.index();
                if('click' == e.type){
                    that.selected_index = index;
                }
                var isAlt = e.altKey;
                var keyCode = e.keyCode;
                
                // console.log(event.keyCode);//39:->, 37:<-
                if(isAlt){
                    switch(keyCode){
                        case 189:
                            var trlast = jEditor.find("tr:last");
                            trlast.index()>0 && that.removeRow(index, function(){
                                jEditor.find("tr").eq(_idx_tr).find("input[type='text']:first").focus();
                            });   break;
                        default:
                            return; 
                    }
                }else{
                    switch(keyCode){
                        case 13:
                        case 40:
                            // console.log(index, that.getRowCount()-1, that.mutex);
                            if(index == that.getRowCount()-1 && that.mutex){
                                that.mutex = false
                                that.appendRow(null, function(newtr){
                                    newtr.find("td").eq(td.index()-1).find("input[type='text']:first").trigger('click').focus();
                                    that.mutex = true;
                                });
                            }else{
                                tr.next().find("td").eq(td.index()-1).find("input[type='text']:first").focus();
                            }
                            return false;
                            break;
                        case 38:
                            tr.prev().find("td").eq(td.index()-1).find("input[type='text']:first").focus();
                            break;
                        case 37:
                            // console.log(td.index())
                            var caretpos = getCaretPosition(td.find("input[type='text']:first")[0])
                            !caretpos && tr.find("td").eq(td.index()-2).find("input[type='text']:first").focus();
                            break;
                        case 39:
                            var _ctrl = td.find("input[type='text']:first");
                            var caretpos = getCaretPosition(_ctrl[0])
                            caretpos==_ctrl.val().length && tr.find("td").eq(td.index()).find("input[type='text']:first").focus();
                            break;
                        default:
                            return; 
                    }
                }
                
            });
        }
    };
    $.fn.extend({
        TTEdit: function(settings){
            var settings = settings || {};
            return new TTEditor($(this), $.extend({}, settings));
        }
    })
})(jQuery,window);