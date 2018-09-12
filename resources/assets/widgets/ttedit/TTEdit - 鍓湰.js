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

        this.selected_index = 0;
        this.mutex = true;
        // this.resortNumber();
        //添加事件================================
        this._insert_tr_event();
        this._remove_tr_event();
        this._keyboard_listen_event();
    };
    TTEditor.prototype = {
        appendRow: function(index, callback){
            if('number' != typeof(index)){
                index = this.getRowCount() - 1;
            }
            return this.insertRow(index, callback, false);
        },
        insertRow: function(index, callback, isbefore){
            var that    = this;
            var jEditor = this.jEditor;
            var tr      = this._get_row(index);
            var columns = (jEditor.find('thead th').length || 4) - 1;
                new_tr = tr.clone();
                new_tr.find('[data-no-copy]').val('').empty();
                // alert(isbefore);
                isbefore = 'undefine'==typeof(isbefore) ? true : false;
            
            isbefore ? new_tr.insertBefore(tr) : new_tr.insertAfter(tr);

            new_tr.find('td,th').wrapInner('<div style="display: none;" />').parent().find('td > div,th > div').slideDown(200, function(){
                    var $set = $(this);
                    $set.replaceWith($set.contents());
                    that.resortNumber();
                    if('function' == typeof(callback)){
                        callback(jEditor.find("tr:last"));
                    }
                    if('function' == typeof(self.settings.changed)){
                        that.settings.changed(jEditor);
                    }
            });
            return new_tr;
        },
        removeRow: function(index, callback){
            var tr = this._get_row(index);
            if(index < 1) {//如果只剩一行了则清空数据
                tr.find("input").val('').empty();
                return;
            }
            this.jEditor.find('input').blur();
            var that = this;
            tr.find('th,td').animate({'paddingTop':0,'paddingBottom':0,height:0}).wrapInner('<div style="display: block;" />').parent().find('div').slideUp(300, function(){
                tr.remove();
                // self._syn_to_recvbox(self, jTable); //self
                that.resortNumber();
                if('function' == typeof(callback)){
                    callback();
                }
            });
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
        //获取一行(实为获取一tr)
        _get_row: function(index){
            return this.jEditor.find("tbody tr").eq(index||0);
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
                var index   = that.selected_index || that.getRowCount()-1;
                that.removeRow(index);
            })
        },
        _keyboard_listen_event: function(){
            var that    = this;
            var jEditor = that.jEditor;
            jEditor.delegate("tbody td", 'keydown', function(e){
                var isAlt = e.altKey;
                var keyCode = e.keyCode;
                var td = $(this);
                var tr = td.parent();
                var index = tr.index();
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
        },
    };
    $.fn.extend({
        TTEdit: function(settings){
            var settings = settings || {};
            return new TTEditor($(this), $.extend({}, settings));

            var tables = $(this);
            var self   = this;
                self.tpl_opration = '';
                self.mutex = true;
                self.settings = settings || {};
            tables.each(function(){
                var table = $(this);
                // console.log(table);
                self._syn_to_recvbox(self, table);
                self._resort_number(self, table);
                self._insert_tr_event(self, table);
                self._remove_tr_event(self, table);
                self._delete_tr_event(self, table);
                self._add_table_event(self, table);
                self._add_recvbox_event(self, table);
                self._switch_source_event(self, table);
                self._keyboard_listen_event(self, table);
            });
            if('function' == typeof(self.settings.change)){
                self.settings.change(self);
            }
        },
        _add_table_event: function(self, jEditor){
            jTable.delegate('td', 'click', function(){
                // 获取被点击的td  
                var td = $(this);
                if(td.attr("data-ttedit-edit"))return false;
                // 检测此td是否已经被替换了，如果被替换直接返回  
                if(td.children("input[type='text'],select").length > 0) {  
                    return false;
                }return true;
                var html_hidden = $('<p>').append(td.children("input[type='hidden']").clone()).html();
                // console.log(html_hidden);
                // 获取td中的文本内容  
                var text = td.text();  

                // 创建替换的input 对象  
                var recvbox = $("<input type='text'>").css({border:0,'outline':'none',"background-color":'transparent','width':td.width(),'padding':0,'margin':0,'font-size':td.css('font-size'),color:td.css('color'),'box-shadow':'none'}).val(text);
                // 设置value值  

                // 清除td中的文本内容  
                td.text("").append(recvbox);
                recvbox.focus();
                // 处理enter事件和esc事件  
                recvbox.on('keyup blur', function(event){
                    // 获取当前按下键盘的键值
                    var key = event.which;  
                    // 处理回车的情况  
                    if(key == 13 || 'blur' == event.type){  
                        var value = recvbox.val();  
                        td.html(value + html_hidden)
                        td.children("input[type='hidden']").val(value);
                        self._syn_to_recvbox(self, jTable);
                    }else if (key == 27){
                        td.html(value + html_hidden)
                        td.children("input[type='hidden']").val(value);
                        self._syn_to_recvbox(self, jTable);
                    }
                });  
            });
        },
        _add_recvbox_event: function(self, jTable){
            var receiveid = jTable.attr('data-receive') || null;
            if(!receiveid) return false;
            var recvbox = $('#'+receiveid);
            recvbox.on('blur', function(){
                self._syn_to_table(self, jTable, recvbox);
            })
        },
        _switch_source_event: function(self, jTable){
            jTable.find("[data-source]").on('click', function(){
                var receiveid = jTable.attr('data-receive') || null;
                if(!receiveid) return false;
                jTable.hide();
                $('#'+receiveid).show();
            })
        },
        _keyboard_listen_event: function(self, jTable){

            jTable.delegate("tbody td", 'keydown', function(e){
                var isAlt = e.altKey;
                var keyCode = e.keyCode;
                var td = $(this);
                var tr = td.parent();
                var _idx_tr = tr.index();
                // console.log(event.keyCode);//39:->, 37:<-
                if(isAlt){
                    switch(keyCode){
                        case 189:
                            var trlast = jTable.find("tr:last");
                            trlast.index()>0 && self.__remove_tr(self, jTable, tr, function(){
                                jTable.find("tr").eq(_idx_tr).find("input[type='text']:first").focus();
                            });   break;
                        default:
                            return; 
                    }
                }else{
                    switch(keyCode){
                        case 13:
                        case 40:
                            // console.log(self.mutex);
                            if(tr.index() == jTable.find('tr').length-2 && self.mutex){
                                self.mutex = false
                                self.__append_tr(self, jTable, tr, function(newtr){
                                    newtr.find("td").eq(td.index()-1).find("input[type='text']:first").trigger('click').focus();
                                    self.mutex = true;
                                });
                            }else{
                                // console.log(tr);
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
        },
        _insert_tr_event: function(self, jTable){
            jTable.delegate("[data-insert-row]", 'click', function(){
                var tr = jTable.find("tr:last");
                self.__append_tr(self, jTable, tr, function(newtr){
                    newtr.find("input[type='text']:first").trigger('click').focus();
                });
            })
        },
        _remove_tr_event: function(self, jTable){
            jTable.delegate("[data-remove-row]", 'click', function(){
                var tr = jTable.find("tr:last");
                tr.index()>0 && self.__remove_tr(self, jTable, tr);
            })
        },
        __append_tr: function(self, jTable, tr, callback){

            var columns = (jTable.find('thead th').length || 4) - 1;
                html_tr = tr.clone();
                html_tr.find('[data-no-copy]').val('').empty();
                // console.log(html_tr);
                if(jTable.find('tbody').find($(this)).length > 0){
                    if(jTable.find("tbody tr").length < 1){
                        jTable.append(html_tr);
                    }else{
                        var _idx = tr.index();
                        // $(html_tr).insertAfter(jTable.find('tr').eq(_idx+1));
                        $(html_tr).insertAfter(jTable.find('tr').eq(_idx+1)).find('td,th').wrapInner('<div style="display: none;" />').parent().find('td > div,th > div').slideDown(200, function(){
                                var $set = $(this);
                                $set.not('th').eq(1).trigger('click');
                                $set.replaceWith($set.contents());
                                self._resort_number(self, jTable);
                                if('function' == typeof(callback)){
                                    callback(jTable.find("tr").eq(_idx+1));
                                }
                                if('function' == typeof(self.settings.change)){
                                    self.settings.change(jTable);
                                }
                        });
                    }
                }else{
                    // jTable.append(html_tr);
                    // console.log(typeof(callback));
                    jTable.append(html_tr).find('tr:last').find('td,th').wrapInner('<div style="display: none;" />').parent().find('td > div,th > div').slideDown(200, function(){
                            var $set = $(this);
                            $set.replaceWith($set.contents());
                            self._resort_number(self, jTable);
                            if('function' == typeof(callback)){
                                callback(jTable.find("tr:last"));
                            }
                            if('function' == typeof(self.settings.change)){
                                self.settings.change(jTable);
                            }
                    });
                }
                self._syn_to_recvbox(self, jTable);
        },
        __remove_tr: function(self, jTable, tr, callback){
            tr.find('input').blur();
            tr.find('th,td').animate({'paddingTop':0,'paddingBottom':0,height:0}).wrapInner('<div style="display: block;" />').parent().find('div').slideUp(300, function(){
                tr.remove();
                self._syn_to_recvbox(self, jTable);
                self._resort_number(self, jTable);
                if('function' == typeof(callback)){
                    callback();
                }
                if('function' == typeof(self.settings.change)){
                    self.settings.change(jTable);
                }
            });
        },
        _delete_tr_event: function(self, jTable){
            jTable.delegate("tbody [data-delete-row]", 'click', function(){
                // $(this).parentsUntil("tbody").remove();
                var tr = $(this).parentsUntil("tbody");
                __remove_tr(self, tr);
            });
        },
        //整理编号
        _resort_number: function(self, jTable){
            jTable.find("tbody tr").each(function(){
                var index = $(this).index() + 1;
                if(index > 0){
                    $(this).find('th').eq(0).html(index);
                }
            })
        },
        //同步文本
        _syn_to_recvbox: function(self, jTable){
            var receiveid = jTable.attr('data-receive') || null;
            if(!receiveid) return false;
            var values = '';
            jTable.find("tbody tr").each(function(){
                var cell = '';
                $(this).find("td").each(function(){
                    cell += $(this).text() + "\t";
                });
                values += cell.replace(/\t+$/g,'') + "\n";
            });
            $('#'+receiveid).val(values);
        },
        _syn_to_table: function(self, jTable, jTbox){
            var columns = (jTable.find('thead th').length || 4) - 2;
            var values  = jTbox.val().replace(/^\s+/g,'').replace(/\s+$/g,'').replace("\r\n","\n");
            var val_arr = values.split("\n");
            var html_tbody = '';
            for(var i=0,len=val_arr.length; i<len; i++){
                var _row = val_arr[i];
                var _v_arr = _row.split("\t");
                var html_tr = "<th></th>";
                for(var c=0; c<columns; c++){
                    var _v = _v_arr[c] || '';
                    html_tr += "<td>"+ _v +"</td>";
                }
                html_tr = '<tr>' + html_tr + '<th>'+self.tpl_opration+'</th></tr>';
                html_tbody += html_tr;
            }
            jTable.find("tr:gt(0)").remove().end().append(html_tbody);
            self._resort_number(self, jTable);
            jTbox.hide();
            jTable.show();
        }
    })
})(jQuery,window);