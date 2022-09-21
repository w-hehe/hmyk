$('#c-category').change(function(){
    var category = $(this).val();
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
                options += `<option value="${i}">${e.data[i].goodsname}</option>`;
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
    // console.log(goodslist[i]);
    var goods_info = goodslist[i];

    goods_info.ys_dock_data = {
        "name": goods_info.goodsname, //商品名称
        "buy_default": goods_info.buyminnum, //默认下单数量
        "price": goods_info.goodsprice, //商品价格
        "images": goods_info.imgurl, //商品图片
        "details": goods_info.details, //商品介绍
        "remote_id": goods_info.goodsid, //商品ID
        "stock": goods_info.stock, //库存
        "inputs": [],
    };

    if(goods_info.hasOwnProperty('attach')){
        for(var i = 0; i < goods_info.attach.length; i++){
            goods_info.ys_dock_data.inputs.push({
                "name": i,
                "title": goods_info.attach[i].title,
                "placeholder": goods_info.attach[i].tip,
            });
        }
    }





    delete goods_info.details;
    delete goods_info.goodsname;
    delete goods_info.groupimgurl;
    delete goods_info.imgurl;

    $('#goods-info').val(JSON.stringify(goods_info));
})
