
//删除本地存储
function deleteStorage(key){
    localStorage.removeItem(key);
}

//设置本地存储
function setStorage(key, val){
    var type = Object.prototype.toString.call(val);
    if(type == '[object Object]'){
        localStorage.setItem(key, JSON.stringify(val));
    }else{
        localStorage.setItem(key, val);
    }

}

//获取本地存储
function getStorage(key){

    var data = localStorage.getItem(key);

    if(isJson(data)){
        return $.parseJSON(data);
    }else{
        return data;
    }
}

function isJson(str){
    if (typeof str == 'string') {
        try {
            var obj = JSON.parse(str);
            if (typeof obj == 'object' && obj) {
                return true;
            } else {
                return false;
            }

        } catch (e) {
            return false;
        }
    }
}

/**
 * 获取单个参数
 * */
function getParam(name){
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) return unescape(r[2]); return null; //返回参数值
}
/**
 * 获取所有参数
 * */
function getParamAll(){
    var aQuery = window.location.href.split("?");  //取得Get参数
    var aGET = {};
    if(aQuery.length > 1){
        var aBuf = aQuery[1].split("&");
        for(var i=0, iLoop = aBuf.length; i<iLoop; i++){
            var aTmp = aBuf[i].split("=");  //分离key与Value
            aGET[aTmp[0]] = aTmp[1];
        }
    }
    return aGET;
}

