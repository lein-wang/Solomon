!function ($) {
    $.isMobile = function(userAgent){
        userAgent = userAgent || navigator.userAgent;
        return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent));
    }
}(window.jQuery);
//dialog=========================================================
!function($,win,doc) {
    

    var Dialog = function(element, settings){
        this.jDialog  = element;
        this.settings = settings;
        // console.log(element);
        this._add_backdrop_event();
        this._add_document_event();
        this.setBackdrop();
        this.setDrag();
        this.show();

    };
    Dialog.prototype = {
        show: function(){
            var that = this;
            if(that.jDialog.is(":visible")){
                that.hide();
            }else{
                console.log('show');
                that.backdrop.show();
                that.jDialog.show();
                // that.smartyLayout();
                if(that.settings.slide){
                    // that.jDialog.css({left:$(doc.body).width()}).animate({left:$(doc.body).width()*0.2}, 300, function(){
                    // });
                }
            }
        },
        hide: function(){
            var that = this;
            var during = that.settings.slide ? 300 : 0;
            that.jDialog.animate({/*left:$(doc.body).width()*/}, during, function(){
                that.jDialog.hide();
                that.backdrop.hide();
            });
        },
        setDrag: function(){
            if(this.settings.drag) this.jDialog.drag();
        },
        setPosition: function(){

        },
        _slide_right: function(callback){
            // var oLeft = this.jDialog.offset().left;

        },
        setBackdrop: function(){
            this.backdrop = $('<div class="modal-backdrop in" />').appendTo(doc.body)
        },
        removeBackdrop: function(){
            this.backdrop.hide();
            this.backdrop = null;
        },
        smartyLayout: function(){//智能计算位置
            var styles = {};

            var scroll_top  = $(doc).scrollTop();
            var scroll_left = $(doc).scrollLeft();
            styles.top   = (Math.abs($(doc.body).height() - this.jDialog.height()))/3 + scroll_top;
            styles.left  = (Math.abs($(doc.body).width() - this.jDialog.width()))/2 + scroll_left;

            /*
            this.jDialog.css(styles);
            if($.isMobile()){
                this.jDialog.css({left:0, right:0, width:'auto', position:'fixed',top:0,bottom:0});
            }

            if(this.settings.styles){
                this.jDialog.css(this.settings.styles);
            }
            */
            return styles;
        },
        yes: function(e) {
            var opts = this.settings;
            this.hide();
            if('function' == typeof(opts.fcbYes)){
                opts.fcbYes();
            }
        },
        _add_backdrop_event: function(){
            var that = this;
            $(doc.body).delegate(".modal-backdrop", "click touchend", function(){
                that.hide();
            });
        },
        _add_document_event: function(){
            var that = this
            if(this.settings.keyboard) {
                $(doc).on('keyup.dismiss.modal', function(e){
                    e.which == 27 && that.hide()
                })
            }else if (!this.isShown) {
                $(doc).off('keyup.dismiss.modal')
            }
            this.jDialog.delegate('[data-dismiss="modal"]', 'click.dismiss.modal', $.proxy(this.hide, this)).delegate('[data-yes="modal"]', 'click', $.proxy(this.yes, this));
        }
    };
    $.fn.extend({
        Modal: function(settings){
            var defaults = {
                backdrop: true,
                keyboard: true,
                show:     true,
                drag:     false,        //+++++++++
                remove:   false
            }
            var settings = settings || {};
            var dialog   = null;
            if(dialog = $(this).data('dialog')){
                dialog.show();
                return dialog;
            }
            dialog = new Dialog($(this), $.extend(defaults, settings));
            $(this).data('dialog', dialog);
            return dialog;
        }
    })
}(window.jQuery, window, document);
//dialog======================================================end

//drag===========================================================
;(function($){
    $.fn.extend({
        drag: function(){
            jObj = this;
            var pos = jObj.offset();
            jObj.dnX = pos.left;      //鼠标按下时的x坐标
            jObj.dnY = pos.top;       //鼠标按下时的y坐标
            // alert(this.offset().left);
            jObj.moved = false;
            jObj.on('mousedown', jObj.dragDown);
            $(document).on('mousemove', jObj.dragMove);
            $(document).on('mouseup',   jObj.dragEnd);
            jObj.find(':header').mousedown(function(evt){
                evt.preventDefault();
            });
        },
        dragDown: function(evt){
            // alert(_this.moved);
            jObj = $(this);           //些名非常重要,此表明了要拖拽对象,不至于被设置dragable的对象一起拖动
            // evt.preventDefault();
            // alert(jObj.attr('id'));
            var TN = evt.target.tagName;
            if(0 === TN.indexOf('H')) {
                jObj.moved = true;  //只有<Hn>才拖动
            }else{
                jObj.moved = false; //bootstrap特殊处理
            }
            
            jObj.msdnX = evt.clientX;  //鼠标按下时的位置
            jObj.msdnY = evt.clientY;  //鼠标按下时的位置
            // alert('mousedown:'+ evt.clientX);
        },
        dragMove: function(evt){
            // alert(this.moved);
            jObj.msmvX = evt.clientX;  //鼠标移动时的位置
            jObj.msmvY = evt.clientY;  //鼠标移动时的位置
            if(jObj.moved){
                var deltaX = jObj.msmvX - jObj.msdnX;
                var deltaY = jObj.msmvY - jObj.msdnY;
                var left = parseInt(jObj.offset().left) + deltaX;
                var top  = parseInt(jObj.offset().top)  + deltaY;
                jObj.css({left:left,top:top});
                // alert('mousemove:'+ deltaX);
                jObj.msdnX = jObj.msmvX;
                jObj.msdnY = jObj.msmvY;
            }
        },
        dragEnd:  function(){
            jObj.moved = false;
        }
    });
})(jQuery);
//drag========================================================end
