define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'docking/increase/index' + location.search,
                    add_url: 'docking/increase/add',
                    edit_url: 'docking/increase/edit',
                    del_url: 'docking/increase/del',
                    multi_url: 'docking/increase/multi',
                    import_url: 'docking/increase/import',
                    table: 'docking_increase',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {
                            field: 'type',
                            title: __('加价'),
                            operate: 'LIKE',
                            formatter: function(value, row, index){
                                if(value == 'follow'){
                                    return '<span class="label"  title="" style="background: #2196f3;">跟随对接站</span>';
                                }else if(value == 'fixed'){
                                    return '<span class="label"  title="" style="background: #00bcd4;"> 固定金额 ' + row.value + '元</span>';
                                }else if(value == 'percent'){
                                    return '<span class="label"  title="" style="background: #ff5722;"> 百分比 ' + row.value + '%</span>';
                                }
                            }
                        },
                        {
                            field: 'effect',
                            title: __('模板生效场景'),
                            operate: 'LIKE',
                            formatter: function(value){
                                if(value == 1){
                                    return '<span class="label"  title="" style="background: #e91e63;">对接站价格高于本站价格</span>';
                                }else if(value == 2){
                                    return '<span class="label"  title="" style="background: #74c515;">对接站价格出现变动时</span>';
                                }
                            }
                        },
                        // {field: 'value', title: __('Value'), operate: 'LIKE'},
                        {
                            field: 'expire',
                            title: __('价格检测过期时间'),
                            operate: 'LIKE',
                            formatter: function(value){
                                return value + '分钟';
                            }
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
        add: function () {
            // $("#c-type").change(function(){
            //     var type = $(this).val();
            //     console.log(type)
            // })
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