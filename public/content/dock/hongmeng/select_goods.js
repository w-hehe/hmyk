$('#c-category').change(function(){
    var category = $(this).val();
    if(category == 0){
        $('#c-goods').empty()
        $('#c-goods').selectpicker('refresh')
        return;
    }
    var dock_id = $('#dock-id').val();
    Layer.load();
    $.get("goods/dockselectgoods/func/goodsList", {"dock_id":dock_id, "category":category}, function(e){
        layer.closeAll();
        if(e.code == 400){
            Toastr.error(e.msg);
        }else if(e.code == 200){
            goodslist = e.data;
            var options = "";
            for(var i = 0; i < e.data.length; i++){
                options += `<option value="${i}">${e.data[i].goods_name}</option>`;
            }
            $('#c-goods').empty();
            $('#c-goods').append(options)
            $('#c-goods').selectpicker('refresh')
            $('#c-goods').change()
        }
    }, "json").error(function(){
        Layer.closeAll();
        Toastr.error('请求失败');
    });
})


$('#c-goods').change(function(){
    var i = $(this).val();

    var goods_info = goodslist[i];

    goods_info.ys_dock_data = {
        "name": goods_info.goods_name, //商品名称
        "buy_default": 1, //默认下单数量
        "price": goods_info.price, //商品价格
        "images": goods_info.images, //商品图片
        "details": goods_info.details, //商品介绍
        "remote_id": goods_info.goods_id, //商品ID
        "stock": goods_info.stock, //库存
        "inputs": [],
    };
    if(goods_info.inputs != ""){
        goods_info.inputs = JSON.parse(goods_info.inputs);
        console.log(goods_info.inputs)
        if(goods_info.hasOwnProperty('inputs')){
            for(let key in goods_info.inputs){
                goods_info.ys_dock_data.inputs.push({
                    "name": goods_info.inputs[key].name,
                    "title": goods_info.inputs[key].title,
                    "placeholder": goods_info.inputs[key].placeholder,
                });
            }
        }
    }

    goods_info.attach = JSON.parse(goods_info.attach);
    console.log(goods_info.attach);
    if(goods_info.hasOwnProperty('attach')){
        for(let key in goods_info.attach){
            goods_info.ys_dock_data.inputs.push({
                "name": key,
                "title": key,
                "placeholder": goods_info.attach[key],
            });
        }
    }




    delete goods_info.details;
    delete goods_info.goodsname;
    delete goods_info.groupimgurl;
    delete goods_info.imgurl;

    $('#goods-info').val(JSON.stringify(goods_info));
})
