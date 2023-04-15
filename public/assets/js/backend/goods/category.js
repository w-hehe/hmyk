define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/category/index' + location.search,
                    add_url: 'goods/category/add',
                    edit_url: 'goods/category/edit',
                    del_url: 'goods/category/del',
                    multi_url: 'goods/category/multi',
                    import_url: 'goods/category/import',
                    table: 'goods_category',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                commonSearch: false,
                search: false,
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},

                        {field: 'name', title: __('Name'), width: 250, align: 'left', formatter:function (value, row, index) {
                                return value.toString().replace(/(&|&amp;)nbsp;/g, '&nbsp;&nbsp;&nbsp;');
                            }
                        },
                        // {field: 'flag', title: __('Flag'), searchList: {"hot":__('Hot'),"index":__('Index'),"recommend":__('Recommend')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label},

                        {field: 'keywords', title: __('Keywords'), operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        // {field: 'image', width: 90, title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'createtime', width: 180, title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime',  width: 180, title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'weigh', width: 80, title: __('Weigh'), operate: false},
                        // {field: 'status', width: 100, title: __('Status'), searchList: {"30":__('Status 30')}, formatter: Table.api.formatter.status},
                        {field: 'operate', width: 120, title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
