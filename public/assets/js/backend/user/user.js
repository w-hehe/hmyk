define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'username', title: __('账号'), operate: 'LIKE'},
                        {field: 'email', title: __('Email'), operate: 'LIKE'},
                        // {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {
                            field: 'agency',
                            title: __('代理等级'),
                            formatter: function(value){
                                if(value == null){
                                    return '<span class="label"  title="" style="background: #8b8b8b;">普通用户</span>';
                                } else {
                                    return '<span class="label"  title="" style="background: #daac0c;">' + value.name + '</span>';
                                }
                            }
                        },
                        {field: 'consume', title: __('总消费'), operate: 'BETWEEN', sortable: true},
                        {field: 'money', title: __('money'), operate: 'BETWEEN', sortable: true},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('封禁')}},
                        {field: 'createtime', sortable: true, width: 180, title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'money',
                                    title: __('余额管理'),
                                    classname: 'btn btn-xs btn-info btn-dialog btn-money',
                                    icon: 'fa fa-yen',
                                    url: 'user/user/money',
                                    text:'余额管理'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            table.on('post-body.bs.table',function(){
                $(".btn-money").data("area",["800px","250px"]);
            })

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
        money: function () {
            Controller.api.bindevent();
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