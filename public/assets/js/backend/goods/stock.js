define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        alone_0: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/stock/alone_0/ids/' + $('#goods-id').val() + location.search,
                    add_url: 'goods/stock/add/ids/' + $('#goods-id').val(),
                    edit_url: 'goods/stock/edit',
                    del_url: 'goods/stock/del',
                    multi_url: 'goods/stock/multi',
                    import_url: 'goods/stock/import',
                    table: 'stock',
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
                        {field: 'content', title: __('库存内容')},
                        {field: 'create_time', title: __('添加时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'sale_time', title: __('出售时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        alone_1: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/stock/alone_1/ids/' + $('#goods-id').val() + location.search,
                    add_url: 'goods/stock/add/ids/' + $('#goods-id').val(),
                    edit_url: 'goods/stock/edit',
                    del_url: 'goods/stock/del',
                    multi_url: 'goods/stock/multi',
                    import_url: 'goods/stock/import',
                    table: 'stock',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                showColumns: false,
                searchFormVisible: true,
                // 必须添加这个,customformtpl与html的ID一致
                searchFormTemplate: 'customformtpl',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'sku.sku', title: __('规格')},
                        {field: 'content', title: __('库存内容')},
                        {field: 'create_time', title: __('添加时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'sale_time', title: __('出售时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
