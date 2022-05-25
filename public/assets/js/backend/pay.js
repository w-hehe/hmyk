define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'pay/index' + location.search,
                    // add_url: 'pay/add',
                    // edit_url: 'pay/edit',
                    // del_url: 'pay/del',
                    // multi_url: 'pay/multi',
                    // import_url: 'pay/import',
                    table: 'pay',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'plugin',
                sortName: 'weigh',
                commonSearch: false,
                showExport: false,
                columns: [
                    [
                        // {checkbox: false},
                        // {field: 'id', title: __('ID')},
                        {
                            field: 'name',
                            title: __('Name'),
                            operate: false,
                            formatter:function(value,row,index){
                                if(value == '官方微信'){
                                    return `<a target="_blank" href="https://pay.weixin.qq.com/" style="color: #000;">官方微信</a>`;
                                }else if(value == '官方支付宝'){
                                    return `<a target="_blank" href="https://b.alipay.com/" style="color: #000;">官方支付宝</a>`;
                                }else{
                                    return value;
                                }
                            }

                        },
                        {
                            field: 'status',
                            title: __('状态'),
                            formatter:function(value,row,index){
                                if(value == 'enable'){
                                    return `<a href="javascript:;" class="btn btn-xs btn-success close-status" data-id="${row.plugin}" data-toggle="tooltip" data-original-title="点击关闭">已启用</a>`;
                                }else if(value == 'disable'){
                                    return `<a href="javascript:;" class="btn btn-xs btn-default open-status" data-id="${row.plugin}" data-toggle="tooltip" data-original-title="点击启用">已关闭</a>`;
                                }else{
                                    return `<a href="javascript:;" class="btn btn-xs btn-danger">状态有误</a>`;
                                }
                            }
                        },
                        // {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'dialog',
                                    title: function(row){
                                        return row.name + ' - 配置';
                                    },
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-cog',
                                    url: 'pay/edit/plugin_name/{plugin}',
                                    text:'配置',
                                    hidden:false

                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            /*Table.button.dragsort = {
                name: 'dragsort',
                text: __('排序'),
                icon: 'fa fa-arrows',
                title: __('排序'),
                classname: 'btn btn-xs btn-primary btn-dragsort'
            }*/

            /*Table.button.edit = {
                // name: 'edit',
                text: '配置',
                icon: 'fa fa-pencil',
                title: function(row){
                    return 'sdds';
                },
                classname: 'btn btn-xs btn-success btn-editone'
            }*/


            // 为表格绑定事件
            Table.api.bindevent(table);



            //启用支付
            $(document).on("click", ".open-status", function () {
                var id = $(this).attr('data-id');
                layer.load();
                $.post("pay/openStatus", {id:id, 'status': 1}, function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                        table.bootstrapTable('refresh', {});
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            });
//            关闭支付
            $(document).on("click", ".close-status", function () {
                var id = $(this).attr('data-id');
                layer.load();
                $.post("pay/closeStatus", {id:id, 'status': 0}, function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                        table.bootstrapTable('refresh', {});
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            });

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
            }
        }
    };
    return Controller;
});
