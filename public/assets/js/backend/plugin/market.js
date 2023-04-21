define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'plugin/market/index',
                    add_url: '',
                    edit_url: '',
                    del_url: '',
                    multi_url: '',
                    dragsort_url: ''
                }
            });

            var table = $("#table");

            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                pagination: false,
                commonSearch: false,
                search: false,
                columns: [
                    [
                        {
                            field: 'name',
                            title: __('插件名称'),
                            operate: 'LIKE'
                        },
                        {field: 'images', width: 200, title: __('演示图'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'description', title: __('介绍'), operate: 'LIKE'},
                        {
                            field: 'demo_url',
                            title: __('演示地址'),
                            formatter:function (value, row, index){
                                if(value == ''){
                                    return '<span style="font-size: 13px; color: #7d7d7d;">暂无演示地址</span>';
                                }else{
                                    return '<a href="' + value + '" target="_blank" data-toggle="tooltip" class="btn" data-original-title="" title="">点击前往演示地址</a>';
                                }

                            }
                        },

                        {
                            field: 'author',
                            title: __('作者'),
                            operate: 'LIKE',
                            formatter:function (value, row, index){
                                if(row.author_url == ''){
                                    return '<span>' + row.author + '</span>';
                                }else{
                                    return '<a href="' + row.author_url + '" target="_blank" data-toggle="tooltip" class="" data-original-title="" title="">' + row.author + '</a>';
                                }

                            }
                        },
                        {
                            field: 'price',
                            title: __('价格'),
                            formatter:function (value, row, index){
                                if(value == 0){
                                    return '<span style=" color: #18bc9c;">免费</span>';
                                }else{
                                    return '<span style="color: #f75444;">&yen; ' + value + '</span>';
                                }

                            }
                        },
                        {field: 'version', title: __('插件版本'), operate: 'LIKE'},
                        {
                            field: 'custom',
                            title: __('状态'),
                            table: table,
                            formatter: Controller.api.formatter.custom
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table, events: Table.api.events.operate,
                            width: 200,
                            buttons: [
                                {
                                    name: 'ajax',
                                    title: __('安装'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    icon: 'fa fa-wrench',
                                    text:'安装',
                                    click: function(data, row){
                                        var plugin_id = row.id;
                                        install(row.id);
                                    },
                                    hidden:function(row){
                                        if(row.exist == true){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'ajax',
                                    title: __('已安装'),
                                    classname: 'btn btn-xs btn-info btn-click disabled',
                                    icon: 'fa fa-wrench',
                                    text:'已安装',
                                    click: function(data, row){
                                        var plugin_id = row.id;
                                        install(row.id);
                                    },
                                    hidden:function(row){
                                        if(row.exist == false){
                                            return true;
                                        }
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.operate
                        },
                        {
                            field: 'operate2',
                            title: __('Operate'),
                            table: table, events: Table.api.events.operate,
                            width: 200,
                            buttons: [
                                {
                                    name: 'ajax',
                                    title: '升级',
                                    classname: 'btn btn-xs btn-warning btn-click',
                                    icon: 'fa fa-wrench',
                                    text:'升级',
                                    click: function(data, row){
                                        var plugin_id = row.id;
                                        install(row.id, 1);
                                    },
                                    hidden:function(row){
                                        if(row.upgrade == false){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'dialog',
                                    title: __('配置'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-cog',
                                    url: 'plugin/myplugin/setting/plugin_name/{english_name}',
                                    text:'配置',
                                    hidden:function(row){
                                        if(row.setting == false || row.upgrade == true){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'dialog',
                                    title: __('配置'),
                                    classname: 'btn btn-xs btn-success btn-dialog disabled',
                                    icon: 'fa fa-cog',
                                    url: 'plugin/myplugin/setting/plugin_name/{plugin}',
                                    text:'配置',
                                    hidden:function(row){
                                        if(row.setting == true){
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
                                    url: 'plugin/myplugin/delp/plugin_name/{english_name}',
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
                                        if(row.status == 'enable' || row.unload == false){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'ajax',
                                    title: __('卸载'),
                                    classname: 'btn btn-xs btn-danger btn-magic btn-ajax disabled',
                                    icon: 'fa fa-trash',
                                    confirm: '确认卸载这个插件？',
                                    text:'卸载',
                                    url: 'plugin/myplugin/delp/plugin_name/{english_name}',
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
                                        if(row.status == 'disable' && row.unload == true){
                                            return true;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            };

            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);



            table.bootstrapTable('hideColumn', 'operate2');
            table.bootstrapTable('hideColumn', 'custom');

            // 切换
            $(document).on("click", ".btn-switch", function () {

                $(".btn-switch").removeClass("active");
                $(this).addClass("active");
                $("form.form-commonsearch input[name='type']").val($(this).data("type"));
                var method = $(this).data("type") == 'local' ? 'hideColumn' : 'showColumn';
                table.bootstrapTable(method, 'price');
                table.bootstrapTable(method, 'images');
                table.bootstrapTable(method, 'operate');
                if($(this).data("type") == 'local'){
                    table.bootstrapTable('hideColumn', 'price');
                    table.bootstrapTable('hideColumn', 'images');
                    table.bootstrapTable('hideColumn', 'operate');
                    table.bootstrapTable('showColumn', 'operate2');
                    table.bootstrapTable('showColumn', 'custom');
                }else{
                    table.bootstrapTable('showColumn', 'price');
                    table.bootstrapTable('showColumn', 'images');
                    table.bootstrapTable('showColumn', 'operate');
                    table.bootstrapTable('hideColumn', 'operate2');
                    table.bootstrapTable('hideColumn', 'custom');
                }

                table.bootstrapTable('refresh', {url: ($(this).data("url") ? $(this).data("url") : $.fn.bootstrapTable.defaults.extend.index_url), pageNumber: 1});
                return false;
            });

            // 切换分类
            $(document).on("click", ".nav-category li a", function () {
                $(".nav-category li").removeClass("active");
                $(this).parent().addClass("active");
                $("form.form-commonsearch input[name='category_id']").val($(this).data("id"));
                table.bootstrapTable('refresh', {url: $(this).data("url"), pageNumber: 1});
                return false;
            });
            var tables = [];
            $(document).on("click", "#droptables", function () {
                if ($(this).prop("checked")) {
                    Fast.api.ajax({
                        url: "addon/get_table_list",
                        async: false,
                        data: {name: $(this).data("name")}
                    }, function (data) {
                        tables = data.tables;
                        return false;
                    });
                    var html;
                    html = tables.length > 0 ? '<div class="alert alert-warning-light droptablestips" style="max-width:480px;max-height:300px;overflow-y: auto;">' + __('The following data tables will be deleted') + '：<br>' + tables.join("<br>") + '</div>'
                        : '<div class="alert alert-warning-light droptablestips">' + __('The Addon did not create a data table') + '</div>';
                    $(html).insertAfter($(this).closest("p"));
                } else {
                    $(".droptablestips").remove();
                }
                $(window).resize();
            });

            // 会员信息
            $(document).on("click", ".btn-userinfo", function (e, name, version) {
                var that = this;
                var area = [$(window).width() > 800 ? '500px' : '95%', $(window).height() > 600 ? '400px' : '95%'];
                var userinfo = Controller.api.userinfo.get();
                if (!userinfo) {
                    Layer.open({
                        content: Template("logintpl", {}),
                        zIndex: 99,
                        area: area,
                        title: __('Login FastAdmin'),
                        resize: false,
                        btn: [__('Login'), __('Register')],
                        yes: function (index, layero) {
                            Fast.api.ajax({
                                // url: Config.api_url + 'api/user/login',
                                url: 'plugin/market/login',
                                type: 'post',
                                data: {
                                    account: $("#inputAccount", layero).val(),
                                    password: $("#inputPassword", layero).val(),
                                    version: Config.faversion,
                                }
                            }, function (data, ret) {
                                Controller.api.userinfo.set(data);
                                Layer.closeAll();
                                Layer.alert(ret.msg, {title: __('Warning'), icon: 1});
                                return false;
                            }, function (data, ret) {
                            });
                        },
                        btn2: function () {
                            window.open(Config.api_url + "index/user/register.html");
                            return false;
                        },
                        success: function (layero, index) {
                            this.checkEnterKey = function (event) {
                                if (event.keyCode === 13) {
                                    $(".layui-layer-btn0").trigger("click");
                                    return false;
                                }
                            };
                            $(document).on('keydown', this.checkEnterKey);
                            $(".layui-layer-btn1", layero).prop("href", Config.api_url + "index/user/register.html").prop("target", "_blank");
                        },
                        end: function () {
                            $(document).off('keydown', this.checkEnterKey);
                        }
                    });
                } else {
                    Fast.api.ajax({
                        // url: Config.api_url + 'api/user/index',
                        url: 'plugin/market/user',
                        data: {
                            uid: userinfo.id,
                            token: userinfo.token,
                            version: Config.faversion,
                        }
                    }, function (data) {
                        Layer.open({
                            content: Template("userinfotpl", userinfo),
                            area: area,
                            title: __('Userinfo'),
                            resize: false,
                            btn: [__('Logout'), __('Close')],
                            yes: function () {
                                Fast.api.ajax({
                                    url: Config.api_url + 'api/user/logout',
                                    data: {uid: userinfo.id, token: userinfo.token, version: Config.faversion}
                                }, function (data, ret) {
                                    Controller.api.userinfo.set(null);
                                    Layer.closeAll();
                                    Layer.alert(ret.msg, {title: __('Warning'), icon: 0});
                                }, function (data, ret) {
                                    Controller.api.userinfo.set(null);
                                    Layer.closeAll();
                                    Layer.alert(ret.msg, {title: __('Warning'), icon: 0});
                                });
                            }
                        });
                        return false;
                    }, function (data) {
                        Controller.api.userinfo.set(null);
                        $(that).trigger('click');
                        return false;
                    });

                }
            });

            //刷新授权
            $(document).on("click", ".btn-authorization", function () {
                var userinfo = Controller.api.userinfo.get();
                if (!userinfo) {
                    $(".btn-userinfo").trigger("click");
                    return false;
                }
                Layer.confirm(__('Are you sure you want to refresh authorization?'), {icon: 3, title: __('Warmtips')}, function () {
                    Fast.api.ajax({
                        url: 'addon/authorization',
                        data: {
                            uid: userinfo.id,
                            token: userinfo.token
                        }
                    }, function (data, ret) {
                        $(".btn-refresh").trigger("click");
                        Layer.closeAll();
                    });
                });
                return false;
            });

            var install = function (plugin_id, upgrade = 0) {
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;
                var token = userinfo ? userinfo.token : '';

                if (parseInt(uid) === 0) {
                    return Layer.alert('您当前未登录官方账号，请登录后继续操作', {
                        title: '温馨提示',
                        btn: ['立即登录'],
                        yes: function (index, layero) {
                            $(".btn-userinfo").trigger("click");
                        }
                    });
                }

                Fast.api.ajax({
                    url: 'plugin/market/install',
                    data: {
                        plugin_id: plugin_id,
                        uid: uid,
                        token: token,
                        upgrade: upgrade
                    }
                }, function (data, ret) {
                    Layer.closeAll();
                    $(".btn-refresh").trigger("click");
                }, function (data, ret) {
                    var area = Fast.config.openArea != undefined ? Fast.config.openArea : [$(window).width() > 650 ? '650px' : '95%', $(window).height() > 555 ? '555px' : '95%'];
                    if (ret && ret.code == 401) {
                        //如果登录已经超时,重新提醒登录
                        Controller.api.userinfo.set(null);
                        $(".btn-userinfo").trigger("click");
                        return;
                    }else if(ret && ret.code == -2){
                        Layer.alert(ret.msg);
                    }else if(ret && ret.code == 400){
                        Toastr.error(ret.msg)
                    }else if(ret && ret.code == -1){
                        top.Fast.api.open(ret.data.pay_url, __('Pay now'), {
                            area: area,
                            end: function () {
                                Fast.api.ajax({
                                    url: 'plugin/market/isbuy',
                                    data: {
                                        plugin_id: plugin_id,
                                        uid: uid,
                                        toekn: token,
                                        out_trade_no: ret.data.outno
                                    }
                                }, function () {
                                    top.Layer.alert(__('Pay successful tips'), {
                                        btn: [__('Continue installation')],
                                        title: __('Warning'),
                                        icon: 1,
                                        yes: function (index) {
                                            top.Layer.close(index);
                                            install(name, version);
                                        }
                                    });
                                    return false;
                                }, function () {
                                    console.log(__('Canceled'));
                                    return false;
                                });
                            }
                        });
                    }else{
                        Layer.alert('请求错误，请重试！如多次重试未能解决问题，请尽快联系开发者修复！');
                    }
                    return false;
                });
            };



            var operate = function (name, action, force, success) {
                Fast.api.ajax({
                    url: 'addon/state',
                    data: {name: name, action: action, force: force ? 1 : 0}
                }, function (data, ret) {
                    var addon = Config['addons'][name];
                    addon.state = action === 'enable' ? 1 : 0;
                    Layer.closeAll();
                    if (typeof success === 'function') {
                        success(data, ret);
                    }
                    Controller.api.refresh(table, name);
                }, function (data, ret) {
                    if (ret && ret.code === -3) {
                        //插件目录发现影响全局的文件
                        Layer.open({
                            content: Template("conflicttpl", ret.data),
                            shade: 0.8,
                            area: area,
                            title: __('Warning'),
                            btn: [__('Continue operate'), __('Cancel')],
                            end: function () {

                            },
                            yes: function () {
                                operate(name, action, true, success);
                            }
                        });

                    } else {
                        Layer.alert(ret.msg, {title: __('Warning'), icon: 0});
                    }
                    return false;
                });
            };

            var upgrade = function (name, version) {
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;
                var token = userinfo ? userinfo.token : '';
                Fast.api.ajax({
                    url: 'addon/upgrade',
                    data: {name: name, uid: uid, token: token, version: version, faversion: Config.faversion}
                }, function (data, ret) {
                    Config['addons'][name] = data.addon;
                    Layer.closeAll();
                    Controller.api.refresh(table, name);
                }, function (data, ret) {
                    Layer.alert(ret.msg, {title: __('Warning')});
                    return false;
                });
            };

            // 点击安装
            $(document).on("click", ".btn-install", function () {
                var that = this;
                var name = $(this).closest(".operate").data("name");
                var version = $(this).data("version");

                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;

                if (parseInt(uid) === 0) {
                    return Layer.alert(__('Not login tips'), {
                        title: __('Warning'),
                        btn: [__('Login now')],
                        yes: function (index, layero) {
                            $(".btn-userinfo").trigger("click", name, version);
                        },
                        btn2: function () {
                            install(name, version, false);
                        }
                    });
                }
                install(name, version, false);
            });

            // 点击卸载
            $(document).on("click", ".btn-uninstall", function () {
                var name = $(this).closest(".operate").data('name');
                if (Config['addons'][name].state == 1) {
                    Layer.alert(__('Please disable the add before trying to uninstall'), {icon: 7});
                    return false;
                }
                Template.helper("__", __);
                Layer.confirm(Template("uninstalltpl", {addon: Config['addons'][name]}), {focusBtn: false}, function (index, layero) {
                    uninstall(name, false, $("input[name='droptables']", layero).prop("checked"));
                });
            });

            // 点击配置
            $(document).on("click", ".btn-config", function () {
                var name = $(this).closest(".operate").data("name");
                Fast.api.open("addon/config?name=" + name, __('Setting'));
            });

            // 点击启用/禁用
            $(document).on("click", ".btn-enable,.btn-disable", function () {
                var name = $(this).data("name");
                var action = $(this).data("action");
                operate(name, action, false);
            });

            // 点击升级
            $(document).on("click", ".btn-upgrade", function () {
                var name = $(this).closest(".operate").data('name');
                if (Config['addons'][name].state == 1) {
                    Layer.alert(__('Please disable the add before trying to upgrade'), {icon: 7});
                    return false;
                }
                var version = $(this).data("version");

                Layer.confirm(__('Upgrade tips', Config['addons'][name].title), function (index, layero) {
                    upgrade(name, version);
                });
            });

            $(document).on("click", ".operate .btn-group .dropdown-toggle", function () {
                $(this).closest(".btn-group").toggleClass("dropup", $(document).height() - $(this).offset().top <= 200);
            });

            $(document).on("click", ".view-screenshots", function () {
                var row = Table.api.getrowbyindex(table, parseInt($(this).data("index")));
                var data = [];
                $.each(row.screenshots, function (i, j) {
                    data.push({
                        "src": j
                    });
                });
                var json = {
                    "title": row.title,
                    "data": data
                };
                top.Layer.photos(top.JSON.parse(JSON.stringify({photos: json})));
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        config: function () {
            $(document).on("click", ".nav-group li a[data-toggle='tab']", function () {
                if ($(this).attr("href") == "#all") {
                    $(".tab-pane").addClass("active in");
                }
                return;
                var type = $(this).attr("href").substring(1);
                if (type == 'all') {
                    $(".table-config tr").show();
                } else {
                    $(".table-config tr").hide();
                    $(".table-config tr[data-group='" + type + "']").show();
                }
            });

            Controller.api.bindevent();
        },
        api: {
            formatter: {
                title: function (value, row, index) {
                    if ($(".btn-switch.active").data("type") == "local") {
                        // return value;
                    }
                    var title = '<a class="title" href="' + row.url + '" data-toggle="tooltip" title="' + __('View addon home page') + '" target="_blank">' + value + '</a>';
                    if (row.screenshots && row.screenshots.length > 0) {
                        title += ' <a href="javascript:;" data-index="' + index + '" class="view-screenshots text-success" title="' + __('View addon screenshots') + '" data-toggle="tooltip"><i class="fa fa-image"></i></a>';
                    }
                    return title;
                },
                operate: function (value, row, index) {
                    return Template("operatetpl", {item: row, index: index});
                },
                toggle: function (value, row, index) {
                    if (!row.addon) {
                        return '';
                    }
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Click to toggle status') + '" class="btn btn-toggle btn-' + (row.addon.state == 1 ? "disable" : "enable") + '" data-action="' + (row.addon.state == 1 ? "disable" : "enable") + '" data-name="' + row.name + '"><i class="fa ' + (row.addon.state == 0 ? 'fa-toggle-on fa-rotate-180 text-gray' : 'fa-toggle-on text-success') + ' fa-2x"></i></a>';
                },
                author: function (value, row, index) {
                    var url = 'javascript:';
                    if (typeof row.homepage !== 'undefined') {
                        url = row.homepage;
                    } else if (typeof row.qq !== 'undefined' && row.qq) {
                        url = 'https://wpa.qq.com/msgrd?v=3&uin=' + row.qq + '&site=fastadmin.net&menu=yes';
                    }
                    return '<a href="' + url + '" target="_blank" data-toggle="tooltip" class="text-primary">' + value + '</a>';
                },
                price: function (value, row, index) {
                    if (isNaN(value)) {
                        return value;
                    }
                    return parseFloat(value) == 0 ? '<span class="text-success">' + __('Free') + '</span>' : '<span class="text-danger">￥' + value + '</span>';
                },
                downloads: function (value, row, index) {
                    return value;
                },
                version: function (value, row, index) {
                    return row.addon && row.addon.version != row.version ? '<a href="' + row.url + '?version=' + row.version + '" target="_blank"><span class="releasetips text-primary" data-toggle="tooltip" title="' + __('New version tips', row.version) + '">' + row.addon.version + '<i></i></span></a>' : row.version;
                },
                home: function (value, row, index) {
                    return row.addon && parseInt(row.addon.state) > 0 ? '<a href="' + row.addon.url + '" data-toggle="tooltip" title="' + __('View addon index page') + '" target="_blank"><i class="fa fa-home text-primary"></i></a>' : '<a href="javascript:;"><i class="fa fa-home text-gray"></i></a>';
                },
                custom: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="plugin/myplugin/status/cmd/' + row.status + '" data-id="' + row.english_name + '"><i class="fa ' + (row.status == 'disable' ? 'fa-toggle-on fa-flip-horizontal text-gray' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            userinfo: {
                get: function () {
                    var userinfo = localStorage.getItem("fastadmin_userinfo");
                    return userinfo ? JSON.parse(userinfo) : null;
                },
                set: function (data) {
                    if (data) {
                        localStorage.setItem("fastadmin_userinfo", JSON.stringify(data));
                    } else {
                        localStorage.removeItem("fastadmin_userinfo");
                    }
                }
            },
            refresh: function (table, name) {
                //刷新左侧边栏
                Fast.api.refreshmenu();
                //刷新插件JS缓存
                Fast.api.ajax({url: require.toUrl('addons.js'), loading: false}, function () {
                    return false;
                }, function () {
                    return false;
                });

                //刷新行数据
                if ($(".operate[data-name='" + name + "']").length > 0) {
                    var tr = $(".operate[data-name='" + name + "']").closest("tr[data-index]");
                    var index = tr.data("index");
                    var row = Table.api.getrowbyindex(table, index);
                    row.addon = typeof Config['addons'][name] !== 'undefined' ? Config['addons'][name] : undefined;
                    table.bootstrapTable("updateRow", {index: index, row: row});
                } else if ($(".btn-switch.active").data("type") == "local") {
                    $(".btn-refresh").trigger("click");
                }
            }
        }
    };
    return Controller;
});
