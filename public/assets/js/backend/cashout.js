define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cashout/index' + location.search,
                    add_url: 'cashout/add',
                    // edit_url: 'cashout/edit',
                    // del_url: 'cashout/del',
                    multi_url: 'cashout/multi',
                    import_url: 'cashout/import',
                    table: 'money_bill',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'user.nickname', title: __('用户')},
                        {field: 'money', title: __('提现金额'), operate:'BETWEEN'},
                        {field: 'actual', title: __('应付金额'), operate:'BETWEEN'},
                        {field: 'charged', title: __('手续费%')},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: function(value, row, index){
                                if(value == 0){
                                    return `<span class="label label-default handle" data-id="${row.id}">未处理</span>
                                `;
                                }else if(value == 1){
                                    return `<span class="label label-success de-handle" data-id="${row.id}">已处理</span>
                                `;
                                }

                            }
                        },
                        {field: 'createtime', title: __('申请时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'handletime', title: __('处理时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $(document).on("click", ".de-handle", function () {
                var id = $(this).attr('data-id');
                layer.load();
                $.post("cashout/status", {id:id, 'status': 0}, function(e){
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


            $(document).on("click", ".handle", function () {
                var id = $(this).attr('data-id');
                layer.load();
                $.post("cashout/status", {id:id, 'status': 1}, function(e){
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