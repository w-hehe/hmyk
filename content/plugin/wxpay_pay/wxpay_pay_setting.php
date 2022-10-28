<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">APPID</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-appid" class="form-control" name="row[appid]" type="text" value="{$info.appid|default=''|htmlentities}">

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">Secret</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-secret" class="form-control" name="row[secret]" type="text" value="{$info.secret|default=''|htmlentities}">

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">商户号</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-mchid" class="form-control" name="row[mchid]" type="text" value="{$info.mchid|default=''|htmlentities}">

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">APIV3密钥</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-aesKey" class="form-control" name="row[aesKey]" type="text" value="{$info.aesKey|default=''|htmlentities}">

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">证书序列号</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-serial_no" class="form-control" name="row[serial_no]" type="text" value="{$info.serial_no|default=''|htmlentities}">

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('证书')}</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-cert" class="form-control" rows="6" name="row[cert]">{$info.cert|default=''|htmlentities}</textarea>
        </div>
    </div>



    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('私钥')}</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-private_key" class="form-control" rows="6" name="row[private_key]">{$info.private_key|default=''|htmlentities}</textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('开启支付')}:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="checkbox">
                <label><input name="row[pay_type][native]" type="checkbox" value="wxpay" {if condition="isset($info.pay_type.native)"}checked{/if}>Native扫码</label>
                <label><input name="row[pay_type][jsapi]" type="checkbox" value="wxpay" {if condition="isset($info.pay_type.jsapi)"}checked{/if}>JsApi公众号</label>
                <label><input name="row[pay_type][h5]" type="checkbox" value="wxpay" {if condition="isset($info.pay_type.h5)"}checked{/if}>H5手机浏览器</label>
            </div>
        </div>
    </div>

    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled">{:__('OK')}</button>
            <button type="reset" class="btn btn-default btn-embossed">{:__('Reset')}</button>
        </div>
    </div>
</form>
