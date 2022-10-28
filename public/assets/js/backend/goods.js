define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/index' + location.search,
                    add_url: 'goods/add',
                    stock_add: 'goods/stock_add',
                    // stock: 'goods/stock',
                    edit_url: 'goods/edit',
                    del_url: 'goods/del',
                    multi_url: 'goods/multi',
                    import_url: 'goods/import',
                    dock_select_goods_url: 'goods/dock_select_goods',
                    table: 'goods',
                }
            });


            var table = $("#table");



            $(".btn-add").data("area",["1000px","670px"]);
            $(".btn-edit").data("area",["1000px","670px"]);

            table.on('post-body.bs.table',function(){
                $(".btn-editone").data("area",["1000px","670px"]);
            })
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "请输入商品或分类名称查询";};
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                showExport: false,
                escape: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID'), sortable: true},
                        {field: 'category.name', title: __('分类')},
                        {
                            field: 'name',
                            title: __('Name'),
                            formatter: function(value){
                                if(value.length > 30){
                                    return value.toString().substr(0, 30) + '...';
                                }else{
                                    return value;
                                }
                            }
                        },

                        {field: 'images', title: '封面图', events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'sales', title: __('销量'), sortable: true},
                        {
                            field: 'goods_type',
                            title: __('商品类型'),
                            formatter: function(value){
                                if(value == 'alone'){
                                    return '<span class="label"  title="" style="background: #00bcd4;">独立卡密</span>';
                                }else if(value == 'fixed'){
                                    return '<span class="label"  title="" style="background: #00bcd4;">固定卡密</span>';
                                }else if(value == 'manual'){
                                    return '<span class="label"  title="" style="background: #ff5722;">人工代充</span>';
                                }else if(value == 'dock'){
                                    return '<span class="label"  title="" style="background: #00acc1;">对接货源</span>';
                                } else {
                                    return '<span class="label"  title="" style="background: #f39c12;">未知类型</span>';
                                }
                            }
                        },
                        {field: 'price', title: __('价格'), operate:'BETWEEN', sortable: false},
                        // {field: 'original_price', title: __('Original_price'), operate:'BETWEEN'},
                        {field: 'stock', title: __('库存'), sortable: false},
                        {
                            field: 'dock_id',
                            title: __('商品来源'),
                            formatter: function(value, row, index){
                                if(row.dock_id == 0){
                                    return '<span class="label"  title="" style="background: #18bc9c;">自营</span>';
                                }else{
                                    return '<span class="label"  title="" style="background: #3498db;">对接</span>';
                                }
                            }
                        },
                        {
                            field: 'shelf',
                            title: __('状态'),
                            formatter:function(value,row,index){
                                if(value == 0){
                                    return `<a href="javascript:;" class="btn btn-xs btn-success down-goods" data-id="${row.id}" data-toggle="tooltip" data-original-title="点击下架">上架中</a>`;
                                }else if(value == 1){
                                    return `<a href="javascript:;" class="btn btn-xs btn-default up-goods" data-id="${row.id}" data-toggle="tooltip" data-original-title="点击上架">已下架</a>`;
                                }else{
                                    return `<a href="javascript:;" class="btn btn-xs btn-danger">状态有误</a>`;
                                }
                            }
                        },
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
                                {
                                    name: 'add_stock',
                                    title: __('添加库存'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'goods/stock_add',
                                    text:'添加库存',
                                    hidden:function(row){
                                        if(row.goods_type == 'dock') return true;
                                    }

                                },
                                { //单规格
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-juse btn-dialog',
                                    icon: 'fa fa-cogs',
                                    url: 'goods/stock',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.goods_type == 'dock' || row.goods_type == 'manual' || (row.sku != '' && row.sku != null)){
                                            return true;
                                        }
                                    }
                                },
                                { //多规格
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-juse btn-dialog',
                                    icon: 'fa fa-cogs',
                                    url: 'goods/stock_sku',
                                    text:'管理库存',
                                    hidden:function(row){
                                        if(row.goods_type == 'dock' || row.goods_type == 'manual' || row.sku == '' || row.sku == null){
                                            return true;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate,
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
                var typeStr = $(this).attr("href").replace('#', '');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    params.shelf = typeStr;
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });


            //上架商品
            $(document).on("click", ".up-goods", function () {
                var id = $(this).attr('data-id');
                layer.load();
                $.post("goods/upGoods", {id:id, 'shelf': 0}, function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                    table.bootstrapTable('refresh', {});
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            });
//            下架商品
            $(document).on("click", ".down-goods", function () {
                var id = $(this).attr('data-id');
                layer.load();
                $.post("goods/downGoods", {id:id, 'shelf': 1}, function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                    table.bootstrapTable('refresh', {});
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            });


        },

        table: {
            ws: function () {
                Table.api.init({
                    extend: {
                        del_url: 'goods/stock_del/goods_id/' + $('#ids').val(),
                        repeat_url: 'goods/repeat/goods_id/' + $('#ids').val(),
                    }
                });
                // 表格1
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'goods/stock' + location.search + "&status=ws&ids=" + $('#ids').val(),
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    visible: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    search:false,
                    commonSearch: false,
                    columns: [
                        [
                            {checkbox: true},
                            {
                                field: 'cdk',
                                title: __('卡密'),
                                align: 'center',
                                formatter:function(value,row,index){
                                    if(row.type == 3){
                                        return `<a href="javascript:"><img class="img-sm img-center" src="${value}"></a>`;
                                    }else{
                                        return value;
                                    }
                                }
                            },
                            {
                                field: 'createtime',
                                title: __('添加时间'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'operate', title: __('Operate'), table: table1, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);

                // 按钮
                $(document).on("click", ".btn-repeat", function () {
                    Layer.confirm('您确定要删除该商品所有重复库存吗？ 注意该操作无法撤销！', {
                        title: __('温馨提示'),
                        btn: [__('确定'), __('取消')]
                    }, function (index) {
                        layer.close(index);
                        layer.load();
                        $.post("goods/repeat", {id: $('#ids').val()}, function (e) {
                            layer.closeAll();
                            if (e.code == 400) {
                                layer.closeAll('loading');
                                Toastr.error(e.msg);
                            } else if (e.code == 200) {
                                Layer.closeAll();
                                Toastr.success(e.msg);
                                table1.bootstrapTable('refresh', {});
                            }
                        }, "json").error(function () {
                            Layer.closeAll();
                            Toastr.error('请求失败');
                        });

                    }, function (index) {
                        layer.close(index);
                    });
                });



                // 按钮
                $(document).on("click", ".btn-ept", function () {
                    Layer.confirm('您确定要清空该商品所有的库存吗？ 注意该操作无法撤销！', {
                        title: __('温馨提示'),
                        btn: [__('确定'), __('取消')]
                    }, function (index) {
                        layer.close(index);
                        layer.load();
                        $.post("goods/ept", {id: $('#ids').val()}, function (e) {
                            layer.closeAll();
                            if (e.code == 400) {
                                layer.closeAll('loading');
                                Toastr.error(e.msg);
                            } else if (e.code == 200) {
                                Layer.closeAll();
                                Toastr.success(e.msg);
                                table1.bootstrapTable('refresh', {});
                            }
                        }, "json").error(function () {
                            Layer.closeAll();
                            Toastr.error('请求失败');
                        });

                    }, function (index) {
                        layer.close(index);
                    });
                });
            },
            ys: function () {
                // 表格2
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'goods/stock' + location.search + "&status=ys&ids=" + $('#ids').val(),
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: '',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    visible: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    search:false,
                    commonSearch: false,
                    columns: [
                        [
                            {checkbox: true},
                            {
                                field: 'content',
                                title: __('卡密'),
                                align: 'center',
                                formatter:function(value,row,index){
                                    if(row.type == 3){
                                        return `<a href="javascript:"><img class="img-sm img-center" src="${value}"></a>`;
                                    }else{
                                        return value;
                                    }
                                }
                            },
                            {
                                field: 'create_time',
                                title: __('出售时间'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },

                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            },

            ws_sku: function () {
                Table.api.init({
                    extend: {
                        del_url: 'goods/stock_del', //删除选中
                        // repeat_url: 'goods/repeat/price_id/' + $('#price_id').val(), //删除重复
                        // ept_url: 'goods/ept/price_id/' + $('#price_id').val(), //清空库存
                    }
                });
                // 表格1
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'goods/stock_sku_manage' + location.search + "&status=ws&price_id=" + $('#price_id').val(),
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    visible: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    search:false,
                    commonSearch: false,
                    columns: [
                        [
                            {checkbox: true},
                            {
                                field: 'cdk',
                                title: __('卡密'),
                                align: 'center',
                                formatter:function(value,row,index){
                                    if(row.type == 3){
                                        return `<a href="javascript:"><img class="img-sm img-center" src="${value}"></a>`;
                                    }else{
                                        return value;
                                    }
                                }
                            },
                            {
                                field: 'createtime',
                                title: __('添加时间'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'operate', title: __('Operate'), table: table1, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);

                // 按钮
                $(document).on("click", ".btn-repeat", function () {
                    Layer.confirm('您确定要删除该商品所有重复库存吗？ 注意该操作无法撤销！', {
                        title: __('温馨提示'),
                        btn: [__('确定'), __('取消')]
                    }, function (index) {
                        layer.close(index);
                        layer.load();
                        $.post("goods/repeat_sku", {price_id: $('#price_id').val()}, function (e) {
                            layer.closeAll();
                            if (e.code == 400) {
                                layer.closeAll('loading');
                                Toastr.error(e.msg);
                            } else if (e.code == 200) {
                                Layer.closeAll();
                                Toastr.success(e.msg);
                                table1.bootstrapTable('refresh', {});
                            }
                        }, "json").error(function () {
                            Layer.closeAll();
                            Toastr.error('请求失败');
                        });

                    }, function (index) {
                        layer.close(index);
                    });
                });



                // 按钮
                $(document).on("click", ".btn-ept", function () {
                    Layer.confirm('您确定要清空该商品所有的库存吗？ 注意该操作无法撤销！', {
                        title: __('温馨提示'),
                        btn: [__('确定'), __('取消')]
                    }, function (index) {
                        layer.close(index);
                        layer.load();
                        $.post("goods/ept_sku", {price_id: $('#price_id').val()}, function (e) {
                            layer.closeAll();
                            if (e.code == 400) {
                                layer.closeAll('loading');
                                Toastr.error(e.msg);
                            } else if (e.code == 200) {
                                Layer.closeAll();
                                Toastr.success(e.msg);
                                table1.bootstrapTable('refresh', {});
                            }
                        }, "json").error(function () {
                            Layer.closeAll();
                            Toastr.error('请求失败');
                        });

                    }, function (index) {
                        layer.close(index);
                    });
                });
            },
            ys_sku: function () {
                // 表格2
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'goods/stock_sku_manage' + location.search + "&status=ys&price_id=" + $('#price_id').val(),
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: '',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    visible: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    search:false,
                    commonSearch: false,
                    columns: [
                        [
                            {checkbox: true},
                            {
                                field: 'content',
                                title: __('卡密'),
                                align: 'center',
                                formatter:function(value,row,index){
                                    if(row.type == 3){
                                        return `<a href="javascript:"><img class="img-sm img-center" src="${value}"></a>`;
                                    }else{
                                        return value;
                                    }
                                }
                            },
                            {
                                field: 'create_time',
                                title: __('出售时间'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },

                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            }
        },

        stock_sku: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/stock_sku',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url + '?goods_id=' + $('#goods_id').val(),
                pk: 'price_id',
                // sortName: 'price_id',
                pagination: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'sku', title: '规格'},
                        {field: 'stock', title: '库存'},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'admin_stock',
                                    title: __('管理库存'),
                                    classname: 'btn btn-xs btn-juse btn-dialog',
                                    icon: 'fa fa-cogs',
                                    url: 'goods/stock_sku_manage',
                                    text:'管理库存'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        stock_sku_manage: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    del_url: 'goods/stock_del',
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

        },
        stock: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    del_url: 'goods/stock_del',
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

        },

        dockselectgoods: function(){
            var dock_type = $('#dock-type').val();
            $.getScript("/content/dock/" + dock_type + "/select_goods.js?v=2.22", function() {
                // Controller.api.bindevent();
                Form.api.bindevent($("form[role=form]"), function(data, ret){

                    console.log(data)
                    Fast.api.close(JSON.parse(data));
                })
            });
        },
        add: function () {
            Controller.api.bindevent();
            var specification = $('#speifition-data').val();
            specification = JSON.parse(specification)
            var grade = $('#grade-data').val();
            grade = JSON.parse(grade);
            $('#c-specification_id').change(function(){
                var specification_id = $(this).val();
                if(specification_id == null){
                    $('#select-sku').empty();
                }else{
                    var html = ``;
                    for(var i = 0; i < specification_id.length; i++){
                        var value = JSON.parse(specification[specification_id[i]].value);
                        var id = specification[specification_id[i]].id;
                        for(var j = 0; j < value.length; j++){
                            var sku_name = value[j].name;
                            html += `<div class="radio sku-checkbox SKU_TYPE"><span propid="${id}-${i}-${j}" sku-type-name="${sku_name}" class="sku-name">`;
                            html += `${sku_name}</span>`;
                            var sku_value = value[j].value.split("|");
                            for(var l = 0; l < sku_value.length; l++){
                                html += `
                                    <label for="value-${id}-${j}-${l}">
                                        <input id="value-${id}-${j}-${l}" propvalid="${id}-${j}-${l}" class="sku-value" name="sku_value[${sku_name}][]" type="checkbox" data-name="${sku_value[l]}" value="${sku_value[l]},${id}-${j}-${l}">
                                        ${sku_value[l]}
                                    </label>`;
                            }
                            html += `</div>`;
                        }
                    }
                    $('#select-sku').empty();
                    $('#select-sku').append(html);
                }

            })
            var alreadySetSkuVals = {};//已经设置的SKU值数据
            $(document).on("change",'.sku-value',function(){
                var skuTypeArr =  [];//存放SKU类型的数组
                var totalRow = 1;//总行数
                //获取元素类型
                $(".SKU_TYPE").each(function(){
                    //SKU类型节点
                    var skuTypeNode = $(this).children("span");
                    var skuTypeObj = {};//sku类型对象
                    //SKU属性类型标题
                    skuTypeObj.skuTypeTitle = $(skuTypeNode).attr("sku-type-name");
                    //SKU属性类型主键
                    var propid = $(skuTypeNode).attr("propid");
                    skuTypeObj.skuTypeKey = propid;
                    skuValueArr = [];//存放SKU值得数组
                    //SKU相对应的节点
                    var skuValNode = $(this);
                    //获取SKU值
                    var skuValCheckBoxs = $(skuValNode).find("input[type='checkbox'][class*='sku-value']");
                    var checkedNodeLen = 0 ;//选中的SKU节点的个数
                    $(skuValCheckBoxs).each(function(){
                        if($(this).is(":checked")){
                            var skuValObj = {};//SKU值对象
                            skuValObj.skuValueTitle = $(this).data('name');//SKU值名称
                            skuValObj.skuValueId = $(this).attr("propvalid");//SKU值主键
                            skuValObj.skuPropId = $(this).attr("propid");//SKU类型主键
                            skuValueArr.push(skuValObj);
                            checkedNodeLen ++ ;
                        }
                    });
                    if(skuValueArr && skuValueArr.length > 0){
                        totalRow = totalRow * skuValueArr.length;
                        skuTypeObj.skuValues = skuValueArr;//sku值数组
                        skuTypeObj.skuValueLen = skuValueArr.length;//sku值长度
                        skuTypeArr.push(skuTypeObj);//保存进数组中
                    }
                });
                if(skuTypeArr.length > 0){
                    var SKUTableDom = "";//sku表格数据
                    SKUTableDom += "<table class='skuTable'><tr>";
                    //创建表头
                    for(var t = 0 ; t < skuTypeArr.length ; t ++){
                        SKUTableDom += '<th>'+skuTypeArr[t].skuTypeTitle+'</th>';
                    }
                    SKUTableDom += '<th>零售价</th>';
                    for(var i = 0; i < grade.length; i++){
                        SKUTableDom += `<th>${grade[i].name}代理价</th>`;
                    }
                    SKUTableDom += "</tr>";
                    //循环处理表体
                    for(var i = 0 ; i < totalRow ; i ++){//总共需要创建多少行
                        var currRowDoms = "";
                        var rowCount = 1;//记录行数
                        var propvalidArr = [];//记录SKU值主键
                        var propIdArr = [];//属性类型主键
                        var propvalnameArr = [];//记录SKU值标题
                        var propNameArr = [];//属性类型标题
                        for(var j = 0 ; j < skuTypeArr.length ; j ++){//sku列
                            var skuValues = skuTypeArr[j].skuValues;//SKU值数组
                            var skuValueLen = skuValues.length;//sku值长度
                            rowCount = (rowCount * skuValueLen);//目前的生成的总行数
                            var anInterBankNum = (totalRow / rowCount);//跨行数
                            var point = ((i / anInterBankNum) % skuValueLen);
                            propNameArr.push(skuTypeArr[j].skuTypeTitle);
                            if(0  == (i % anInterBankNum)){//需要创建td
                                currRowDoms += '<td rowspan='+anInterBankNum+'>'+skuValues[point].skuValueTitle+'</td>';
                                propvalidArr.push(skuValues[point].skuValueId);
                                propIdArr.push(skuValues[point].skuPropId);
                                propvalnameArr.push(skuValues[point].skuValueTitle);
                            }else{
                                //当前单元格为跨行
                                propvalidArr.push(skuValues[parseInt(point)].skuValueId);
                                propIdArr.push(skuValues[parseInt(point)].skuPropId);
                                propvalnameArr.push(skuValues[parseInt(point)].skuValueTitle);
                            }
                        }

                        var propvalids = propvalidArr.toString()
                        var alreadySetSkuPrice = "";//已经设置的SKU价格
                        var alreadySetSkuStock = "";//已经设置的SKU库存
                        //赋值
                        if(alreadySetSkuVals){
                            var currGroupSkuVal = alreadySetSkuVals[propvalids];//当前这组SKU值
                            if(currGroupSkuVal){
                                alreadySetSkuPrice = currGroupSkuVal.skuPrice;
                                alreadySetSkuStock = currGroupSkuVal.skuStock
                            }
                        }
                        //console.log(propvalids);
                        var _propids = propIdArr.toString();
                        var _propvalnames = propvalnameArr.join(";");
                        var _propnames = propNameArr.join(";");
                        SKUTableDom += `<tr propvalids="${propvalids}" propids="${_propids}" propvalnames="${_propvalnames}" propnames="${_propnames}" class="sku_table_tr">${currRowDoms}`;
                        SKUTableDom += `<td><input name="sku[price][${propvalids}][price]" type="number" class="setting_sku_price" value="${alreadySetSkuPrice}"/></td>`;
                        for(var j = 0; j < grade.length; j++){
                            SKUTableDom += `<td><input name="sku[price][${propvalids}][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value=""/></td>`;
                        }

                        SKUTableDom += `</tr>`;
                    }
                    SKUTableDom += "</table>";
                    $('#sku-table').empty();
                    $('#sku-table').append(SKUTableDom)
                }else{
                    var SKUTableDom = "";//sku表格数据
                    SKUTableDom += "<table class='skuTable'><tr>";
                    SKUTableDom += '<th>零售价</th>';
                    for(var i = 0; i < grade.length; i++){
                        SKUTableDom += `<th>${grade[i].name}代理价</th>`;
                    }
                    SKUTableDom += "</tr>";
                    SKUTableDom += `<tr class="sku_table_tr">`;
                    SKUTableDom += `<td><input name="sku[price][price]" type="number" class="setting_sku_price" value=""/></td>`;
                    for(var j = 0; j < grade.length; j++){
                        SKUTableDom += `<td><input name="sku[price][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value=""/></td>`;
                    }
                    SKUTableDom += `</tr>`;
                    SKUTableDom += "</table>";
                    $('#sku-table').empty();
                    $('#sku-table').append(SKUTableDom)
                }

            });

            var SKUTableDom = "";//sku表格数据
            SKUTableDom += "<table class='skuTable'><tr>";
            SKUTableDom += '<th>零售价</th>';
            for(var i = 0; i < grade.length; i++){
                SKUTableDom += `<th>${grade[i].name}代理价</th>`;
            }
            SKUTableDom += "</tr>";
            SKUTableDom += `<tr class="sku_table_tr">`;
            SKUTableDom += `<td><input name="sku[price][price]" type="number" class="setting_sku_price" value=""/></td>`;
            for(var j = 0; j < grade.length; j++){
                SKUTableDom += `<td><input name="sku[price][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value=""/></td>`;
            }
            SKUTableDom += `</tr>`;
            SKUTableDom += "</table>";
            $('#sku-table').empty();
            $('#sku-table').append(SKUTableDom)


            $('#dock-select-btn').click(function(){
                var dock_id = $('#c-dock_id').val();
                if(dock_id == 0){
                    Toastr.error('请先选择对接网站');
                    return;
                }
                Fast.api.open('goods/dockselectgoods?dock_id=' + dock_id, '选择对接商品', {
                    callback:function(goods_info){
                        $('#c-name').val(goods_info.ys_dock_data.name);
                        $('#c-buy_default').val(goods_info.ys_dock_data.buy_default);
                        $('#c-buy_price').val(goods_info.ys_dock_data.price);
                        $('#c-details').summernote("reset");
                        // if(goods_info.ys_dock_data.details != ""){
                        //     $('#c-details').summernote('pasteHTML', goods_info.ys_dock_data.details)
                        // }
                        $('#c-images').val(goods_info.ys_dock_data.images);
                        $('#remote_id').val(goods_info.ys_dock_data.remote_id);
                        $('#c-stock').val(goods_info.ys_dock_data.stock);
                        $('#inputs').val(JSON.stringify(goods_info.ys_dock_data.inputs))
                        $('#dock_id').val(dock_id);

                        delete goods_info.ys_dock_data;


                        var dock_data = JSON.stringify(goods_info)
                        $('#dock-data').val(dock_data)


                    }
                });

            })

            $('#c-dock_id').change(function(){
                var dock_id = $(this).val();
                if(dock_id == 0) $('#dock-select-btn-box').addClass('ys-hidden');
                if(dock_id > 0) $('#dock-select-btn-box').removeClass('ys-hidden');
            })

            $("input[name='row[goods_type]']").change(function(){
                var goods_type = $(this).val();
                if(goods_type == 'manual'){
                    $('.ys-stock').removeClass('ys-hidden'); //显示库存表单
                }else{
                    $('.ys-stock').addClass('ys-hidden'); //隐藏库存表单
                }
                if(goods_type == 'dock'){
                    $('.goods-type-dock').removeClass('ys-hidden'); //显示对接选项
                }else{
                    $('.goods-type-dock').addClass('ys-hidden'); //隐藏对接选项
                }

            })
        },
        stockshow: function () {
            Controller.api.bindevent();
        },
        stock_add: function () {
            //初始化表格
            var skuData = $('#sku-data').val();
            if(skuData != ''){
                var alreadySetSkuVals = {};//已经设置的SKU值数据
                skuData = JSON.parse(skuData)
                console.log(skuData)
                var skuTotal = 1;
                var SKUTableDom = "";//sku表格数据
                SKUTableDom += "<table class='skuTable'><tr>";
                //创建表头
                for(key in skuData){
                    if(skuData[key].value == undefined) continue;
                    SKUTableDom += '<th>'+ key +'</th>';
                    skuTotal = skuTotal * skuData[key].value.length;
                }
                SKUTableDom += '<th>添加库存</th>';
                SKUTableDom += "</tr>";
                //循环处理表体
                for(var i = 0 ; i < skuTotal ; i ++){//总共需要创建多少行
                    var currRowDoms = "";
                    var rowCount = 1;//记录行数
                    var propvalidArr = [];//记录SKU值主键
                    var propIdArr = [];//属性类型主键
                    var propvalnameArr = [];//记录SKU值标题
                    var propNameArr = [];//属性类型标题
                    for(key in skuData){
                        if(skuData[key].value == undefined) continue;
                        var skuValues = skuData[key].value;//SKU值数组
                        var skuValueLen = skuValues.length;//sku值长度
                        rowCount = (rowCount * skuValueLen);//目前的生成的总行数
                        var anInterBankNum = (skuTotal / rowCount);//跨行数
                        var point = ((i / anInterBankNum) % skuValueLen);
                        propNameArr.push(skuData[key].name);
                        if(0  == (i % anInterBankNum)){//需要创建td
                            currRowDoms += '<td rowspan='+anInterBankNum+'>'+skuValues[point].name+'</td>';
                            propvalidArr.push(skuValues[point].id);
                            propIdArr.push(skuValues[point].id);
                            propvalnameArr.push(skuValues[point].name);
                        }else{
                            //当前单元格为跨行
                            propvalidArr.push(skuValues[parseInt(point)].id);
                            propIdArr.push(skuValues[parseInt(point)].id);
                            propvalnameArr.push(skuValues[parseInt(point)].name);
                        }
                    }


                    var propvalids = propvalidArr.toString()
                    var alreadySetSkuPrice = "";//已经设置的SKU价格
                    var alreadySetSkuStock = "";//已经设置的SKU库存
                    //赋值
                    if(alreadySetSkuVals){
                        var currGroupSkuVal = alreadySetSkuVals[propvalids];//当前这组SKU值
                        if(currGroupSkuVal){
                            alreadySetSkuPrice = currGroupSkuVal.skuPrice;
                            alreadySetSkuStock = currGroupSkuVal.skuStock
                        }
                    }
                    //console.log(propvalids);
                    var _propids = propIdArr.toString();
                    var _propvalnames = propvalnameArr.join(";");
                    var _propnames = propNameArr.join(";");
                    SKUTableDom += `<tr propvalids="${propvalids}" propids="${_propids}" propvalnames="${_propvalnames}" propnames="${_propnames}" class="sku_table_tr">${currRowDoms}`;
                    SKUTableDom += `<td style="padding: 0;"><textarea name="stock[${propvalids}]" class="setting_sku_price" value="${alreadySetSkuPrice}" style="resize: vertical; border: none; width: 100%; height: 60px;"></textarea></td>`;
                    SKUTableDom += `</tr>`;
                }
                SKUTableDom += "</table>";
                $('#sku-table').empty();
                $('#sku-table').append(SKUTableDom)
            }

            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
            var specification = $('#speifition-data').val();
            specification = JSON.parse(specification)
            var grade = $('#grade-data').val();
            grade = JSON.parse(grade);
            console.log(specification);
            console.log(grade);
            $('#c-specification_id').change(function(){ //选择规格
                var specification_id = $(this).val();
                if(specification_id == null){
                    $('#select-sku').empty();
                }else{
                    var html = ``;
                    for(var i = 0; i < specification_id.length; i++){
                        var value = JSON.parse(specification[specification_id[i]].value);
                        var id = specification[specification_id[i]].id;
                        for(var j = 0; j < value.length; j++){
                            var sku_name = value[j].name;
                            html += `<div class="radio sku-checkbox SKU_TYPE"><span propid="${id}-${i}-${j}" sku-type-name="${sku_name}" class="sku-name">`;
                            html += `${sku_name}</span>`;
                            var sku_value = value[j].value.split("|");
                            for(var l = 0; l < sku_value.length; l++){
                                html += `
                                    <label for="value-${id}-${j}-${l}">
                                        <input id="value-${id}-${j}-${l}" propvalid="${id}-${j}-${l}" class="sku-value" name="sku_value[${sku_name}][]" type="checkbox" data-name="${sku_value[l]}" value="${sku_value[l]},${id}-${j}-${l}">
                                        ${sku_value[l]}
                                    </label>`;
                            }
                            html += `</div>`;
                        }
                    }
                    $('#select-sku').empty();
                    $('#select-sku').append(html);
                }
            })
            var alreadySetSkuVals = {};//已经设置的SKU值数据
            $(document).on("change",'.sku-value',function(){
                var skuTypeArr =  [];//存放SKU类型的数组
                var totalRow = 1;//总行数
                //获取元素类型
                $(".SKU_TYPE").each(function(){
                    //SKU类型节点
                    var skuTypeNode = $(this).children("span");
                    var skuTypeObj = {};//sku类型对象
                    //SKU属性类型标题
                    skuTypeObj.skuTypeTitle = $(skuTypeNode).attr("sku-type-name");
                    //SKU属性类型主键
                    var propid = $(skuTypeNode).attr("propid");
                    skuTypeObj.skuTypeKey = propid;
                    skuValueArr = [];//存放SKU值得数组
                    //SKU相对应的节点
                    var skuValNode = $(this);
                    //获取SKU值
                    var skuValCheckBoxs = $(skuValNode).find("input[type='checkbox'][class*='sku-value']");
                    var checkedNodeLen = 0 ;//选中的SKU节点的个数
                    $(skuValCheckBoxs).each(function(){
                        if($(this).is(":checked")){
                            var skuValObj = {};//SKU值对象
                            skuValObj.skuValueTitle = $(this).data('name');//SKU值名称
                            skuValObj.skuValueId = $(this).attr("propvalid");//SKU值主键
                            skuValObj.skuPropId = $(this).attr("propid");//SKU类型主键
                            skuValueArr.push(skuValObj);
                            checkedNodeLen ++ ;
                        }
                    });
                    if(skuValueArr && skuValueArr.length > 0){
                        totalRow = totalRow * skuValueArr.length;
                        skuTypeObj.skuValues = skuValueArr;//sku值数组
                        skuTypeObj.skuValueLen = skuValueArr.length;//sku值长度
                        skuTypeArr.push(skuTypeObj);//保存进数组中
                    }
                });

                if(skuTypeArr.length > 0){
                    var SKUTableDom = "";//sku表格数据
                    SKUTableDom += "<table class='skuTable'><tr>";
                    console.log(skuTypeArr)
                    //创建表头
                    for(var t = 0 ; t < skuTypeArr.length ; t ++){
                        SKUTableDom += '<th>'+skuTypeArr[t].skuTypeTitle+'</th>';
                    }
                    SKUTableDom += '<th>零售价</th>';
                    for(var i = 0; i < grade.length; i++){
                        SKUTableDom += `<th>${grade[i].name}代理价</th>`;
                    }
                    SKUTableDom += "</tr>";
                    //循环处理表体
                    for(var i = 0 ; i < totalRow ; i ++){//总共需要创建多少行
                        var currRowDoms = "";
                        var rowCount = 1;//记录行数
                        var propvalidArr = [];//记录SKU值主键
                        var propIdArr = [];//属性类型主键
                        var propvalnameArr = [];//记录SKU值标题
                        var propNameArr = [];//属性类型标题
                        for(var j = 0 ; j < skuTypeArr.length ; j ++){//sku列
                            var skuValues = skuTypeArr[j].skuValues;//SKU值数组
                            var skuValueLen = skuValues.length;//sku值长度
                            rowCount = (rowCount * skuValueLen);//目前的生成的总行数
                            var anInterBankNum = (totalRow / rowCount);//跨行数
                            var point = ((i / anInterBankNum) % skuValueLen);
                            propNameArr.push(skuTypeArr[j].skuTypeTitle);
                            if(0  == (i % anInterBankNum)){//需要创建td
                                currRowDoms += '<td rowspan='+anInterBankNum+'>'+skuValues[point].skuValueTitle+'</td>';
                                propvalidArr.push(skuValues[point].skuValueId);
                                propIdArr.push(skuValues[point].skuPropId);
                                propvalnameArr.push(skuValues[point].skuValueTitle);
                            }else{
                                //当前单元格为跨行
                                propvalidArr.push(skuValues[parseInt(point)].skuValueId);
                                propIdArr.push(skuValues[parseInt(point)].skuPropId);
                                propvalnameArr.push(skuValues[parseInt(point)].skuValueTitle);
                            }
                        }

                        var propvalids = propvalidArr.toString()
                        var alreadySetSkuPrice = "";//已经设置的SKU价格
                        var alreadySetSkuStock = "";//已经设置的SKU库存
                        //赋值
                        if(alreadySetSkuVals){
                            var currGroupSkuVal = alreadySetSkuVals[propvalids];//当前这组SKU值
                            if(currGroupSkuVal){
                                alreadySetSkuPrice = currGroupSkuVal.skuPrice;
                                alreadySetSkuStock = currGroupSkuVal.skuStock
                            }
                        }
                        //console.log(propvalids);
                        var _propids = propIdArr.toString();
                        var _propvalnames = propvalnameArr.join(";");
                        var _propnames = propNameArr.join(";");
                        SKUTableDom += `<tr propvalids="${propvalids}" propids="${_propids}" propvalnames="${_propvalnames}" propnames="${_propnames}" class="sku_table_tr">${currRowDoms}`;
                        SKUTableDom += `<td><input name="sku[price][${propvalids}][price]" type="number" class="setting_sku_price" value="${alreadySetSkuPrice}"/></td>`;
                        for(var j = 0; j < grade.length; j++){
                            SKUTableDom += `<td><input name="sku[price][${propvalids}][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value=""/></td>`;
                        }

                        SKUTableDom += `</tr>`;
                    }
                    SKUTableDom += "</table>";
                    $('#sku-table').empty();
                    $('#sku-table').append(SKUTableDom)
                }else{
                    var SKUTableDom = "";//sku表格数据
                    SKUTableDom += "<table class='skuTable'><tr>";
                    SKUTableDom += '<th>零售价</th>';
                    for(var i = 0; i < grade.length; i++){
                        SKUTableDom += `<th>${grade[i].name}代理价</th>`;
                    }
                    SKUTableDom += "</tr>";
                    SKUTableDom += `<tr class="sku_table_tr">`;
                    SKUTableDom += `<td><input name="sku[price][price]" type="number" class="setting_sku_price" value=""/></td>`;
                    for(var j = 0; j < grade.length; j++){
                        SKUTableDom += `<td><input name="sku[price][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value=""/></td>`;
                    }
                    SKUTableDom += `</tr>`;
                    SKUTableDom += "</table>";
                    $('#sku-table').empty();
                    $('#sku-table').append(SKUTableDom)
                }

            });

            //初始化表格
            var skuData = $('#sku-data').val();
            if(skuData == ''){
                var priceData = $('#price-data').val();
                priceData =  JSON.parse(priceData);
                console.log(priceData)
                var SKUTableDom = "";//sku表格数据
                SKUTableDom += "<table class='skuTable'><tr>";
                SKUTableDom += '<th>零售价</th>';
                for(var i = 0; i < grade.length; i++){
                    SKUTableDom += `<th>${grade[i].name}代理价</th>`;
                }
                SKUTableDom += "</tr>";
                SKUTableDom += `<tr class="sku_table_tr">`;
                SKUTableDom += `<td><input name="sku[price][price]" type="number" class="setting_sku_price" value="${priceData[0]}"/></td>`;
                for(var j = 0; j < grade.length; j++){
                    SKUTableDom += `<td><input name="sku[price][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value="${priceData[grade[j].grade_id]}"/></td>`;
                }
                SKUTableDom += `</tr>`;
                SKUTableDom += "</table>";
            }else{
                var priceData = $('#price-data').val();
                console.log(skuData)
                priceData =  JSON.parse(priceData);
                console.log(priceData)
                skuData = JSON.parse(skuData)
                console.log(skuData)
                var skuTotal = 1;
                var SKUTableDom = "";//sku表格数据
                SKUTableDom += "<table class='skuTable'><tr>";
                //创建表头
                for(key in skuData){
                    if(skuData[key].value == undefined) continue;
                    SKUTableDom += '<th>'+ key +'</th>';
                    skuTotal = skuTotal * skuData[key].value.length;
                }
                SKUTableDom += '<th>零售价</th>';
                for(var i = 0; i < grade.length; i++){
                    SKUTableDom += `<th>${grade[i].name}代理价</th>`;
                }
                SKUTableDom += "</tr>";
                //循环处理表体
                for(var i = 0 ; i < skuTotal ; i ++){//总共需要创建多少行
                    var currRowDoms = "";
                    var rowCount = 1;//记录行数
                    var propvalidArr = [];//记录SKU值主键
                    var propIdArr = [];//属性类型主键
                    var propvalnameArr = [];//记录SKU值标题
                    var propNameArr = [];//属性类型标题
                    for(key in skuData){
                        if(skuData[key].value == undefined) continue;
                        var skuValues = skuData[key].value;//SKU值数组
                        var skuValueLen = skuValues.length;//sku值长度
                        rowCount = (rowCount * skuValueLen);//目前的生成的总行数
                        var anInterBankNum = (skuTotal / rowCount);//跨行数
                        var point = ((i / anInterBankNum) % skuValueLen);
                        propNameArr.push(skuData[key].name);
                        if(0  == (i % anInterBankNum)){//需要创建td
                            currRowDoms += '<td rowspan='+anInterBankNum+'>'+skuValues[point].name+'</td>';
                            propvalidArr.push(skuValues[point].id);
                            propIdArr.push(skuValues[point].id);
                            propvalnameArr.push(skuValues[point].name);
                        }else{
                            //当前单元格为跨行
                            propvalidArr.push(skuValues[parseInt(point)].id);
                            propIdArr.push(skuValues[parseInt(point)].id);
                            propvalnameArr.push(skuValues[parseInt(point)].name);
                        }
                    }

                    var propvalids = propvalidArr.toString()
                    var _propids = propIdArr.toString();
                    var _propvalnames = propvalnameArr.join(";");
                    var _propnames = propNameArr.join(";");
                    SKUTableDom += `<tr propvalids="${propvalids}" propids="${_propids}" propvalnames="${_propvalnames}" propnames="${_propnames}" class="sku_table_tr">${currRowDoms}`;
                    SKUTableDom += `<td><input name="sku[price][${propvalids}][price]" type="number" class="setting_sku_price" value="${priceData[propvalids][0]}" /></td>`;
                    for(var j = 0; j < grade.length; j++){
                        SKUTableDom += `<td><input name="sku[price][${propvalids}][grade_${grade[j].grade_id}]" type="number" class="setting_sku_price" value="${priceData[propvalids][grade[j].grade_id]}"/></td>`;
                    }
                    SKUTableDom += `</tr>`;
                }
                SKUTableDom += "</table>";
            }

            $('#sku-table').empty();
            $('#sku-table').append(SKUTableDom)



            $('#dock-select-btn').click(function(){
                var dock_id = $('#c-dock_id').val();
                if(dock_id == 0){
                    Toastr.error('请先选择对接网站');
                    return;
                }
                Fast.api.open('goods/dockselectgoods?dock_id=' + dock_id, '选择对接商品', {
                    callback:function(goods_info){
                        $('#c-name').val(goods_info.ys_dock_data.name);
                        $('#c-buy_default').val(goods_info.ys_dock_data.buy_default);
                        $('#c-buy_price').val(goods_info.ys_dock_data.price);
                        $('#c-details').summernote("reset");
                        // if(goods_info.ys_dock_data.details != ""){
                        //     $('#c-details').summernote('pasteHTML', goods_info.ys_dock_data.details)
                        // }
                        $('#c-images').val(goods_info.ys_dock_data.images);
                        $('#remote_id').val(goods_info.ys_dock_data.remote_id);
                        $('#c-stock').val(goods_info.ys_dock_data.stock);
                        $('#inputs').val(JSON.stringify(goods_info.ys_dock_data.inputs))
                        $('#dock_id').val(dock_id);

                        delete goods_info.ys_dock_data;


                        var dock_data = JSON.stringify(goods_info)
                        $('#dock-data').val(dock_data)


                    }
                });

            })

            $('#c-dock_id').change(function(){
                var dock_id = $(this).val();
                if(dock_id == 0) $('#dock-select-btn-box').addClass('ys-hidden');
                if(dock_id > 0) $('#dock-select-btn-box').removeClass('ys-hidden');
            })

            $("input[name='row[goods_type]']").change(function(){
                var goods_type = $(this).val();
                if(goods_type == 'manual'){
                    $('.ys-stock').removeClass('ys-hidden'); //显示库存表单
                }else{
                    $('.ys-stock').addClass('ys-hidden'); //隐藏库存表单
                }
                if(goods_type == 'dock'){
                    $('.goods-type-dock').removeClass('ys-hidden'); //显示对接选项
                }else{
                    $('.goods-type-dock').addClass('ys-hidden'); //隐藏对接选项
                }

            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            sku: {
                get: function () {
                    var userinfo = localStorage.getItem("hmyk_userinfo");
                    console.log(userinfo)
                    return userinfo ? JSON.parse(userinfo) : null;
                },
                set: function (data) {
                    if (data) {
                        localStorage.setItem("hmyk_userinfo", JSON.stringify(data));
                    } else {
                        localStorage.removeItem("hmyk_userinfo");
                    }
                }
            }
        }
    };
    return Controller;
});
