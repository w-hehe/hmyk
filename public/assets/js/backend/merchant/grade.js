define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'merchant/grade/index' + location.search,
                    add_url: 'merchant/grade/add',
                    edit_url: 'merchant/grade/edit',
                    del_url: 'merchant/grade/del',
                    multi_url: 'merchant/grade/multi',
                    import_url: 'merchant/grade/import',
                    table: 'merchant_grade',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {
                            field: 'rebate',
                            title: __('返佣'),
                            formatter: function(value){
                                return value + '%';
                            }
                        },
                        {
                            field: 'domain',
                            title: __('Domain'),
                            formatter: function(value){
                                if(value == 0){
                                    return '<span class="label"  title="" style="background: #8b8b8b;">无权限</span>';
                                }
                                if(value == 1){
                                    return '<span class="label"  title="" style="background: #daac0c;">有权限</span>';
                                }
                            }
                        },
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
