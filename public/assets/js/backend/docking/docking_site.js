define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'docking/docking_site/index' + location.search,
                    add_url: 'docking/docking_site/add',
                    edit_url: 'docking/docking_site/edit',
                    del_url: 'docking/docking_site/del',
                    multi_url: 'docking/docking_site/multi',
                    import_url: 'docking/docking_site/import',
                    table: 'docking_site',
                }
            });

            var table = $("#table");

            table.on('post-body.bs.table',function(){
                $(".btn-goods-list").data("area",["1000px","720px"]);
            })

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {
                            field: 'id',
                            title: __('ID'),
                            formatter: function(value, row, index){
                                return ++index;
                            }
                        },
                        {field: 'remark', title: __('备注'), operate: 'LIKE'},

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [

                            ],
                            formatter: Table.api.formatter.operate
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
        },

        sync: function () {
            Controller.api.bindevent();
        },
        add: function () {
            var type = $('#default-dock').val();
            $('.dock-config-' +  type).show();
            $('#c-dock_type').change(function(){
                var type = $(this).val();
                $('.dock-config').hide();
                $('.dock-config-' + type).show();
            })

            Controller.api.bindevent();
        },
        edit: function () {
            var type = $('#default-dock').val();
            $('.dock-config-' +  type).show();
            $('#c-dock_type').change(function(){
                var type = $(this).val();
                $('.dock-config').hide();
                $('.dock-config-' + type).show();
            })
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
