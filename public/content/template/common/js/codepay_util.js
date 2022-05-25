var myTimer;
function timer(intDiff) {
    var i = 0;
    myTimer = window.setInterval(function () {
        i++;
        var day = 0,
            hour = 0,
            minute = 0,
            second = 0;//时间默认值
        if (intDiff > 0) {
            day = Math.floor(intDiff / (60 * 60 * 24));
            hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
            minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
            second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
        }
        if (minute <= 9) minute = '0' + minute;
        if (second <= 9) second = '0' + second;
        $('#hour_show').html('<s id="h"></s>' + hour + '时');
        $('#minute_show').html('<s></s>' + minute + '分');
        $('#second_show').html('<s></s>' + second + '秒');
        if (hour <= 0 && minute <= 0 && second <= 0) {
            qrcode_timeout()
            clearInterval(myTimer);

        }
        intDiff--;
    }, 1000);
}


try {
    document.ontouchstart = function () {
        $('#use').hide();
    }
} catch (e) {

}
function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var seperator2 = ":";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
        + " " + date.getHours() + seperator2 + date.getMinutes()
        + seperator2 + date.getSeconds();
    return currentdate;
}
myDate = function (s, t, g) {
    if (t == "null") {
        return "?"
    }
    s = "-" + s;
    s = s.toLocaleLowerCase();
    a = s.indexOf("y");
    b = s.indexOf("-m");
    c = s.indexOf("d");
    d = s.indexOf("h");
    e = s.indexOf(":m");
    f = s.indexOf("s");
    t ? date = new Date(t * 1000) : date = new Date();
    b < 0 ? as = "" : as = "-";
    c < 0 ? bs = "" : bs = "-";
    d < 0 ? cs = "" : cs = " ";
    e < 0 ? ds = "" : ds = ":";
    f < 0 ? es = "" : es = ":";
    g ? g1 = ":00" : g1 = ""; //设置显示时分秒则显示否则不显示
    g ? g2 = ":00" : g2 = "";

    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    a >= 0 ? a = date.getFullYear() + as : a = "";
    b >= 0 ? b = month + bs : b = "";
    c >= 0 ? c = strDate + cs : c = "";
    d >= 0 ? d = date.getHours() + ds : d = g1;
    e >= 0 ? e = date.getMinutes() + es : e = g1;
    f >= 0 ? f = date.getSeconds() : f = g2;
    var currentdate = a + b + c + d + e + f;
    return currentdate;
}
createLinkstring = function (para) {
    var arg = "";
    for (i in para) {
        if ((!para[i] && typeof para[i] != 'number') || i == 'password' || (i + '').toLowerCase() == 'creattime')continue; //跳过传递此类
        arg += i + "=" + encodeURIComponent(para[i]) + "&";
    }
    //去掉最后一个&字符
    arg = arg.substring(0, arg.length - 1)
    return arg;
}
function isMobile() {
    var ua = navigator.userAgent.toLowerCase();
    _long_matches = 'googlebot-mobile|android|avantgo|blackberry|blazer|elaine|hiptop|ip(hone|od)|kindle|midp|mmp|mobile|o2|opera mini|palm( os)?|pda|plucker|pocket|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino|maemo|fennec'
    _long_matches = new RegExp(_long_matches);
    _short_matches = '1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-'
    _short_matches = new RegExp(_short_matches);
    if (_long_matches.test(ua)) {
        return 1
    }
    user_agent = ua.substring(0, 4)
    if (_short_matches.test(user_agent)) {
        return 1
    }
    return 0
}

