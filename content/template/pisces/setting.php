<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">应用</label>
        <div class="col-xs-12 col-sm-8">
            <div class="checkbox">
                <label><input name="row[apply][]" type="checkbox" value="pc" {if condition="$active_template.pc == 'pisces'"}checked{/if}>电脑</label>
                <label><input name="row[apply][]" type="checkbox" value="mobile" {if condition="$active_template.mobile == 'pisces'"}checked{/if}>手机</label>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">首页风格</label>
        <div class="col-xs-12 col-sm-8">
            {:build_radios('row[home_style]', ['card'=>'卡片', 'table'=>'表格'], empty($info.home_style) ? 'card' : $info.home_style)}
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">首页分类</label>
        <div class="col-xs-12 col-sm-8">
            {:build_radios('row[home_category]', ['normal'=>'显示', 'hidden'=>'隐藏'], empty($info.home_category) ? 'normal' : $info.home_category)}
        </div>
    </div>
    <div class="form-group">
        <label for="c-logo" class="control-label col-xs-12 col-sm-2">Logo</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-logo" data-rule="" class="form-control" size="50" name="row[logo]" type="text" value="{$info.logo}">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-logo" class="btn btn-danger plupload" data-input-id="c-logo" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,svg" data-multiple="false" data-preview-id="p-logo"><i class="fa fa-upload"></i> 上传</button></span>
                    <span><button type="button" id="fachoose-logo" class="btn btn-primary fachoose" data-input-id="c-logo" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> 选择</button></span>
                </div>
                <span class="msg-box n-right" for="c-logo"></span>
            </div>
            <ul class="row list-inline plupload-preview" id="p-logo"></ul>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">隐藏封面图</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-hidden_cover" name="row[hidden_cover]" type="hidden" value="{$info.hidden_cover|default=0}">
            <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-hidden_cover" data-yes="1" data-no="0" >
                <i class="fa fa-toggle-on text-success {:empty($info.hidden_cover) ? 'fa-flip-horizontal text-gray' : ''} fa-2x"></i>
            </a>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">销量显示</label>
        <div class="col-xs-12 col-sm-8">
            <input  id="c-sales" name="row[sales]" type="hidden" value="{$info.sales|default=0}">
            <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-sales" data-yes="1" data-no="0" >
                <i class="fa fa-toggle-on text-success {:empty($info.sales) ? 'fa-flip-horizontal text-gray' : ''} fa-2x"></i>
            </a>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">库存显示</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-stock" name="row[stock]" type="hidden" value="{$info.stock|default=0}">
            <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-stock" data-yes="1" data-no="0" >
                <i class="fa fa-toggle-on text-success {:empty($info.stock) ? 'fa-flip-horizontal text-gray' : ''} fa-2x"></i>
            </a>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">公告</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-notice" class="form-control editor" name="row[notice]" style="height: 300px;">{$info.notice|default=''}</textarea>
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
