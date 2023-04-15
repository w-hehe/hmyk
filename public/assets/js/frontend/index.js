define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {

        index: function(){
            // require(['../libs/clipboard/dist/clipboard.min', '../libs/jquery.qrcode/jquery.qrcode.min'], function (Clipboard) {
            //
            // });
        },

        goods: function(){
            $('#buy-btn').click(function(){
                var data = {
                    goods_id: $(this).data('goods-id'), //商品ID
                    num: $('#buy-num').html(), //购买数量
                    sku_id: $('#buy-specs .active').data('sku-id'), //规格
                    pay_type: $('#pay-type .active').data('pay-type'), //支付方式
                    attach: [], //附加选项
                    coupon: $('#coupon-input').val(), //优惠券
                    password: $('#password').val(), //查单密码
                };
                $('.attach-input').each(function(){
                    data.attach.push({title: $(this).data('title'), value: $(this).val()});
                });
                layer.load();
                // console.log(data)
                $.post("/pay/goods", data, function(e){
                    console.log(e);
                    if(e.code == 200){
                        if(e.mode == 'form'){
                            subForm(e.gateway_url, e.data);
                        }
                        if(e.mode == 'scan'){
                            location.href = "/scan.html?data=" + e.data;
                        }
                    }
                    if(e.code == 201){
                        Toastr.success('购买成功');
                        location.href = e.data.url;
                    }
                    if(e.code == 400){
                        Toastr.error(e.msg);
                        layer.closeAll();
                        return;
                    }
                }).error(function(e){
                    layer.closeAll();
                    Toastr.error('请求失败');
                });
            });


            function subForm( url, params, target){
                var tempform = document.createElement("form");
                tempform.action = url;
                tempform.method = "post";
                tempform.style.display="none"
                if(target) {
                    tempform.target = target;
                }
                for (var x in params) {
                    var opt = document.createElement("input");
                    opt.name = x;
                    opt.value = params[x];
                    tempform.appendChild(opt);
                }
                var opt = document.createElement("input");
                opt.type = "submit";
                tempform.appendChild(opt);
                document.body.appendChild(tempform);
                tempform.submit();
                document.body.removeChild(tempform);
            }

            $('.pay-type').click(function(){
                if($(this).hasClass('disabled')){
                    Toastr.error('未登录用户无法使用余额支付');
                    return false;
                }
                var pay_type = $(this).data('pay-type');
                $('.pay-type').removeClass('active');
                $(this).addClass('active');
            });

            $('.select-specs').click(function(){
                if($(this).hasClass('disabled')){
                    // Toastr.error('库存不足');
                    return false;
                }
                $('#buy-num').html(1);
                var price = $(this).data('price');
                if(price > 0) {
                    $('#pay-type-box').show();
                }else{
                    $('#pay-type-box').hide();
                }
                var crossed_price = $(this).data('crossed');
                $('.select-specs').removeClass('active');
                $(this).addClass('active');
                $('#sale-price').html(price);
                $('#reg-price').html(crossed_price);
                $('#jiesheng').html((crossed_price - price).toFixed(2));
                $('#init-stock').html($(this).data('stock'));
                $('#buy-btn').removeAttr('disabled');
                $('#buy-btn-text').html('立即购买');
            });

            $(document).on('click', '[data-qty-control]', function(event) {
                event.preventDefault();

                var qty_num = Number($('#buy-num').html());
                var qty_dir = $(this).data("qty-control");
                // var price = $('.select-specs.active').data('price');
                if (qty_dir == "plus") {
                    qty_num += 1;
                }else{
                    qty_num = (qty_num <= 1) ? 1 : (qty_num -= 1);
                }
                // $('#sale-price').html((price * qty_num).toFixed(2));

                $(this).siblings("[data-count]").data('count', qty_num).text(qty_num);
            });


        },

        login: function () {

            //本地验证未通过时提示
            $("#login-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });

            //忘记密码
            $(document).on("click", ".btn-forgot", function () {
                var id = "resetpwdtpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: __('Reset password'),
                    area: ["450px", "355px"],
                    content: content,
                    success: function (layero) {
                        var rule = $("#resetpwd-form input[name='captcha']").data("rule");
                        Form.api.bindevent($("#resetpwd-form", layero), function (data) {
                            Layer.closeAll();
                        });
                        $(layero).on("change", "input[name=type]", function () {
                            var type = $(this).val();
                            $("div.form-group[data-type]").addClass("hide");
                            $("div.form-group[data-type='" + type + "']").removeClass("hide");
                            $('#resetpwd-form').validator("setField", {
                                captcha: rule.replace(/remote\((.*)\)/, "remote(" + $(this).data("check-url") + ", event=resetpwd, " + type + ":#" + type + ")")
                            });
                            $(".btn-captcha").data("url", $(this).data("send-url")).data("type", type);
                        });
                    }
                });
            });
        },
        register: function () {
            //本地验证未通过时提示
            $("#register-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#register-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            }, function (data) {
                $("input[name=captcha]").next(".input-group-btn").find("img").trigger("click");
            });
        },
        changepwd: function () {
            //本地验证未通过时提示
            $("#changepwd-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#changepwd-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        profile: function () {
            // 给上传按钮添加上传成功事件
            $("#faupload-avatar").data("upload-success", function (data) {
                var url = Fast.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                Toastr.success(__('Uploaded successful'));
            });
            Form.api.bindevent($("#profile-form"));
            $(document).on("click", ".btn-change", function () {
                var that = this;
                var id = $(this).data("type") + "tpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: "修改",
                    area: ["400px", "250px"],
                    content: content,
                    success: function (layero) {
                        var form = $("form", layero);
                        Form.api.bindevent(form, function (data) {
                            location.reload();
                            Layer.closeAll();
                        });
                    }
                });
            });
        },
        attachment: function () {
            require(['table'], function (Table) {

                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'user/attachment',
                    }
                });
                var urlArr = [];
                var multiple = Fast.api.query('multiple');
                multiple = multiple == 'true' ? true : false;

                var table = $("#table");

                table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function (e, row) {
                    if (e.type == 'check' || e.type == 'uncheck') {
                        row = [row];
                    } else {
                        urlArr = [];
                    }
                    $.each(row, function (i, j) {
                        if (e.type.indexOf("uncheck") > -1) {
                            var index = urlArr.indexOf(j.url);
                            if (index > -1) {
                                urlArr.splice(index, 1);
                            }
                        } else {
                            urlArr.indexOf(j.url) == -1 && urlArr.push(j.url);
                        }
                    });
                });

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    sortName: 'id',
                    showToggle: false,
                    showExport: false,
                    fixedColumns: true,
                    fixedRightNumber: 1,
                    columns: [
                        [
                            {field: 'state', checkbox: multiple, visible: multiple, operate: false},
                            {field: 'id', title: __('Id'), operate: false},
                            {
                                field: 'url', title: __('Preview'), formatter: function (value, row, index) {
                                    var html = '';
                                    if (row.mimetype.indexOf("image") > -1) {
                                        html = '<a href="' + row.fullurl + '" target="_blank"><img src="' + row.fullurl + row.thumb_style + '" alt="" style="max-height:60px;max-width:120px"></a>';
                                    } else {
                                        html = '<a href="' + row.fullurl + '" target="_blank"><img src="' + Fast.api.fixurl("ajax/icon") + "?suffix=" + row.imagetype + '" alt="" style="max-height:90px;max-width:120px"></a>';
                                    }
                                    return '<div style="width:120px;margin:0 auto;text-align:center;overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">' + html + '</div>';
                                }
                            },
                            {
                                field: 'filename', title: __('Filename'), formatter: function (value, row, index) {
                                    return '<div style="width:150px;margin:0 auto;text-align:center;overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">' + Table.api.formatter.search.call(this, value, row, index) + '</div>';
                                }, operate: 'like'
                            },
                            {field: 'imagewidth', title: __('Imagewidth'), operate: false},
                            {field: 'imageheight', title: __('Imageheight'), operate: false},
                            {field: 'mimetype', title: __('Mimetype'), formatter: Table.api.formatter.search},
                            {field: 'createtime', title: __('Createtime'), width: 120, formatter: Table.api.formatter.datetime, datetimeFormat: 'YYYY-MM-DD', operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                            {
                                field: 'operate', title: __('Operate'), width: 85, events: {
                                    'click .btn-chooseone': function (e, value, row, index) {
                                        Fast.api.close({url: row.url, multiple: multiple});
                                    },
                                }, formatter: function () {
                                    return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                                }
                            }
                        ]
                    ]
                });

                // 选中多个
                $(document).on("click", ".btn-choose-multi", function () {
                    Fast.api.close({url: urlArr.join(","), multiple: multiple});
                });

                // 为表格绑定事件
                Table.api.bindevent(table);
                require(['upload'], function (Upload) {
                    Upload.api.upload($("#toolbar .faupload"), function () {
                        $(".btn-refresh").trigger("click");
                    });
                });

            });
        }
    };
    return Controller;
});
