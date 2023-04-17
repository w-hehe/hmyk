define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        login: function () {

            //本地验证未通过时提示
            $("#login-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data, ret) {
                location.href = ret.url ? ret.url : "/";
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
        agency: function () {

            $('.submit-disabled').removeClass('disabled');

            //为表单绑定事件
            Form.api.bindevent($(".agency-form"), function (data, ret) {

            }, function (data) {
                $("input[name=captcha]").next(".input-group-btn").find("img").trigger("click");
            });
        },

        merchant: function () {
            $('#grade_id').val('');
            var grade_json = JSON.parse($('#grade-json').val());
            console.log(grade_json)
            $('#grade_id').change(function(){
                var id = $(this).val();

                if(id == '') {
                    $('.bangding').addClass('hide');
                    $('.alert-grade').addClass('hide');
                }else{
                    $('.bangding').addClass('hide');
                    $('.alert-grade').addClass('hide');
                    $('#alert-' + id).removeClass('hide');
                    if(grade_json['_' + id].domain == 0){
                        $('#domain-0').removeClass('hide');
                    }else{
                        $('#domain-1').removeClass('hide');
                    }
                }

            });

            $('.submit-disabled').removeClass('disabled');

            //为表单绑定事件
            Form.api.bindevent($("#open-merchant"), function (data, ret) {

            }, function (data) {
                $("input[name=captcha]").next(".input-group-btn").find("img").trigger("click");
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
        order: function(){
            require(['../libs/clipboard/dist/clipboard.min', '../libs/jquery.qrcode/jquery.qrcode.min'], function (Clipboard) {

                $('.qrcode-text').click(function(){
                    let contentId = $(this).data('content-id');
                    $('#' + contentId + '-btn').hide();
                    $('#' + contentId).show();
                    var id = $(this).data('id');
                    $('#' + id + '-btn').show();
                    $('#' + id).empty();
                })

                $('.text-qrcode').click(function(){
                    var id = $(this).data('id');
                    $('#' + id + '-btn').hide();
                    var contentId = $(this).data('content-id');
                    $('#' + contentId).hide();
                    $('#' + contentId + '-btn').show();
                    $('#' + id).empty();
                    $('#' + id).qrcode($('#' + contentId).html());
                })

                $('.delete-order-btn').click(function(){
                    var id = $(this).data('id'); //订单ID
                    layer.confirm('确定要删除这个订单吗？', {
                        btn: ['按钮一', '按钮二', '按钮三'] //可以无限个按钮
                        ,btn3: function(index, layero){
                            //按钮【按钮三】的回调
                        }
                    }, function(index, layero){
                        //按钮【按钮一】的回调
                    }, function(index){
                        //按钮【按钮二】的回调
                    });
                })



                $('.template-nav-modal').removeAttr('tabindex');
                var clipboard = new Clipboard(".btn-copy", {
                    text: function (trigger) {
                        var id = $(trigger).data("id");
                        // console.log(id)
                        return $('#' + id).html();
                    }
                });
                clipboard.on('success', function (e, a) {
                    Toastr.success("卡密已复制到剪贴板!");
                    e.clearSelection();
                });

                var clipboard2 = new Clipboard(".copy-all", {
                    text: function (trigger) {
                        var id = $(trigger).data("id");
                        // console.log(id)
                        return $('#copy-all-' + id).val();
                    }
                });
                clipboard2.on('success', function (e, a) {
                    Toastr.success("卡密已复制到剪贴板!");
                    e.clearSelection();
                });

            });
        },
        findorder: function(){
            require(['../libs/clipboard/dist/clipboard.min', '../libs/jquery.qrcode/jquery.qrcode.min'], function (Clipboard) {

                $('.qrcode-text').click(function(){
                    let contentId = $(this).data('content-id');
                    $('#' + contentId + '-btn').hide();
                    $('#' + contentId).show();
                    var id = $(this).data('id');
                    $('#' + id + '-btn').show();
                    $('#' + id).empty();
                })

                $('.text-qrcode').click(function(){
                    var id = $(this).data('id');
                    $('#' + id + '-btn').hide();
                    var contentId = $(this).data('content-id');
                    $('#' + contentId).hide();
                    $('#' + contentId + '-btn').show();
                    $('#' + id).empty();
                    $('#' + id).qrcode($('#' + contentId).html());
                })

                $('.delete-order-btn').click(function(){
                    var id = $(this).data('id'); //订单ID
                    layer.confirm('确定要删除这个订单吗？', {
                        btn: ['按钮一', '按钮二', '按钮三'] //可以无限个按钮
                        ,btn3: function(index, layero){
                            //按钮【按钮三】的回调
                        }
                    }, function(index, layero){
                        //按钮【按钮一】的回调
                    }, function(index){
                        //按钮【按钮二】的回调
                    });
                })



                $('.template-nav-modal').removeAttr('tabindex');
                var clipboard = new Clipboard(".btn-copy", {
                    text: function (trigger) {
                        var id = $(trigger).data("id");
                        // console.log(id)
                        return $('#' + id).html();
                    }
                });
                clipboard.on('success', function (e, a) {
                    Toastr.success("卡密已复制到剪贴板!");
                    e.clearSelection();
                });

                var clipboard2 = new Clipboard(".copy-all", {
                    text: function (trigger) {
                        var id = $(trigger).data("id");
                        // console.log(id)
                        return $('#copy-all-' + id).val();
                    }
                });
                clipboard2.on('success', function (e, a) {
                    Toastr.success("卡密已复制到剪贴板!");
                    e.clearSelection();
                });

            });
        },
        spread: function(){
            require(['../libs/clipboard/dist/clipboard.min', '../libs/jquery.qrcode/jquery.qrcode.min'], function (Clipboard) {


                var clipboard = new Clipboard("#btn-copy", {
                    text: function (trigger) {
                        var link = $('#link').val();
                        return link;
                    }
                });
                clipboard.on('success', function (e, a) {
                    Toastr.success("邀请链接已复制到剪贴板!");
                    e.clearSelection();
                });
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
