define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "订单号/商品名称/下单账号/用户账号";};
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/index' + location.search,
                    edit_url: 'order/edit',
                    del_url: 'order/del',
                    table: 'order',
                }
            });

            var table = $("#table");

            // 初始化表格
            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'order_no', title: __('订单号')},
                        {
                            field: 'user.nickname',
                            title: '用户 / 验证信息',
                            formatter:function(value,row,index){
                                var user = '';
                                var email = '';
                                var password = '';
                                if(row.user.nickname){
                                    if(row.user.nickname.length > 30){
                                        user = row.user.nickname.toString().substr(0, 30) + '...';
                                    }else{
                                        user = row.user.nickname;
                                    }
                                }else{
                                    user = '游客';
                                }

                                if(row.email){
                                    if(row.email.length > 30){
                                        email = row.email.toString().substr(0, 30) + '...';
                                    }else{
                                        email = row.email;
                                    }
                                }else{
                                    email = '';
                                }

                                if(row.password){
                                    if(row.password.length > 30){
                                        password = row.password.toString().substr(0, 30) + '...';
                                    }else{
                                        password = row.password;
                                    }
                                }else{
                                    password = '';
                                }


                                if(email != '' || password != ''){
                                    var str = `<div style="padding: 6px 0; height: 100%;">${user}`;
                                    str += `<hr style="margin: 6px 0;">`;
                                }else{
                                    var str = `<div>${user}`;
                                }
                                if(email != '' && password != ''){
                                    str += `${email}/${password}</div>`;
                                }else{
                                    if(email != '') str += `${email}`;
                                    if(password != '') str += `${password}`;
                                }
                                str += `</div>`;
                                return str;
                            }
                        },
                        {field: 'goods.name', title: __('Goods_name'), operate: 'LIKE'},
                        // {field: 'goods_cover', title: __('Goods_cover'), operate: 'LIKE'},
                        {
                            field: 'goods_money',
                            title: '商品单价 / 购买数量',
                            formatter: function(value,row,index){

                                return `${row.goods_money}元 * ${row.buy_num}`;
                            }
                        },
                        {field: 'money', title: __('订单金额'), operate:'BETWEEN'},
                        {
                            field: 'status',
                            title: __('订单状态'),
                            formatter: function(value, row){
                                if(value == 'fail'){
                                    return `<span class="label label-danger">下单失败：${row.dock_explain}</span>`;
                                }else if(value == 'yiguoqi'){
                                    return '<span class="label label-default">已过期</span>';
                                }else if(value == 'wait-pay'){
                                    return '<span class="label label-default">未支付</span>';
                                }else if(value == 'wait-send'){
                                    return '<span class="label label-danger">待发货</span>';
                                }else if(value == 'conduct'){
                                    return '<span class="label label-success">进行中</span>';
                                }else if(value == 'success'){
                                    return '<span class="label label-success">已完成</span>';
                                }else {
                                    return '<span class="label label-warning">状态有误</span>';
                                }

                            }
                        },
                        {
                            field: 'pay_type',
                            title: '支付方式',
                            formatter: function(value){
                                if(value == 'alipay'){
                                    return '<span class="label label-success">支付宝支付</span>';
                                }else if(value == 'wxpay'){
                                    return '<span class="label label-success">微信支付</span>';
                                }else if(value == 'qqpay'){
                                    return '<span class="label label-success">QQ支付</span>';
                                }else {
                                    return '-';
                                }

                            }
                        },
                        {
                            field: 'createtime',
                            title: '下单时间 / 支付时间',
                            operate:'RANGE',
                            addclass:'datetimerange',
                            autocomplete:false,
                            formatter: function(value,row,index){

                                return `<div style="padding: 6px 0; height: 100%;">${row.create_time}<hr style="margin: 6px 0;">${row.pay_time}</div>`;
                            }
                        },
                        // {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'click',
                                    title: __('同步状态'),
                                    text: __('同步状态'),
                                    classname: 'btn btn-xs btn-info btn-click',
                                    icon: 'fa fa-exchange',
                                    // dropdown: '更多',//如果包含dropdown，将会以下拉列表的形式展示
                                    click: function (data, row) {
                                        // console.log(row)
                                        layer.load();
                                        $.get("order/sync_order_status", row, function(e){
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
                                        if(row.goods.dock_id == 0 || row.status == 'success' || row.status == 'wait-pay' || row.status == 'fail'){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'sendgoods',
                                    title: '发货',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-paper-plane-o',
                                    url: 'order/sendgoods',
                                    text:'发货',
                                    hidden:function(row){
                                        if(row.goods.dock_id > 0 || row.status != 'wait-send') return true;
                                    }

                                },
                                {
                                    name: 'ajax',
                                    title: '补单',
                                    text: '补单',
                                    classname: 'btn btn-xs btn-info btn-magic btn-ajax',
                                    icon: 'fa fa-check-square-o',
                                    confirm: '确认补单？',
                                    url: 'order/supplement',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh', {})
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    hidden:function(row){
                                        if(row.status != 'wait-pay' && row.status != 'fail'){
                                            return true;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate,
                        }
                    ]
                ]
            };


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

            // 初始化表格
            table.bootstrapTable(tableOptions);

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
                    params.status = typeStr;
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        sendgoods: function () {
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
