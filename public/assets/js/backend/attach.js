define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "请输入名称查询";};
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'attach/index' + location.search,
                    add_url: 'attach/add',
                    edit_url: 'attach/edit',
                    del_url: 'attach/del',
                    multi_url: 'attach/multi',
                    import_url: 'attach/import',
                    table: 'attach',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
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
