var Util = (function () {
    var that;
	var obj = function () {
	    this.ajaxCount = 0;
	    that = this;
	};
	obj.prototype = {
		getQueryString: function (name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
			var r = window.location.search.substr(1).match(reg);
			if (r != null) return unescape(r[2]);
			return "";
		},
		getApiUrl: function (method, param) {
			var url = Config.Api.replace("{method}", method);
			if (param) {
				if (url.indexOf("?") < 0) url += "?";
				for (var key in param) {
					url += key + "=" + param[key] + "&";
				}
				url = url.substring(0, url.length - 1);
			}
			console.log(url);
			return url;
		},
		info: function (msg) {
			if (top != window) {
				top.Util.info(msg);
				return;
			}

			$(".pop_info").remove();
			var div = document.createElement("div");
			div.className = "pop_info";
			div.style.cssText = "position:fixed;top:40%;left:0;width:100%;text-align:center;z-index:10000;";
			var innerDiv = document.createElement("div");
			innerDiv.style.cssText = "display:inline-block;background-color:rgba(0,0,0,0.8);border-radius:3px;padding:5px 8px;color:white;";
			innerDiv.innerHTML = msg;
			div.appendChild(innerDiv);
			top.document.body.appendChild(div);
			setTimeout(function () {
				top.document.body.removeChild(div);
			}, 3000);
		},
		showmsg: function (msg, funok, funcancel, key) {
			//1.
			var id = "huijawghfjkghwjef";
			var html="<div class='btn-fade' id='"+id+"' style='z-index:9999999;'>";
			html+="<div class='btn-box'><div class='btn-title'>"+msg+"</div>";
			if(key == 'confirm'){
				html+="<div id='btn-cancel' class='btn-cancel br1'>取消</div>";
			}
			html+="<div class='btn-sure' >确定</div>";
			html+="</div>";
			//2.
			$('body').append(html);
			//3.
			var box = $('#'+id);
			box.show();
			box.find('.btn-sure').on('click',function(){
				if(funok!=undefined){
					funok();
				}
				box.remove();
			});
			box.find('#btn-cancel').on('click', function(){
				box.remove();
			});
		},
		redirect: function (url, param, isNewWindow) {
			url = this.getRedirectPath(url, param);
			if (!isNewWindow) {
				location.href = url;
			}
			else {
				window.open(url, "newwindow", "fullscreen=yes");
			}
		},
		
		getRedirectPath: function (url, param) {
			url = Config.RootPath + url;
			while (url.lastIndexOf("?") == url.length - 1
                || url.lastIndexOf("&") == url.length - 1) {
				url = url.substring(0, url.length - 1);
			}
			if (url.indexOf("?") < 0) {
				url += "?";
			} else {
				url += "&";
			}
			if (param) {
				for (var key in param) {
					url += key + "=" + param[key] + "&";
				}
			}
			return url;
		},

		ajax: function (option, callback) {
		    this.showLoading();
		    $.ajax({
		        type: option.method,
		        url: option.url,
		        data: option.data,
		        success: function (data) {
		            if (data && option.datatype!='html') {
		                data = JSON.parse(data);
		            }
		            that.hideLoading();
		            if (callback) {
		                    callback(data);
		                }
		            }
		        }
		    );
		},
		showLoading: function () {
			this.ajaxCount++;
			var loading = top.document.getElementById("loading");
			if (!loading) {
				loading = document.createElement("div");
				loading.id = "loading";
				loading.innerHTML = '<img src="/assets/common/images/loading.gif" />';
				top.document.body.appendChild(loading);
			}
		},
		hideLoading: function () {
			this.ajaxCount--;
			if (this.ajaxCount <= 0) {
				var loading = top.document.getElementById("loading");
				if (loading) {
					top.document.body.removeChild(loading);
				}
				this.ajaxCount = 0;
			}
		},

		alert: function (msg) {
			alert(msg);
		},
		isMobile: function (v) {
		    var reg = /^(1\d{10})$/;
		    return reg.test(v);
		},
		isInteger: function (v) {
		    var reg = /^(\-?\d+)$/;
		    return reg.test(v);
		},
		isEmpty: function (v) {
		    return !v || /^\s*$/.test(v);
		},
		zoomImg: function (src) {
		    var fade = document.createElement("div");
		    fade.style.cssText = "position:fixed;top:0;bottom:0;right:0;left:0;background-color:rgba(0,0,0,0.9);padding:20px 0;z-index:5";
		    fade.onclick = function () {
		        $(this).remove();
		    }
		    var imgDiv = document.createElement("div");
		    imgDiv.style.cssText = "width:100%;height:100%;background:url(" + src + ") center center no-repeat;background-size:contain;z-index:5";
		    fade.appendChild(imgDiv);

		    document.body.appendChild(fade);
		},
		getDateString: function (strDate) {
		    var r = /(\d+)/.exec(strDate);
		    if (r) {
		        var date = new Date(parseFloat(r[1]));
		        return date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
		    }

		    return "";
		},
		callPhone: function (tel) {
		    location.href = "tel:" + tel;
		},
		getDate: function (dateString) {
		    var reg = /(\d+)/;
		    if (reg.test(dateString)) {
		        return new Date(parseFloat(RegExp.$1));
		    }
		    return null;
		},
		getDateStr: function (strDate) {
		    var r = /(\d+)/.exec(strDate);
		    if (r) {
		        var date = new Date(parseFloat(r[1]));
		        return that.getDateStrT(date);
		    }

		    return "";
		},
		getDateStrT: function (dt) {
		    var m = dt.getMonth() + 1;
		    if (m < 10) {
		        m = "0" + m;
		    }
		    var d = dt.getDate();
		    if (d < 10) {
		        d = "0" + d;
		    }
		    return dt.getFullYear() + "-" + m + "-" + d;
		},
		timer:function(){  
			var ts = (new Date('2015-05-30 12:12')) - (new Date());//计算剩余的毫秒数  
			var dd = parseInt(ts / 1000 / 60 / 60 / 24, 10);//计算剩余的天数  
			var hh = parseInt(ts / 1000 / 60 / 60 % 24, 10);//计算剩余的小时数  
			var mm = parseInt(ts / 1000 / 60 % 60, 10);//计算剩余的分钟数  
			var ss = parseInt(ts / 1000 % 60, 10);//计算剩余的秒数  
			dd = that.checkTime(dd);  
			hh = that.checkTime(hh);  
			mm = that.checkTime(mm);  
			//ss = that.checkTime(ss); 
			
			var html="";
			html+="<div class='ddmmss'><font>"+dd+"</font><span>天</span><font>"+hh+"</font><span>时</span><font>"+mm+"</font><span>分</span></div>";
			$("#timer").html(html);
			//document.getElementById("timer").innerHTML = dd + "天" + hh + "时" + mm + "分"+ss+"秒" ;  
			//setInterval("Util.timer()",1000);  
		},
		checkTime:function(i){    
			   if (i < 10) {    
				   i = "0" + i;    
				}    
			   return i;    
			}

	};
	return new obj();
})();