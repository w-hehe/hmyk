define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'plugin/index' + location.search,
                    add_url: 'plugin/add',
                    edit_url: 'plugin/edit',
                    del_url: 'plugin/del/',
                    multi_url: 'plugin/multi',
                    import_url: 'plugin/import',
                    table: 'plugin',
                }
            });

            var table = $("#table");



            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                // pk: 'id',
                // sortName: 'id',
                pagination: false,
                escape: false,
                columns: [
                    [
                        {field: 'name', title: '插件名称', operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'author', title: __('作者'), operate: 'LIKE'},
                        {
                            field: 'price',
                            title: __('价格'),
                            operate: false,
                            formatter:function(value,row,index){
                                if(value == 0){
                                    return `<span class="text-success">免费</span>`;
                                }else{
                                    return `<span class="text-danger">${value}</span>`;
                                }
                            }
                        },
                        {field: 'version', title: __('Version'), operate: 'LIKE'},
                        {
                            field: 'status',
                            title: __('状态'),
                            formatter:function(value,row,index){
                                if(value == 'enable'){
                                    return `<a href="javascript:;" class="btn btn-xs btn-success disable" data-plugin="${row.plugin}" data-toggle="tooltip" data-original-title="点击禁用">已启用</a>`;
                                }else if(value == 'disable'){
                                    return `<a href="javascript:;" class="btn btn-xs btn-default enable" data-plugin="${row.plugin}" data-toggle="tooltip" data-original-title="点击启用">已禁用</a>`;
                                }else{
                                    return '-';
                                    return `<a href="javascript:;" class="btn btn-xs btn-default">未安装</a>`;
                                }
                            }
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'dialog',
                                    title: __('配置'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-cog',
                                    url: 'plugin/setting/plugin_name/{plugin}',
                                    text:'配置',
                                    hidden:function(row){
                                        if(row.setting == false || row.install == false){
                                            return true;
                                        }
                                    }

                                },
                                {
                                    name: 'ajax',
                                    title: __('安装'),
                                    classname: 'btn btn-xs btn-success btn-click',
                                    icon: 'fa fa-wrench',
                                    text:'安装',
                                    click: function(data, row){
                                        var plugin_id = row.id;
                                        install(row);
                                    },
                                    hidden:function(row){
                                        if(row.install == true){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'ajax',
                                    title: __('升级'),
                                    classname: 'btn btn-xs btn-warning btn-click',
                                    icon: 'fa fa-wrench',
                                    text:'升级',
                                    click: function(data, row){
                                        upgrade(row);
                                    },
                                    hidden:function(row){
                                        if(row.upgrade == false){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'ajax',
                                    title: __('卸载'),
                                    classname: 'btn btn-xs btn-danger btn-magic btn-ajax',
                                    icon: 'fa fa-trash',
                                    confirm: '确认卸载这个插件？',
                                    text:'卸载',
                                    url: 'plugin/del/plugin_name/{plugin}',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh', {});
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        Toastr.error(ret.msg);
                                        return false;
                                    },
                                    hidden:function(row){
                                        if(row.install != true){
                                            return true;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });


            table.bootstrapTable('hideColumn', 'price');


            // 为表格绑定事件
            Table.api.bindevent(table);

            $('.local-plugin').click(function(){
                // $('.install-type .active').addClass('active');
                $('.cjsc-type').hide();
                $('.install-type').hide();
            })
            $('.all-plugin').click(function(){
                // $('.cjsc-type .active').addClass('active');
                $('.install-type').hide();
                $('.cjsc-type').css('display', 'inline-block');
            })

            var install = function (row) {

                Layer.confirm(__('您确定要安装这个插件吗? 如您想把其他域名的插件授权更换到当前域名下，请选择【更新授权】按钮。'), {
                    title: __('温馨提示'),
                    btn: ['安装插件', '更新授权', '取消']
                }, function (index) {
                    layer.close(index);
                    layer.load();
                    var url = 'plugin/install';
                    var data = {
                        plugin_id: row.id
                    };
                    $.post(url, data, function(e){
                        layer.closeAll();
                        if(e.code == 200){
                            Toastr.success(e.msg);
                            table.bootstrapTable('refresh', {});
                        }else if(e.code == 401){ //需要授权码
                            var area = [$(window).width() > 800 ? '600px' : '95%', $(window).height() > 600 ? '600px' : '95%'];
                            Layer.open({
                                content: Template("authorize_tpl", {"plugin_auth_id": e.data.plugin_auth_id,"qr_code": e.data.qr_code,"host": e.data.host,"out_trade_no": e.data.out_trade_no}),
                                zIndex: 99,
                                area: area,
                                title: '付费插件 - ' + e.data.plugin_name,
                                resize: false,
                                btn: ['立即绑定', '取消支付'],
                                yes: function (index, layero) {

                                    var data = {
                                        plugin_id: row.id,
                                        question: $('#c-question').val(),
                                        answer: $.trim($('#c-answer').val()),
                                        plugin_auth_id: $('#plugin-auth-id').val(),
                                        host: $.trim($('#c-host').val())
                                    };
                                    console.log(data)
                                    if(data.host == ''){
                                        Toastr.error("请输入您要绑定的域名");
                                        return;
                                    }
                                    if(data.question == ''){
                                        Toastr.error("请选择密保问题");
                                        return;
                                    }
                                    if(data.answer == ''){
                                        Toastr.error("请输入密保答案");
                                        return;
                                    }

                                    layer.load();
                                    $.post("plugin/bind_authorize", data, function(e){
                                        if(e.code == 400){
                                            layer.closeAll('loading');
                                            Toastr.error(e.msg);
                                        }else if(e.code == 200){
                                            Layer.closeAll();
                                            Layer.alert('该插件已授权给域名: ' + $.trim($('#c-host').val()) + ' 接下来可以使用该域名安装插件');
                                        }
                                    }, "json");

                                },
                                btn2: function () {

                                }
                            });
                        }else if(e.code == 4000){ //设置密保
                            var area = [$(window).width() > 800 ? '600px' : '95%', $(window).height() > 600 ? '320px' : '95%'];
                            Layer.open({
                                content: Template("question_tpl", {host:e.data.host, plugin_auth_id:e.data.id}),
                                zIndex: 99,
                                area: area,
                                title: '设置密保',
                                resize: false,
                                btn: ['立即绑定', '取消支付'],
                                yes: function (index, layero) {

                                    var data = {
                                        plugin_auth_id: $('#question-plugin-auth-id').val(),
                                        question: $('#c-question-question').val(),
                                        answer: $.trim($('#c-question-answer').val()),
                                        host: $.trim($('#c-question-host').val())
                                    };
                                    if(data.plugin_auth_id == ''){
                                        Toastr.error("参数错误，请刷新页面后重试");
                                        return;
                                    }
                                    if(data.host == ''){
                                        Toastr.error("请输入您要绑定的域名");
                                        return;
                                    }
                                    if(data.question == ''){
                                        Toastr.error("请选择密保问题");
                                        return;
                                    }
                                    if(data.answer == ''){
                                        Toastr.error("请输入密保答案");
                                        return;
                                    }

                                    layer.load();
                                    $.post("plugin/bind_authorize", data, function(e){
                                        if(e.code == 400){
                                            layer.closeAll('loading');
                                            Toastr.error(e.msg);
                                        }else if(e.code == 200){
                                            Layer.closeAll();
                                            Layer.alert('该插件已授权给域名: ' + $.trim($('#c-question-host').val()) + ' 接下来可以使用该域名安装插件');
                                        }
                                    }, "json");

                                },
                                btn2: function () {

                                }
                            });
                        }else{
                            Toastr.error(e.msg);
                        }
                    }, "json");

                }, function(){
                    var area = [$(window).width() > 800 ? '600px' : '95%', $(window).height() > 600 ? '370px' : '95%'];
                    Layer.open({
                        content: Template("auth_tpl", {}),
                        zIndex: 99,
                        area: area,
                        title: '更新授权 - ' + del_html_tags(row.name),
                        resize: false,
                        btn: ['更新授权', '取消'],
                        yes: function (index, layero) {

                            var data = {
                                plugin_id: row.id,
                                question: $('#c-auth-question').val(),
                                answer: $.trim($('#c-auth-answer').val()),
                                old_host: $.trim($('#c-auth-old-host').val()),
                                host: $.trim($('#c-auth-host').val())
                            };
                            if(data.plugin_id == ''){
                                Toastr.error("参数错误，请刷新页面后重试");
                                return;
                            }
                            if(data.old_host == ''){
                                Toastr.error("请输入旧的绑定的域名");
                                return;
                            }
                            if(data.question == ''){
                                Toastr.error("请选择密保问题");
                                return;
                            }
                            if(data.answer == ''){
                                Toastr.error("请输入密保答案");
                                return;
                            }
                            if(data.host == ''){
                                Toastr.error("请输入您要绑定的域名");
                                return;
                            }

                            layer.load();
                            $.post("plugin/update_auth", data, function(e){
                                if(e.code == 400){
                                    layer.closeAll('loading');
                                    Toastr.error(e.msg);
                                }else if(e.code == 200){
                                    Layer.closeAll();
                                    Layer.alert('该插件已授权给域名: ' + $.trim($('#c-auth-host').val()) + ' 接下来可以使用该域名安装插件。该插件授权剩余免费更换次数：' + e.data.surplus_change);
                                }
                            }, "json");

                        },
                        btn2: function () {

                        }
                    });
                }, function (index) {
                    layer.close(index);
                });

            };

            var upgrade = function (row) {

                Layer.confirm(__('您正在升级插件！<br>如您想把其他域名的插件授权更换到当前域名下，请选择【更新授权】按钮。'), {
                    title: __('温馨提示'),
                    btn: ['升级插件', '更新授权', '取消']
                }, function (index) {
                    layer.close(index);
                    layer.load();
                    var url = 'plugin/install/cmd/upgrade';
                    var data = {
                        plugin_id: row.id
                    };
                    $.post(url, data, function(e){
                        layer.closeAll();
                        if(e.code == 200){
                            Toastr.success(e.msg);
                            table.bootstrapTable('refresh', {});
                        }else if(e.code == 401){ //需要授权码
                            var area = [$(window).width() > 800 ? '600px' : '95%', $(window).height() > 600 ? '600px' : '95%'];
                            Layer.open({
                                content: Template("authorize_tpl", {"plugin_auth_id": e.data.plugin_auth_id,"qr_code": e.data.qr_code,"host": e.data.host,"out_trade_no": e.data.out_trade_no}),
                                zIndex: 99,
                                area: area,
                                title: '付费插件 - ' + e.data.plugin_name,
                                resize: false,
                                btn: ['立即绑定', '取消支付'],
                                yes: function (index, layero) {

                                    var data = {
                                        plugin_id: row.id,
                                        question: $('#c-question').val(),
                                        answer: $.trim($('#c-answer').val()),
                                        plugin_auth_id: $('#plugin-auth-id').val(),
                                        host: $.trim($('#c-host').val())
                                    };
                                    console.log(data)
                                    if(data.host == ''){
                                        Toastr.error("请输入您要绑定的域名");
                                        return;
                                    }
                                    if(data.question == ''){
                                        Toastr.error("请选择密保问题");
                                        return;
                                    }
                                    if(data.answer == ''){
                                        Toastr.error("请输入密保答案");
                                        return;
                                    }

                                    layer.load();
                                    $.post("plugin/bind_authorize", data, function(e){
                                        if(e.code == 400){
                                            layer.closeAll('loading');
                                            Toastr.error(e.msg);
                                        }else if(e.code == 200){
                                            Layer.closeAll();
                                            Layer.alert('该插件已授权给域名: ' + $.trim($('#c-host').val()) + ' 接下来可以使用该域名安装插件');
                                        }
                                    }, "json");

                                },
                                btn2: function () {

                                }
                            });
                        }else if(e.code == 4000){ //设置密保
                            var area = [$(window).width() > 800 ? '600px' : '95%', $(window).height() > 600 ? '320px' : '95%'];
                            Layer.open({
                                content: Template("question_tpl", {host:e.data.host, plugin_auth_id:e.data.id}),
                                zIndex: 99,
                                area: area,
                                title: '设置密保',
                                resize: false,
                                btn: ['立即绑定', '取消支付'],
                                yes: function (index, layero) {

                                    var data = {
                                        plugin_auth_id: $('#question-plugin-auth-id').val(),
                                        question: $('#c-question-question').val(),
                                        answer: $.trim($('#c-question-answer').val()),
                                        host: $.trim($('#c-question-host').val())
                                    };
                                    if(data.plugin_auth_id == ''){
                                        Toastr.error("参数错误，请刷新页面后重试");
                                        return;
                                    }
                                    if(data.host == ''){
                                        Toastr.error("请输入您要绑定的域名");
                                        return;
                                    }
                                    if(data.question == ''){
                                        Toastr.error("请选择密保问题");
                                        return;
                                    }
                                    if(data.answer == ''){
                                        Toastr.error("请输入密保答案");
                                        return;
                                    }

                                    layer.load();
                                    $.post("plugin/bind_authorize", data, function(e){
                                        if(e.code == 400){
                                            layer.closeAll('loading');
                                            Toastr.error(e.msg);
                                        }else if(e.code == 200){
                                            Layer.closeAll();
                                            Layer.alert('该插件已授权给域名: ' + $.trim($('#c-question-host').val()) + ' 接下来可以使用该域名安装插件');
                                        }
                                    }, "json");

                                },
                                btn2: function () {

                                }
                            });
                        }else{
                            Toastr.error(e.msg);
                        }
                    }, "json");

                },function(){
                    var area = [$(window).width() > 800 ? '600px' : '95%', $(window).height() > 600 ? '370px' : '95%'];
                    Layer.open({
                        content: Template("auth_tpl", {}),
                        zIndex: 99,
                        area: area,
                        title: '更新授权 - ' + del_html_tags(row.name),
                        resize: false,
                        btn: ['更新授权', '取消'],
                        yes: function (index, layero) {

                            var data = {
                                plugin_id: row.id,
                                question: $('#c-auth-question').val(),
                                answer: $.trim($('#c-auth-answer').val()),
                                old_host: $.trim($('#c-auth-old-host').val()),
                                host: $.trim($('#c-auth-host').val())
                            };
                            if(data.plugin_id == ''){
                                Toastr.error("参数错误，请刷新页面后重试");
                                return;
                            }
                            if(data.old_host == ''){
                                Toastr.error("请输入旧的绑定的域名");
                                return;
                            }
                            if(data.question == ''){
                                Toastr.error("请选择密保问题");
                                return;
                            }
                            if(data.answer == ''){
                                Toastr.error("请输入密保答案");
                                return;
                            }
                            if(data.host == ''){
                                Toastr.error("请输入您要绑定的域名");
                                return;
                            }

                            layer.load();
                            $.post("plugin/update_auth", data, function(e){
                                if(e.code == 400){
                                    layer.closeAll('loading');
                                    Toastr.error(e.msg);
                                }else if(e.code == 200){
                                    Layer.closeAll();
                                    Layer.alert('该插件已授权给域名: ' + $.trim($('#c-auth-host').val()) + ' 接下来可以使用该域名安装插件。该插件授权剩余免费更换次数：' + e.data.surplus_change);
                                }
                            }, "json");

                        },
                        btn2: function () {

                        }
                    });
                }, function (index) {
                    layer.close(index);
                });

            };

            function del_html_tags(str)
            {
                var words = '';
                words = str.replace(/<[^>]+>/g,"");
                return words;
            }


            // 一级切换
            $(document).on("click", ".btn-switch.btn-info", function () {
                var type = $(this).data('type');
                if(type == 'local'){
                    table.bootstrapTable('hideColumn', 'price');
                }
                if(type == 'all'){
                    table.bootstrapTable('showColumn', 'price');
                }
                $(".btn-switch.btn-info").removeClass("active");
                $(this).addClass("active");
                $(".btn-switch.btn-default").removeClass("active");
                $('.all-btn').addClass('active');
                $("form.form-commonsearch input[name='type']").val($(this).data("type"));
                table.bootstrapTable('refresh', {url: ($(this).data("url") ? $(this).data("url") : $.fn.bootstrapTable.defaults.extend.index_url), pageNumber: 1});
                return false;
            });

            //二级切换
            $(document).on("click", ".btn-switch.btn-default", function () {

                $(".btn-switch.btn-default").removeClass("active");
                $(this).addClass("active");
                $("form.form-commonsearch input[name='type']").val($(this).data("type"));
                table.bootstrapTable('refresh', {url: ($(this).data("url") ? $(this).data("url") : $.fn.bootstrapTable.defaults.extend.index_url), pageNumber: 1});
                return false;
            });



            //启用插件
            $(document).on("click", ".enable", function () {
                var plugin = $(this).data('plugin');
                layer.load();
                $.post("plugin/enable", {plugin:plugin, 'shelf': 0}, function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                    table.bootstrapTable('refresh', {});
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            });
//          禁用插件
            $(document).on("click", ".disable", function () {
                var plugin = $(this).data('plugin');
                layer.load();
                $.post("plugin/disable", {plugin:plugin, 'shelf': 1}, function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                    table.bootstrapTable('refresh', {});
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            });
        },

        setting: function () {
            Controller.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            userinfo: {
                get: function () {
                    var userinfo = localStorage.getItem("hmyk_userinfo");
                    console.log(userinfo)
                    return userinfo ? JSON.parse(userinfo) : null;
                },
                set: function (data) {
                    if (data) {
                        localStorage.setItem("hmyk_userinfo", JSON.stringify(data));
                    } else {
                        localStorage.removeItem("hmyk_userinfo");
                    }
                }
            },
        }
    };
    return Controller;
});
