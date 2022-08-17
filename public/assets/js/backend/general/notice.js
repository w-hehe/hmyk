define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            // Table.api.init();

            Table.api.init({
                extend: {
                    edit_url: 'goods/edit'
                }
            });

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");



            // 给表单绑定事件
            Form.api.bindevent($("#update-form"), function () {});


        },
        table: {
            oneTable: function () {
                var table1 = $("#table1");
                // 初始化表格
                table1.bootstrapTable({
                    url: 'general/notice/index',
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    pk: 'className',
                    commonSearch: false,
                    visible: false,
                    showToggle: false,
                    showColumns: false,
                    search:false,
                    showExport: false,
                    columns: [
                        [
                            // {checkbox: true},
                            {field: 'info.name', title: '名称1'},
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
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table1,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'edit',
                                        title: __('配置'),
                                        classname: 'btn btn-xs btn-info btn-dialog',
                                        icon: 'fa fa-pencil',
                                        url: 'general/notice/edit',
                                        text:'配置'
                                    }
                                ],
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            twoTable: function () {
                var table2 = $("#table2");
                // 初始化表格
                table2.bootstrapTable({
                    url: 'general/notice/template',
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    pk: 'file',
                    commonSearch: false,
                    visible: false,
                    showToggle: false,
                    showColumns: false,
                    search:false,
                    showExport: false,
                    columns: [
                        [
                            // {checkbox: true},
                            {field: 'name', title: '模板'},
                            {field: 'file', title:'标识'},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table2,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'edit_template',
                                        title: __('编辑'),
                                        classname: 'btn btn-xs btn-info btn-dialog',
                                        icon: 'fa fa-pencil',
                                        url: 'general/notice/edit_template',
                                        text:'编辑'
                                    }
                                ],
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格2绑定事件
                Table.api.bindevent(table2);
            }
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit_template: function () {
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
