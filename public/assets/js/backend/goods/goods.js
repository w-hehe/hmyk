define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/goods/index' + location.search,
                    add_url: 'goods/goods/add',
                    edit_url: 'goods/goods/edit',
                    del_url: 'goods/goods/del',
                    multi_url: 'goods/goods/multi',
                    import_url: 'goods/goods/import',
                    table: 'goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id'), width: 80},
                        {field: 'cover', title: __('封面'), width: 70, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'name', title: __('Name'), operate: 'LIKE', align: 'left'},
                        {
                            field: 'is_sku',
                            width: 80,
                            title: __('规格'),
                            formatter: function(value){
                                if(value == 0){
                                    return '<span class="label"  title="" style="background: #00bcd4;">单规格</span>';
                                } else {
                                    return '<span class="label"  title="" style="background: #daac0c;">多规格</span>';
                                }
                            }
                        },
                        {field: 'price', title: __('价格'), width: 80},
                        {field: 'sales', title: __('Sales'), width: 80},
                        {field: 'stock', title: __('Stock'), width: 80},
                        {
                            field: 'type',
                            title: __('商品类型'),
                            width: 80,
                            formatter: function(value){
                                if(value == 'alone'){
                                    return '<span class="label"  title="" style="background: #0f9bce;">独立卡密</span>';
                                }else if(value == 'fixed'){
                                    return '<span class="label"  title="" style="background: #f35656;">固定卡密</span>';
                                }else if(value == 'invented'){
                                    return '<span class="label"  title="" style="background: #00a383;">虚拟商品</span>';
                                }else{
                                    return '<span class="label"  title="" style="background: #f39c12;">未知类型</span>';
                                }
                            }
                        },
                        {
                            field: 'shelf',
                            title: __('Shelf'),
                            align: 'center',
                            table: table,
                            formatter: Table.api.formatter.toggle, width: 80
                        },
                        {field: 'weigh', title: __('排序'), operate: false, width: 80},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'click',
                                    title: __('同步库存'),
                                    text: __('同步库存'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    icon: 'fa fa-exchange',
                                    // dropdown: '更多',//如果包含dropdown，将会以下拉列表的形式展示
                                    click: function (data, row) {
                                        layer.load();
                                        $.get("goods/update_stock", row, function(e){
                                            layer.closeAll('loading');
                                            if(e.code == 200){
                                                Toastr.success(e.msg)
                                                table.bootstrapTable('refresh', {})
                                            }else{
                                                Toastr.error(e.msg)
                                            }
                                        }, "json").error(function(){
                                            layer.closeAll('loading');
                                            Toastr.error("请求失败")
                                        })
                                    },
                                    hidden:function(row){
                                        if(row.goods_type != 'dock') return true;
                                    }
                                },
                                /*{
                                    name: 'add_stock',
                                    title: __('添加库存'), //标题
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'goods.stock/add',
                                    text:'添加库存', //按钮
                                    hidden:function(row){
                                        if(row.goods_type == 'dock') return true;
                                    }

                                },*/
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr fa-fw',
                                    url: 'goods.stock/alone_0',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.type == 'alone' && row.is_sku == 0) {
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr fa-fw',
                                    url: 'goods.stock/alone_1',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.type == 'alone' && row.is_sku == 1) {
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr fa-fw',
                                    url: 'goods.stock/add',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.type == 'fixed' && row.is_sku == 0) {
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr fa-fw',
                                    url: 'goods.stock/add',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.type == 'fixed' && row.is_sku == 1) {
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr fa-fw',
                                    url: 'goods.stock/add',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.type == 'invented' && row.is_sku == 0) {
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr fa-fw',
                                    url: 'goods.stock/add',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.type == 'invented' && row.is_sku == 1) {
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },

                                { //多规格
                                    name: 'edit',
                                    title: __('编辑商品'),
                                    classname: 'btn btn-xs btn-success btn-addtabs',
                                    icon: 'fa fa-pencil',
                                    url: 'goods.goods/edit',
                                    text:'编辑',

                                }
                            ],
                            formatter: Table.api.formatter.operate,
                            width: 220
                        }
                    ]
                ]
            });

            Table.button.edit = {
                classname: 'hidden'
                // name: 'edit',
                // text: __('编辑'),
                // icon: 'fa fa-pencil',
                // title: __('编辑'),
                // classname: 'btn btn-xs btn-success btn-editone'
            }

            Table.button.dragsort = {
                classname: 'hidden'
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
            // Controller.api.bindevent();
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Toastr.success('添加成功');
                var obj = top.window.$("ul.nav-addtabs li.active");
                obj.find(".fa-remove").trigger("click");
            });
            $('input[name="row[is_sku]"]').change(function(){
                if($(this).val() == 0){
                    $('.sku-false').show();
                    $('.sku-true').hide();
                }else{
                    $('.sku-false').hide();
                    $('.sku-true').show();
                }
            });
        },

        add_stock: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
            $('input[name="row[is_sku]"]').change(function(){
                if($(this).val() == 0){
                    $('.sku-false').show();
                    $('.sku-true').hide();
                }else{
                    $('.sku-false').hide();
                    $('.sku-true').show();
                }
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
