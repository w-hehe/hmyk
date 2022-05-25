<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">应用:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="checkbox">
                <label><input name="row[apply][]" type="checkbox" value="pc" {if condition="$active_template.pc == 'default'"}checked{/if}>电脑</label>
                <label><input name="row[apply][]" type="checkbox" value="mobile" {if condition="$active_template.mobile == 'default'"}checked{/if}>手机</label>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('首页商品图')}:</label>
        <div class="col-xs-12 col-sm-8">
            {:build_radios('row[index_cover]', ['normal'=>'显示', 'hidden'=>'隐藏'], $info['index_cover'])}
        </div>
    </div>
    <div class="form-group">
        <label for="c-logo" class="control-label col-xs-12 col-sm-2">logo:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-logo" data-rule="" class="form-control" size="50" name="row[logo]" type="text" value="{$info.logo}">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-logo" class="btn btn-danger plupload" data-input-id="c-logo" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-logo"><i class="fa fa-upload"></i> 上传</button></span>
                    <span><button type="button" id="fachoose-logo" class="btn btn-primary fachoose" data-input-id="c-logo" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> 选择</button></span>
                </div>
                <span class="msg-box n-right" for="c-logo"></span>
            </div>
            <ul class="row list-inline plupload-preview" id="p-logo"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">公告:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-notice" class="form-control editor" name="row[notice]" style="height: 300px;">{$info.notice}</textarea>
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
