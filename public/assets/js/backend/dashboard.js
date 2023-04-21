define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {





            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            let column = Config.column;
            let order_num = Config.order_num;
            let order_money = Config.order_money;

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '',
                    subtext: ''
                },
                color: [
                    // "#18d1b1",
                    "#3fb1e3",
                    "#626c91",
                    "#a0a7e6",
                    "#c4ebad",
                    "#96dee8"
                ],
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['订单数量', '订单金额']
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: column
                },
                yAxis: [{
                    type: 'value',
                    name: '订单数量',
                    axisLabel: {
                        formatter: '{value}'
                    }
                }, {
                    type: 'value',
                    name: '订单金额',
                    axisLabel: {
                        formatter: '{value}'
                    }
                }],
                grid: [{
                    left: 40,
                    top: 50,
                    right: 50,
                    bottom: 20
                }],
                series: [{
                    name: '订单数量',
                    type: 'line',
                    smooth: true,
                    yAxisIndex: 0,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: order_num
                },{
                    name: '订单金额',
                    type: 'line',
                    smooth: true,
                    yAxisIndex: 1,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: order_money
                }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

            $(window).resize(function () {
                myChart.resize();
            });

            $(document).on("click", ".btn-refresh", function () {
                setTimeout(function () {
                    myChart.resize();
                }, 0);
            });

            $('.btn-shijian').click(function(){
                layer.load()
                $('.btn-shijian').removeClass('active');
                $(this).addClass('active');
                $.post("dashboard/tongji", {shijian: $(this).data('type')}, function(e){
                    layer.closeAll()
                    $('#add-user').html(e.add_user);
                    $('#order-num').html(e._order_num);
                    $('#order-money').html(e._order_money);
                    $('#cost').html(e._cost);
                    $('#lirun').html(e._lirun);
                    console.log(e)
                    // myChart.clear()

                    // $('#echart').empty();
                    // document.getElementById('echart').removeAttribute('_echarts_instance_');
                    // myChart = Echarts.init(document.getElementById('echart'), 'walden');
                    myChart.setOption({
                        title: {
                            text: '',
                            subtext: ''
                        },
                        color: [
                            // "#18d1b1",
                            "#3fb1e3",
                            "#626c91",
                            "#a0a7e6",
                            "#c4ebad",
                            "#96dee8"
                        ],
                        tooltip: {
                            trigger: 'axis'
                        },
                        legend: {
                            data: ['订单数量', '订单金额']
                        },
                        toolbox: {
                            show: false,
                            feature: {
                                magicType: {show: true, type: ['stack', 'tiled']},
                                saveAsImage: {show: true}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: e.column
                        },
                        yAxis: [{
                            type: 'value',
                            name: '订单数量',
                            axisLabel: {
                                formatter: '{value}'
                            }
                        }, {
                            type: 'value',
                            name: '订单金额',
                            axisLabel: {
                                formatter: '{value}'
                            }
                        }],
                        grid: [{
                            left: 40,
                            top: 50,
                            right: 50,
                            bottom: 20
                        }],
                        series: [{
                            name: '订单数量',
                            type: 'line',
                            smooth: true,
                            yAxisIndex: 0,
                            areaStyle: {
                                normal: {}
                            },
                            lineStyle: {
                                normal: {
                                    width: 1.5
                                }
                            },
                            data: e.order_num
                        },{
                            name: '订单金额',
                            type: 'line',
                            smooth: true,
                            yAxisIndex: 1,
                            areaStyle: {
                                normal: {}
                            },
                            lineStyle: {
                                normal: {
                                    width: 1.5
                                }
                            },
                            data: e.order_money
                        }]
                    }, true);
                }, 'json');
            });


            
            let new_version = $('#new-version').val();

            if(new_version == 1){
                Layer.open({
                    content: Template("upgrade_tpl", {}),
                    zIndex: 99,
                    maxmin: true,
                    area: '50%',
                    title: '发现新版本',
                    resize: true,
                    btn: ['立即更新', '取消'],
                    yes: function (index, layero) {
                        upgradeFun();
                    },
                    btn2: function () {
                        Layer.closeAll();
                    }
                });
            }

            $('#jiance').click(function(){
                let loading = $(this).data('loading');
                if(loading == 1){
                    return ;
                }
                $(this).data('loading', 1);
                $(this).html('检测中');
                var index = layer.load();
                $.get("upgrade/jiance", function(e){
                    layer.close(index);
                    if(e.code == 400){
                        $('#jiance').data('loading', 0);
                        $('#jiance').html('检测版本');
                        Toastr.error(e.msg);
                    }
                    if(e.code == 200){
                        if(e.data == 0){
                            $('#jiance').data('loading', 0);
                            $('#jiance').html('当前已是最新版本');
                            Toastr.success('当前已是最新版本');
                        }
                        if(e.data == 1){
                            Layer.open({
                                content: Template("upgrade_tpl", {}),
                                zIndex: 99,
                                maxmin: true,
                                area: '50%',
                                title: '发现新版本',
                                resize: true,
                                btn: ['立即更新', '取消'],
                                yes: function (index, layero) {
                                    upgradeFun();
                                },
                                btn2: function () {
                                    Layer.closeAll();
                                }
                            });
                        }
                    }
                }).fail(function(e){
                    $('#jiance').data('loading', 0);
                    $('#jiance').html('检测版本');
                    layer.close(index);
                    Toastr.error(e.status + ' ' + e.statusText);
                });
            });
            
            
            
            
            //更新方法
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

        }
    };

    return Controller;
});
