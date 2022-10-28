define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'navi/index' + location.search,
                    add_url: 'navi/add',
                    edit_url: 'navi/edit',
                    del_url: 'navi/del',
                    multi_url: 'navi/multi',
                    import_url: 'navi/import',
                    table: 'navi',
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
                        // {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        /*{
                            field: 'category',
                            title: '类别',
                            operate: 'LIKE',
                            formatter: function(value){
                                if(value == 'pc'){
                                    return '<span class="label"  title="" style="background: #00bcd4;">电脑端</span>';
                                }else {
                                    return '<span class="label"  title="" style="background: #f39c12;">手机端</span>';
                                }
                            }
                        },*/
                        {field: 'url', title: '跳转链接', operate: 'LIKE', formatter: Table.api.formatter.url},
                        {
                            field: 'target',
                            title: '新窗口打开',
                            formatter: function(value){
                                if(value == 1){
                                    return '<span class="label"  title="" style="background: #04bd4f;">是</span>';
                                }else {
                                    return '<span class="label"  title="" style="background: #e33434;">否</span>';
                                }
                            }
                        },
                        {
                            field: 'type',
                            title: __('Type'),
                            operate: 'LIKE',
                            formatter: function(value){
                                if(value == 'system'){
                                    return '<span class="label"  title="" style="background: #1762ec;">系统</span>';
                                }else {
                                    return '<span class="label"  title="" style="background: #fd5d2d;">自定义</span>';
                                }
                            }
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            // formatter: Table.api.formatter.operate,
                            formatter: function (value, row, index){
                                if(row.type == 'system'){
                                    return `<a href="navi/edit/ids/${row.id}" class="btn btn-xs btn-success btn-editone" title="编辑" data-table-id="table" data-field-index="${index}" data-row-index="0" data-button-index="1"><i class="fa fa-pencil"></i> 编辑</a>`;
                                }
                                return Table.api.formatter.operate.call(this, value, row, index);

                            }
                        }
                    ]
                ]
            });

            Table.button.edit = {
                name: 'edit',
                text: __('编辑'),
                icon: 'fa fa-pencil',
                title: __('编辑'),
                classname: 'btn btn-xs btn-success btn-editone'
            }

            Table.button.del = {
                name: 'del',
                text: __('删除'),
                icon: 'fa fa-trash',
                title: __('删除'),
                classname: 'btn btn-xs btn-danger btn-delone'
            }

            // 为表格绑定事件
            Table.api.bindevent(table);

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('.search > input').val('')
                // var options = table.bootstrapTable(tableOptions);
                var typeStr = $(this).attr("href").replace('#', '');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // params.filter = JSON.stringify({type: typeStr});
                    params.category = typeStr;
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

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
