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
                        // {checkbox: true},
                        // {field: 'plugin', title: __('标识')},
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
                                        var userinfo = Controller.api.userinfo.get();
                                        var uid = userinfo ? userinfo.id : 0;
                                        console.log(userinfo)
                                        if (parseInt(uid) === 0) { //未登录
                                            return Layer.alert(__('您需要登录【云商学院】账号才可以继续操作！'), {
                                                title: __('温馨提示'),
                                                btn: [__('立即登录')],
                                                yes: function (index, layero) {
                                                    var area = [$(window).width() > 800 ? '500px' : '95%', $(window).height() > 600 ? '350px' : '95%'];
                                                    Layer.open({
                                                        content: Template("logintpl", {}),
                                                        zIndex: 99,
                                                        area: area,
                                                        title: '登录',
                                                        resize: false,
                                                        btn: ['登录', '取消'],
                                                        yes: function (index, layero) {
                                                            layer.load();
                                                            var data = {
                                                                email: $("#inputAccount", layero).val(),
                                                                password: $("#inputPassword", layero).val(),
                                                            };
                                                            $.post("plugin/login", data, function(e){
                                                                if(e.code == 400){
                                                                    layer.closeAll('loading');
                                                                    Toastr.error(e.msg);
                                                                }else if(e.code == 200){
                                                                    Controller.api.userinfo.set(e.data.user);
                                                                    Layer.closeAll();
                                                                    Toastr.success('登录成功');
                                                                }
                                                            }, "json");

                                                        },
                                                        btn2: function () {
                                                            Layer.closeAll();
                                                        }
                                                    });

                                                },
                                                btn2: function () {
                                                    alert('错误')
                                                    // install(name, version, false);
                                                }
                                            });
                                        }
                                        install(plugin_id);
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
                                        var plugin_id = row.id;
                                        var userinfo = Controller.api.userinfo.get();

                                        var uid = userinfo ? userinfo.id : 0;

                                        if (parseInt(uid) === 0) { //未登录
                                            return Layer.alert(__('您需要登录【云商学院】账号才可以继续操作！'), {
                                                title: __('温馨提示'),
                                                btn: [__('立即登录')],
                                                yes: function (index, layero) {
                                                    var area = [$(window).width() > 800 ? '500px' : '95%', $(window).height() > 600 ? '350px' : '95%'];
                                                    Layer.open({
                                                        content: Template("logintpl", {}),
                                                        zIndex: 99,
                                                        area: area,
                                                        title: '登录',
                                                        resize: false,
                                                        btn: ['登录', '取消'],
                                                        yes: function (index, layero) {
                                                            layer.load();
                                                            var data = {
                                                                account: $("#inputAccount", layero).val(),
                                                                password: $("#inputPassword", layero).val(),
                                                            };
                                                            $.post("plugin/login", data, function(e){
                                                                if(e.code == 400){
                                                                    layer.closeAll('loading');
                                                                    Toastr.error(e.msg);
                                                                }else if(e.code == 200){
                                                                    Controller.api.userinfo.set(e.data);
                                                                    Layer.closeAll();
                                                                    Toastr.success('登录成功');
                                                                }
                                                            }, "json");

                                                        },
                                                        btn2: function () {
                                                            Layer.closeAll();
                                                        }
                                                    });

                                                },
                                                btn2: function () {
                                                    alert('错误')
                                                    // install(name, version, false);
                                                }
                                            });
                                        }
                                        upgrade(plugin_id);
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

            var install = function (plugin_id) {

                Layer.confirm(__('您确定要安装这个插件吗?'), {
                    title: __('温馨提示'),
                    btn: [__('确定'), __('取消')]
                }, function (index) {
                    layer.close(index);
                    layer.load();
                    var userinfo = Controller.api.userinfo.get();
                    var uid = userinfo ? userinfo.id : 0;
                    var token = userinfo ? userinfo.token : '';
                    var expiretime = userinfo ? userinfo.expiretime : 0;
                    var url = 'plugin/install';
                    var data = {
                        plugin_id: plugin_id,
                        uid: uid,
                        token: token,
                        expiretime: expiretime
                    };
                    $.post(url, data, function(e){
                        layer.closeAll();
                        if(e.code == 200){
                            Toastr.success(e.msg);
                            table.bootstrapTable('refresh', {});
                        }else if(e.code == 401){ //需要授权码
                            var buy_link = e.data;
                            var area = [$(window).width() > 800 ? '500px' : '95%', $(window).height() > 600 ? '330px' : '95%'];
                            /*var userinfo = Controller.api.userinfo.get();
                            var uid = userinfo ? userinfo.id : 0;*/
                            Layer.open({
                                content: Template("authorize_tpl", {"href":buy_link}),
                                zIndex: 99,
                                area: area,
                                title: '请输入插件授权码',
                                resize: false,
                                btn: ['立即绑定', '取消'],
                                yes: function (index, layero) {
                                    layer.load();
                                    var data = {
                                        user_id:uid,
                                        plugin_id: plugin_id,
                                        authorize_code: $("#inputAuthorize", layero).val(),
                                    };
                                    $.post("plugin/bind_authorize", data, function(e){
                                        if(e.code == 4001){
                                            Layer.closeAll();
                                            Controller.api.userinfo.set(null)
                                            Toastr.error("您需要重新登录官网账号");
                                        }else if(e.code == 400){
                                            layer.closeAll('loading');
                                            Toastr.error(e.msg);
                                        }else if(e.code == 200){
                                            Layer.closeAll();
                                            Layer.alert(e.msg);
                                        }
                                    }, "json");

                                },
                                btn2: function () {
                                    Layer.closeAll();
                                }
                            });


                        }else{
                            Toastr.error(e.msg);
                        }
                    }, "json");

                }, function (index) {
                    layer.close(index);
                });

            };

            var upgrade = function (plugin_id) {

                Layer.confirm(__('您确定要升级这个插件吗? 如有配置的插件，升级后则需要重新配置插件信息，请知悉！'), {
                    title: __('温馨提示'),
                    btn: [__('确定'), __('取消')]
                }, function (index) {
                    layer.close(index);
                    layer.load();
                    var userinfo = Controller.api.userinfo.get();
                    var uid = userinfo ? userinfo.id : 0;
                    var token = userinfo ? userinfo.token : '';
                    var expiretime = userinfo ? userinfo.expiretime : 0;
                    var url = 'plugin/install';
                    var data = {
                        plugin_id: plugin_id,
                        uid: uid,
                        token: token,
                        expiretime: expiretime
                    };
                    $.post(url, data, function(e){
                        layer.closeAll();
                        if(e.code == 200){
                            Toastr.success('升级成功');
                            table.bootstrapTable('refresh', {});
                        }else if(e.code == 401){ //需要授权码
                            var buy_link = e.data;
                            var area = [$(window).width() > 800 ? '500px' : '95%', $(window).height() > 600 ? '330px' : '95%'];
                            /*var userinfo = Controller.api.userinfo.get();
                            var uid = userinfo ? userinfo.id : 0;*/
                            Layer.open({
                                content: Template("authorize_tpl", {"href":buy_link}),
                                zIndex: 99,
                                area: area,
                                title: '请输入插件授权码',
                                resize: false,
                                btn: ['立即绑定', '取消'],
                                yes: function (index, layero) {
                                    layer.load();
                                    var data = {
                                        user_id:uid,
                                        plugin_id: plugin_id,
                                        authorize_code: $("#inputAuthorize", layero).val(),
                                    };
                                    $.post("plugin/bind_authorize", data, function(e){
                                        if(e.code == 4001){
                                            Layer.closeAll();
                                            Controller.api.userinfo.set(null)
                                            Toastr.error("您需要重新登录官网账号");
                                        }else if(e.code == 400){
                                            layer.closeAll('loading');
                                            Toastr.error(e.msg);
                                        }else if(e.code == 200){
                                            Layer.closeAll();
                                            Layer.alert(e.msg);
                                        }
                                    }, "json");

                                },
                                btn2: function () {
                                    Layer.closeAll();
                                }
                            });


                        }else{
                            Toastr.error(e.msg);
                        }
                    }, "json");

                }, function (index) {
                    layer.close(index);
                });

            };



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
