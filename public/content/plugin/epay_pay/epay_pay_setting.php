<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

<!--    <div class="alert alert-dismissable alert-info-light" style="line-height: 100%; text-align: center;">-->
<!--    </div>-->
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('接口地址')}</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-gateway_url" class="form-control" name="row[gateway_url]" type="text" value="{$info.gateway_url|default=''|htmlentities}">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('商户ID')}</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-appid" class="form-control" name="row[appid]" type="text" value="{$info.appid|default=''|htmlentities}">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('商户密钥')}</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-secret_key" class="form-control" name="row[secret_key]" type="text" value="{$info.secret_key|default=''|htmlentities}">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('开启支付')}:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="checkbox">
                <label><input name="row[pay_type][]" type="checkbox" value="alipay" {if condition="in_array('alipay', $info.pay_type)"}checked{/if}>支付宝</label>
                <label><input name="row[pay_type][]" type="checkbox" value="wxpay" {if condition="in_array('wxpay', $info.pay_type)"}checked{/if}>微信</label>
                <label><input name="row[pay_type][]" type="checkbox" value="qqpay" {if condition="in_array('qqpay', $info.pay_type)"}checked{/if}>QQ</label>
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
