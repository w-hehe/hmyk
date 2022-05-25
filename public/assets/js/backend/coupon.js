define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/index' + location.search,
                    add_url: 'coupon/add',
                    edit_url: 'coupon/edit',
                    del_url: 'coupon/del',
                    multi_url: 'coupon/multi',
                    import_url: 'coupon/import',
                    table: 'coupon',
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
                        {field: 'value', title: '优惠码'},
                        {
                            field: 'type',
                            title: '优惠类型',
                            formatter:function(value,row,index){
                                if(value == 0){
                                    return '百分比';
                                }else{
                                    return '固定金额';
                                }

                            }
                        },
                        {field: 'discount', title: '折扣'},
                        {field: 'use_num', title: '已使用次数'},
                        {
                            field: 'max_use',
                            title: '最大使用次数',
                            formatter:function(value,row,index){
                                if(value == 0 || value == ''){
                                    return '不限次';
                                }else{
                                    return value;
                                }

                            }
                        },
                        {
                            field: 'apply',
                            title: '适用于',
                            formatter:function(value){
                                if(value == 0){
                                    return '通用';
                                }
                                if(value == 1){
                                    return '分类';
                                }
                                if(value == 2){
                                    return '商品';
                                }

                            }
                        },
                        {field: 'remark', title: '备注'},
                        {field: 'expire_time', title: '过期时间'},
                        {field: 'operate', title: '操作', table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $('.btn-switch').click(function(){
                layer.load();
                $.get("coupon/pon", function(e){
                    if(e.code == 200){
                        layer.closeAll();
                        Toastr.success(e.msg);
                        if(e.data == 1){ //已开启
                            $('#pon-text').html('关闭优惠券');
                            $('#pon-icon').attr('class', 'fa fa-toggle-on');
                        }else{ //已关闭
                            $('#pon-text').html('开启优惠券');
                            $('#pon-icon').attr('class', 'fa fa-toggle-off');
                        }
                    }else{
                        layer.closeAll('loading');
                        Toastr.error(e.msg);
                    }
                }).error(function(){
                    layer.closeAll('loading');
                    Toastr.error('服务器错误！');
                })
            })


        },
        add: function () {
            Controller.api.bindevent();

            $("input[name='row[apply]']").change(function(){
                var value = $(this).val();
                console.log(value);
                if(value == 0){
                    $('.select-category').hide();
                    $('.select-goods').hide();
                }
                if(value == 1){
                    $('.select-category').show();
                    $('.select-goods').hide();
                }
                if(value == 2){
                    $('.select-category').hide();
                    $('.select-goods').show();
                }
            })


        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[apply]']").change(function(){
                var value = $(this).val();
                console.log(value);
                if(value == 0){
                    $('.select-category').hide();
                    $('.select-goods').hide();
                }
                if(value == 1){
                    $('.select-category').show();
                    $('.select-goods').hide();
                }
                if(value == 2){
                    $('.select-category').hide();
                    $('.select-goods').show();
                }
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
