define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'recharge/index' + location.search,
                    // add_url: 'recharge/add',
                    // edit_url: 'recharge/edit',
                    del_url: 'recharge/del',
                    // multi_url: 'recharge/multi',
                    // import_url: 'recharge/import',
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
                        {field: 'order_no', title: __('订单号'), operate: 'LIKE'},
                        {field: 'user.nickname', title: __('用户')},
                        {field: 'money', title: __('充值金额'), operate:'BETWEEN'},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: function(value){
                                if(value == 0){
                                    return `<span class="label label-default">未支付</span>
                                `;
                                }else if(value == 1){
                                    return `<span class="label label-success">已支付</span>
                                `;
                                }

                            }
                        },
                        {field: 'createtime', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'handletime', title: __('支付时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_type', title: __('付款方式'), operate: 'LIKE'},
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