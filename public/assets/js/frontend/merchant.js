define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {

        index: function () {
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

    };
    return Controller;
});
