define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {




            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'template/index' + location.search,
                    add_url: 'template/upload',
                    // import_url: 'template/upload',
                    // del_url: 'template/del',
                    edit_url: 'template/setting',
                    multi_url: 'template/multi',
                    setting_url: 'template/setting',
                    table: 'template',
                }
            });

            var table = $("#table");



            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                // templateView: true,
                pk: 'folder',
                sortName: 'id',
                commonSearch: false,
                visible: false,
                showToggle: false,
                showColumns: false,
                search:false,
                showExport: false,
                columns: [
                    [
                        // {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'description', title: __('描述')},
                        {field: 'author', title: __('Author')},
                        {field: 'version', title: __('Version')},
                        {
                            field: 'apply',
                            title: __('应用'),
                            formatter: function(value){
                                if(value == 'pc_mobile'){
                                    return '<span class="label"  title="" style="background: #00bcd4;"> 电 脑 </span>&nbsp;&nbsp;&nbsp;<span class="label"  title="" style="background: #ff5722;"> 手 机 </span>';
                                }else if(value == 'pc'){
                                    return '<span class="label"  title="" style="background: #00bcd4;"> 电 脑 </span>';
                                }else if(value == 'mobile'){
                                    return '<span class="label"  title="" style="background: #ff5722;"> 手 机 </span>';
                                }else if(value == 'none'){
                                    return '<span class="label"  title="" style="background: #868686;"> 未使用 </span>';
                                }
                            }
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            Table.button.edit = {
                name: 'edit',
                text: __('配置'),
                icon: 'fa fa-pencil',
                title: __('配置'),
                classname: 'btn btn-xs btn-success btn-editone'
            }

            Table.button.del = {
                name: 'del',
                text: __('删除'),
                icon: 'fa fa-trash',
                title: __('删除'),
                classname: 'btn btn-xs btn-danger btn-delone'
            }


            table.on("post-body.bs.table", function(){
                $(".btn-setting").data("area", ["1000px", "800px"]);
            });

            require(['upload'], function (Upload) {
                Upload.api.plupload("#plupload-addon", function (data, ret) {
                    Toastr.success(ret.msg);
                    table.bootstrapTable('refresh', {});
                });
            });



            $(document).on("click", ".set-all", function () {
                var id = $(this).attr('data-id');
                var data = {id:id};
                $.post("template/set_all", data, function(e){
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
            $(document).on("click", ".set-pc", function () {
                var id = $(this).attr('data-id');
                var data = {id:id};
                $.post("template/set_pc", data, function(e){
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
            $(document).on("click", ".set-mobile", function () {
                var id = $(this).attr('data-id');
                var data = {id:id};
                $.post("template/set_mobile", data, function(e){
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

            $(document).on("click", ".set-default", function () {
                var id = $(this).attr('data-id');
                var data = {id:id};
                $.post("template/set_default", data, function(e){
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
        add: function () {
            Controller.api.bindevent();
        },
        setting: function () {
            Controller.api.bindevent();
        },
        upload: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
