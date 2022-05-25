define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload'], function ($, undefined, Backend, Table, Form, Upload) {

    var Controller = {
        index: function () {

            // 初始化表格参数配置
            Table.api.init({
                search: true,
                advancedSearch: true,
                pagination: true,
                extend: {
                    "index_url": "general/profile/index",
                    "add_url": "",
                    "edit_url": "",
                    "del_url": "",
                    "multi_url": "",
                }
            });



            // 给上传按钮添加上传成功事件
            $("#faupload-avatar").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                Toastr.success("上传成功！");
            });

            // 给表单绑定事件
            Form.api.bindevent($("#update-form"), function () {
                $("input[name='row[password]']").val('');
                var url = Backend.api.cdnurl($("#c-avatar").val());
                top.window.$(".user-panel .image img,.user-menu > a > img,.user-header > img").prop("src", url);
                return true;
            });
        },
    };
    return Controller;
});