callback = function (data) {
    if(!data)return;
    var status = parseInt(data.status);
    if (!isNaN(status) && status >= 0) {
        try {
            data.id = user_data.codePay_id; //用户标识
        } catch (e) {
        }
        data.mobile = !data.mobile ? isMobile() : 1;
        startNotiry(data); //启用支付成功通知服务
        show_Qrcode(data); //显示二维码
        log(data);
    } else if (data.status == -8) {
        show_Qrcode(data); //显示二维码
        $("#msg h1").html("商家的软件未对接,付款后请等待商家确认。");
        // alert("管理员的软件未运行,付款后无法立即通知。管理员可以关闭调试模式来禁用此消息");
    } else {
        alert("订单创建失败 请更换其他支付方式或反馈管理员错误信息：" + data.msg);
    }
}
qrcode_timeout = function () { //二维码超时则停止显示二维码
    if (notifyServer && notifyServer.started)notifyServer.emit('notify_out', {});
    $("#show_qrcode").attr("src", '');
    $("#show_qrcode").attr("alt", '二维码失效');

    // $("#msg h1").html("支付超时 请重新提交订单"); //过期提醒信息
}
show_Qrcode = function (data) {
    var ua = navigator.userAgent.toLowerCase()
    if (!data)return;
    if (user_data && user_data.qrcode_url) {
        data.qrcode = user_data.qrcode_url + '?money=' + data.money + '&type=' + data["type"] + '&tag=' + data.tag;
    }
    if (data.qrcode)$("#show_qrcode").attr("src", data.qrcode); //二维码更新
    if (data.money){
        $("#money").html("￥"+data.money); //金额改变 云端匹配最佳金额
        $("#copy").attr("data-clipboard-text",data.money); //金额改变
    }
    var tps = data.money != data.price ? '<span style="color:red">为了您及时到账 请务必付款' + data.money + '元</span><br>' : '';
    if (data.message) { //创建后云端根据浏览器返回的tps提醒
        tps = data.message;
        //$("#msg h1").html(data.message);
    } else if (data.mobile) {
        if ((data["type"] == 3 && ua.indexOf('micromessenger') > 1) || (data["type"] == 2 && ua.indexOf('qq') > 1)) {
            tps += '长按识别该二维码 自动到账';
        } else {
            tps += '长按保存到相册使用' + (data.type == 3 ? '微信' : data.type == 2 ? 'QQ' : '支付宝') + '扫码';
        }
        // $("#msg h1").html(tps); //手机支付
    } else {
        tps += tps ? '' : '付款后自动到账 未到账可联系我们';
        // $("#msg h1").html('付款后自动到账 未到账可联系我们');
    }
    if(data.kfqq){
        $("#kf").html('客服QQ：'+data.kfqq+'<a target="_blank"  href="http://wpa.qq.com/msgrd?v=3&amp;uin='+data.kfqq+'&amp;site=qq&amp;menu=yes"><i class="fa fa-qq"></i></a>'); //客服QQ
    }
    $("#msg h1").html(tps);
    show_desc(data);
}
function getDescMode(key, value) {
    var reslut = value ? '<dt>' + key + '</dt><dd>' + value + '</dd>' : '';
    return reslut;
}
show_desc = function (data) { //商品描述
    var html = '';
    html += getDescMode('商品', user_data.subject||user_data.order_id||data.pay_id);
    html += user_data.order_id?getDescMode('单号', user_data.order_id):'';
    html += getDescMode('金额', "￥" + data.money);
    html += getDescMode('云端单号', data.order_id);
    html += getDescMode('创建时间', getNowFormatDate());
    html += getDescMode('过期时间', myDate("y-m-d h:m:s", data.endTime));
    $("#desc").html(html);
}
getApiHost = function (data) {
    if ((data && data.https) || 'https:' == document.location.protocol) { //走HTTPS通道
        return 'https://codepay.fateqq.com:51888'
    } else {
        return 'http://codepay.fateqq.com:52888'
    }
}
log = function (s) {
    try {
        console.log(s);
    } catch (e) {

    }
}

var notifyServer;
function startNotiry(data) {
    data.notiry_host = data.notiry_host || getApiHost(data);
    notifyServer = notify = io.connect(data.notiry_host);
    //notifyServer = notify = io.connect('http://127.0.0.1:52888');
    notify.emit('notify', data);
    notify.on('notify', function (o) { //同步通知服务返回
        log(o);
        notifyServer.started = true;
        if (!o)return;
        if (user_data && user_data.qrcode_url && o.money) {
            o.qrcode = user_data.qrcode_url + '?money=' + o.money + '&type=' + o["type"] + '&tag=' + o.tag;
        }
        if (o.qrcode && $("#show_qrcode").src != o.qrcode)$("#show_qrcode").attr("src", o.qrcode); //二维码
        if (o.money){
            $("#money").html("￥"+o.money); //金额改变
            $("#copy").attr("data-clipboard-text",o.money); //金额改变
        }
        if (o.msg)$("#msg h1").html(o.msg); //过期
        if (o.outTime) {
            try {
                clearInterval(myTimer);
            } catch (e) {
            }
            timer(parseInt(o.outTime));
        }

    });
    notify.on('sussecc', function (o) { //成功后跳转
        log(o);
        var status = parseInt(o.status);
        $("#show_qrcode").attr("src", '');
        $("#show_qrcode").attr("alt", '支付成功 1秒后自动跳转');
        if (!isNaN(status) && status > 1)$("#msg h1").html('支付成功 1秒后自动跳转');
        if (!user_data.return_url)user_data.return_url = 'https://codepay.fateqq.com/demo_show.html';
        var openUrl = user_data.return_url;
        var query = createLinkstring(o);
        openUrl += openUrl.indexOf('?') > 1 ? "&" + query : "?" + query;
        setTimeout(function () {
            location.href = openUrl;
        }, 1000); //延时1秒跳转

    });
    notify.on('disconnect', function (o) {//服务器断开 无法通知
        log(o);
    });
    notify.on('warning', function (o) {//系统通知
        if (o.msg)$("#msg h1").html(o.msg);
    });
}
$(document).ready(function () {
    $(function () {
        timer(user_data.outTime || 360);
    });

    $('#orderDetail .arrow').click(function (event) {
        if ($('#orderDetail').hasClass('detail-open')) {
            $('#orderDetail .detail-ct').slideUp(500, function () {
                $('#orderDetail').removeClass('detail-open');
            });
        } else {
            $('#orderDetail .detail-ct').slideDown(500, function () {
                $('#orderDetail').addClass('detail-open');
            });
        }
    });
});

