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

            $('.pay-type').click(function(){
                $('.pay-type').removeClass('active');
                $(this).addClass('active');
            });

            $('#btn-recharge').click(function(){
                let form = $('#recharge-form').serializeArray();
                let formData = {};
                $.each(form, function (index, item) {
                    formData[item.name] = item.value;
                });
                $.post("/balance", formData, function(e){
                    console.log(e);
                    if(e.code == 200){
                        if(e.mode == 'form'){
                            location.href = "/submit.html?data=" + e.data;
                        }
                        if(e.mode == 'scan'){
                            location.href = "/scan.html?data=" + e.data;
                        }
                    }
                    if(e.code == 201){
                        Toastr.success('购买成功');
                        location.href = "/order.html";
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
            })

            Form.api.bindevent($(".balance-form"), function (data, ret) {
                location.reload();
            }, function (data) {
            });

        },

    };
    return Controller;
});
