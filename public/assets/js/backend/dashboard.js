define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {




    var Controller = {

        index: function () {
            initUpgrade();
            //初始化版本检测
            function initUpgrade(){
                var upgrade = $('#upgrade').data('upgrade');
                if(upgrade != 0){
                    upgradeModel(upgrade);
                }
            }

            //弹出更新框
            function upgradeModel(version){
                var area = [$(window).width() > 800 ? '500px' : '95%', $(window).height() > 600 ? '200px' : '95%'];
                Layer.open({
                    content: Template("upgrade_tpl", {"upgrade_text":version}),
                    zIndex: 99,
                    area: area,
                    title: '发现新版本',
                    resize: false,
                    btn: ['立即更新', '取消'],
                    yes: function (index, layero) {
                        upgradeFun();
                    },
                    btn2: function () {
                        Layer.closeAll();
                    }
                });
            }



            //查询广告接口
            var posterExists = $('#poster-box').data('exists');
            if(posterExists == 'false' || posterExists == false){
                $.get("dashboard/poster", function(e){

                    if(e.code == 200){
                        $('#poster-box').show()
                        $('#poster_url').attr('href', e.data.poster_url);
                        var poster_html = ``;
                        var notice_html = ``;
                        for(var i = 0; i < e.data.poster.length; i++){
                            poster_html += `
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3">
                                        <a href="${e.data.poster[i].url}" target="_blank">
                                            ${e.data.poster[i].content}
                                        </a>
                                    </td>
                                </tr>
                            `;
                        }
                        for(var i = 0; i < e.data.notice.length; i++){

                            notice_html += `
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3">
                                        <a href="${e.data.notice[i].url}" target="_blank">
                                            ${e.data.notice[i].content}
                                        </a>
                                    </td>
                                </tr>
                            `;
                        }
                        $('#poster-list').append(poster_html);
                        $('#notice-list').append(notice_html);
                    }
                }, "json");

            }


            function upgradeFun(){
                var index = layer.load();
                $.get("upgrade/index", function(e){
                    layer.close(index)
                    if(e.code == 200){ //版本段更新完成
                        console.log(e)
                        upgradeFun()
                    }else if(e.code == 400){ //更新出错啦~
                        $("#upgrade-text").html(e.msg);
                        Toastr.error(e.msg);
                    }else if(e.code == 201){ //版本全部更新完成
                        $("#upgrade-text").html(e.msg);
                        Toastr.success(e.msg);
                    }else{
                        $("#upgrade-text").html(e.msg);
                    }
                }, "json").error(function(e){
                    layer.close(index)
                    Toastr.error(e.status + ' ' + e.statusText);
                });
            }


            /**
             * 检查更新
             * */
            $(document).on("click", "#check-update", function(){
                var _this = $(this);
                if($(this).html() != "检测更新" && $(this).html() != "重新检测" && $(this).html() != "检测失败，请点击重试"){
                    return;
                }
                $(this).html("正在检测...");

                $.get("upgrade/checkUpgrade", function(e){
                    _this.html('检测更新');
                    if(e.code == 400){ //暂无更新
                        Toastr.success(e.msg);
                    }else if(e.code == 200){ //发现新版本
                        $('.upgrade-text').html("发现新版本v，老版本即刻停止维护，建议您立即更新！");
                        upgradeModel(e.data.version);
                    }else if(e.code == 401){ //beat版本不支持更新
                        Toastr.error(e.msg);
                    }else if(e.code == 402){ //检测出错
                        Toastr.error(e.msg);
                    }
                }, "json").error(function(){
                    $("#check-update").html('检查更新');
                    Toastr.error('检查失败，请重试');
                });

            });

            var lineChart = Echarts.init(document.getElementById('lineChart'), 'walden');
            var option = {
                title: {
                    text: '订单统计'
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['今日', '昨日']
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        // saveAsImage: {}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: JSON.parse($('#sts-order-date').html())
                },
                yAxis: {
                    type: 'value'
                },
                series: [
                    {
                        name: '成交额',
                        type: 'line',
                        color: '#4FA8F9',
                        areaStyle: {
                            color:'#E5F2FE'
                        },
                        data: JSON.parse($('#sts-order-sales-money').html())
                    },
                    {
                        name: '订单量',
                        type: 'line',
                        color: '#B9DCFD',
                        data: JSON.parse($('#sts-order-order-count').html())
                    }
                ]
            };

            lineChart.setOption(option);



        }
    };

    return Controller;
});
