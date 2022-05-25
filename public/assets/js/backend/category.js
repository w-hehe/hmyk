define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "请输入分类名称查询";};
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'category/index',
                    add_url: 'category/add',
                    edit_url: 'category/edit',
                    del_url: 'category/del',
                    multi_url: 'category/multi',
                    dragsort_url: 'ajax/weigh',
                    table: 'category',
                }
            });

            var table = $("#table");
            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                commonSearch: false,
                clickToSelect: false,
                dblClickToEdit: false,
                // search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'name', title: __('Name'), align: 'left'},
                        {field: 'status', title: __('Status'), operate: false, formatter: Table.api.formatter.status},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'add_stock',
                                    title: __('查看分类'),
                                    classname: 'btn btn-xs btn-info btn-look',
                                    icon: 'fa fa-eye',
                                    text:'查看分类',
                                    extend: 'target="_blank"',
                                    url: function(row){
                                        return '/category/' + row.id + '.html';
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            };
            // 初始化表格
            table.bootstrapTable(tableOptions);



            Table.button.dragsort = {
                name: 'dragsort',
                text: __('排序'),
                icon: 'fa fa-arrows',
                title: __('排序'),
                classname: 'btn btn-xs btn-primary btn-dragsort'
            }

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
                // var options = table.bootstrapTable(tableOptions);
                var typeStr = $(this).attr("href").replace('#', '');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // params.filter = JSON.stringify({type: typeStr});
                    params.type = typeStr;

                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });

            //必须默认触发shown.bs.tab事件
            // $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

        },
        add: function () {
            Controller.api.bindevent();
            setTimeout(function () {
                $("#c-type").trigger("change");
            }, 100);
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $(document).on("change", "#c-type", function () {
                    $("#c-pid option[data-type='all']").prop("selected", true);
                    $("#c-pid option").removeClass("hide");
                    $("#c-pid option[data-type!='" + $(this).val() + "'][data-type!='all']").addClass("hide");
                    $("#c-pid").data("selectpicker") && $("#c-pid").selectpicker("refresh");
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
