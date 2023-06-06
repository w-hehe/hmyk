define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/order/goods/index' + location.search,
                    add_url: 'finance/order/goods/add',
                    edit_url: 'finance/order/goods/edit',
                    del_url: 'finance/order/goods/del',
                    multi_url: 'finance/order/goods/multi',
                    import_url: 'finance/order/goods/import',
                    table: 'goods_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                commonSearch: false,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'p_trade_no', title: __('P_trade_no'), operate: 'LIKE'},
                        {field: 'out_trade_no', title: __('订单号'), operate: 'LIKE'},
                        {field: 'user.username', title: __('用户')},
                        {
                            field: 'goods_name',
                            title: __('商品'),
                            operate: 'LIKE',
                            align: 'left',
                            formatter: function(value, row, index){
                                if(row.sku){
                                    return '<div style="white-space: normal; width: max-content; max-width: 300px;">' + value + ' >>&nbsp; ' + row.sku_name + '：' + row.sku + '</div>';
                                }else{
                                    return value;
                                }
                            }
                        },
                        {field: 'money', title: __('订单金额'), operate:'BETWEEN'},
                        {field: 'goods_num', title: __('购买数量')},
                        {field: 'goods_money', title: __('商品单价'), operate:'BETWEEN'},
                        {
                            field: 'id', 
                            title: __('下单信息'),
                            align: 'left',
                            formatter: function(value, row, index){
                                var str = '';
                                if(row.mobile){
                                    str += '手机号码：' + row.mobile + '<br>';
                                }
                                if(row.email){
                                    str += '电子邮箱：' + row.email + '<br>';
                                }
                                if(row.password){
                                    str += '查单密码：' + row.password + '<br>';
                                }
                                return str;
                            }
                        },
                        {field: 'create_time', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_time', title: __('付款时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'pay_type',
                            title: __('付款方式'),
                            operate: 'LIKE',
                            formatter: function(value, row, index){
                                if(row.money == 0) return '免费商品';
                                if(value == 'balance') return '余额支付';
                                if(value == 'alipay') return '支付宝';
                                if(value == 'wxpay') return '微信支付';
                                if(value == 'qqpay') return 'QQ支付';
                                if(value == 'usdt') return 'USDT PAY';
                            }
                        },

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table, events: Table.api.events.operate,
                            buttons: [
                                /*{
                                    name: 'ajax',
                                    title: '补单',
                                    text: '补单',
                                    classname: 'btn btn-xs btn-info btn-magic btn-ajax',
                                    icon: 'fa fa-check-square-o',
                                    confirm: '确定将订单状态改为已支付？',
                                    url: 'finance.order.goods/supplement',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh', {})
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    hidden:function(row){
                                        if(row.pay_time != null){
                                            return true;
                                        }
                                    }
                                },*/
                                {
                                    name: 'deliver',
                                    title: __('订单发货'), //标题
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-share',
                                    url: 'finance.order.goods/deliver',
                                    text:'发货', //按钮
                                    hidden:function(row){
                                        if(row.goods_type != 'invented' || !row.pay_time) {
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('订单详情'), //标题
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-flickr',
                                    url: 'finance.order.goods/detail',
                                    text:'订单详情', //按钮
                                    hidden:function(row){
                                        // if(row.goods_type == 'dock') return true;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
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
            Controller.api.bindevent();
        },
        deliver: function () {
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
