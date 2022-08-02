define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/notice/index' + location.search,
                    add_url: 'general/notice/add',
                    edit_url: 'general/notice/edit',
                    del_url: 'general/notice/del',
                    multi_url: 'general/notice/multi',
                    import_url: 'general/notice/import',
                    table: 'test',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'className',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'info.name', title: '名称'},
                        {field: 'info.version', title:'版本'},
                        {
                            field: 'info.description', title:'描述',
                            formatter:function(value,row,index){
                                if(value == ''){
                                    return `未添加任何描述`;
                                }else{
                                    return value;
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
                text: '配置',
                icon: 'fa fa-pencil',
                title: '配置',
                classname: 'btn btn-xs btn-success btn-editone'
            }

            // 给表单绑定事件
            Form.api.bindevent($("#update-form"), function () {
                $("input[name='row[password]']").val('');
                var url = Backend.api.cdnurl($("#c-avatar").val());
                top.window.$(".user-panel .image img,.user-menu > a > img,.user-header > img").prop("src", url);
                return true;
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
