const api = "http://127.0.0.1:3509/api/";

/*if(equipment == 'pc'){
    $("body").css('width', '550px');
    $("body").css('background', '#39a4ff');
    $("body").css('margin', '0 auto');
    $(".bui-page").css('background', '#f8f8f8');
}*/

var uiLoading = bui.loading({
    width: 40,
    height: 40,
    autoClose: false,
    text: "加载中"
});



//跳转链接按钮
$('.jump').click(function(){
    var href = $(this).data('href');
    if(href == "111"){
        hint("功能研发中");
        return;
    }
    if(href){
        location.href = $(this).data('href');
    }else{
        hint('系统错误，链接未找到');
    }
});

//返回上一页按钮
$('.btn-back').click(function(){
    window.history.back();
});



function msg_error(msg){
    bui.hint({ content: "<i class='icon-close'></i><br />" + msg, position: "center", effect: "fadeInDown" });
}
function msg_succese(msg){
    bui.hint({ content: "<i class='icon-check'></i><br />" + msg, position: "center", effect: "fadeInDown" });
}

function hint(msg){
    bui.hint({
        "content": msg,
        "autoClose": true
    });
    hintWidth = $('.bui-hint-bottom').width();
    $(".bui-hint-bottom").css("margin-left", "-" + hintWidth/2 + "px");
}







