<form id="setting-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">


    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('HTML代码')}</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-html" class="form-control" rows="6" name="row[html]">{$info.html|default=''|htmlentities}</textarea>
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
