//common function
function parseUrl(url) {
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