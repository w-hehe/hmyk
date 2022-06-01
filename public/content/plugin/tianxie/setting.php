<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">应用:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="checkbox">
                <label><input name="row[apply][]" type="checkbox" value="pc" {if condition="$active_template.pc == 'tianxie'"}checked{/if}>电脑</label>
                <label><input name="row[apply][]" type="checkbox" value="mobile" {if condition="$active_template.mobile == 'tianxie'"}checked{/if}>手机</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('首页商品图')}:</label>
        <div class="col-xs-12 col-sm-8">
            {:build_radios('row[index_cover]', ['normal'=>'显示', 'hidden'=>'隐藏'], $info['index_cover'])}
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
