define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            
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
