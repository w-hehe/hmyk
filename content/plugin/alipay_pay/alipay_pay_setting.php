<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('APPID')}</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-app_id" class="form-control" name="row[app_id]" type="text" value="{$info.app_id|default=''|htmlentities}">

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('支付宝公钥')}</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-public_key" class="form-control" rows="6" name="row[public_key]">{$info.public_key|default=''|htmlentities}</textarea>
        </div>
    </div>



    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('应用私钥')}</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-private_key" class="form-control" rows="6" name="row[private_key]">{$info.private_key|default=''|htmlentities}</textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('开启支付')}:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="checkbox">
                <label><input name="row[pay_type][pc]" type="checkbox" value="alipay" {if condition="isset($info.pay_type.pc)"}checked{/if}>pc网站支付</label>
                <label><input name="row[pay_type][sm]" type="checkbox" value="alipay" {if condition="isset($info.pay_type.sm)"}checked{/if}>扫码当面付</label>
                <label><input name="row[pay_type][wap]" type="checkbox" value="alipay" {if condition="isset($info.pay_type.wap)"}checked{/if}>手机wap支付</label>
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
