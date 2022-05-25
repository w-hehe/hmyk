define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'complain/index' + location.search,
                    add_url: 'complain/add',
                    edit_url: 'complain/edit',
                    del_url: 'complain/del',
                    multi_url: 'complain/multi',
                    import_url: 'complain/import',
                    table: 'complain',
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
                        // {field: 'complain_id', title: __('投诉ID'), operate: 'LIKE'},
                        {field: 'out_trade_no', title: __('订单号'), operate: 'LIKE'},
                        {
                            field: 'name',
                            title: __('Name'),
                            formatter: function(value){
                                if(value.length > 10){
                                    return value.toString().substr(0, 10) + '...';
                                }else{
                                    return value;
                                }
                            }, operate: 'LIKE'
                        },
                        {field: 'qq', title: __('Qq')},
                        {
                            field: 'details',
                            title: __('Details'),
                            formatter: function(value){
                                if(value.length > 30){
                                    return value.toString().substr(0, 30) + '...';
                                }else{
                                    return value;
                                }
                            }
                        },
                        {field: 'handle_result', title: __('Handle_result'), operate: 'LIKE'},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: function(value){
                                if(value == 0) return '待处理';
                                if(value == 1) return '处理中';
                                if(value == 0) return '已完成';
                            }
                        },
                        {field: 'create_time', title: __('投诉时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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