define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'money_bill/index' + location.search,
                    add_url: 'money_bill/add',
                    edit_url: 'money_bill/edit',
                    del_url: 'money_bill/del',
                    multi_url: 'money_bill/multi',
                    import_url: 'money_bill/import',
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
                        {field: 'id', title: __('Id')},
                        {field: 'uid', title: __('Uid')},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'value', title: __('Value'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'actual', title: __('Actual'), operate:'BETWEEN'},
                        {field: 'charged', title: __('Charged')},
                        {field: 'status', title: __('Status')},
                        {field: 'handletime', title: __('Handletime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_type', title: __('Pay_type'), operate: 'LIKE'},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
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