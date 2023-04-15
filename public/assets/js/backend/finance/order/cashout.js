define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/order/cashout/index' + location.search,
                    add_url: 'finance/order/cashout/add',
                    edit_url: 'finance/order/cashout/edit',
                    del_url: 'finance/order/cashout/del',
                    multi_url: 'finance/order/cashout/multi',
                    import_url: 'finance/order/cashout/import',
                    table: 'cashout',
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
                        {field: 'out_trade_no', title: __('Out_trade_no'), operate: 'LIKE'},
                        {field: 'user.username', title: __('User_id')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'account', title: __('Account'), operate: 'LIKE'},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: function(value){
                                if(value == 0){
                                    return '未处理';
                                }
                                if(value == 1){
                                    return '已完成';
                                }
                            }
                        },
                        {field: 'complete_time', title: __('Complete_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'ajax',
                                    title: __('发送Ajax'),
                                    classname: 'btn btn-xs btn-warning btn-magic btn-ajax',
                                    // icon: 'fa fa-magic',
                                    text: '确认打款',
                                    confirm: '确认更改状态为已处理？',
                                    url: 'finance/order/cashout/chuli',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    hidden: function(row){
                                        if(row.status == 1) return true;
                                    }
                                },
                                {
                                    name: 'ajax',
                                    title: __('发送Ajax'),
                                    classname: 'btn btn-xs btn-primary btn-magic btn-ajax',
                                    // icon: 'fa fa-magic',
                                    text: '撤销处理',
                                    confirm: '确认撤销处理状态？',
                                    url: 'finance/order/cashout/unchuli',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    hidden: function(row){
                                        if(row.status == 0) return true;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
