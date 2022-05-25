<div class="dock-config dock-config-hongmeng">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">网站域名</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-hongmeng_host" class="form-control" name="row[hongmeng_host]" type="text" placeholder="请输入红盟云卡程序的域名" value="<?php echo empty($info['hongmeng_host']) ? '' : $info['hongmeng_host'] ?>">
            <span class="input-notice">必须以http开头，斜线(/)结尾</span>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">商户ID</label>
        <div class="col-xs-12 col-sm-8">
            <input class="form-control" name="row[hongmeng_user_id]" type="text" value="<?php echo empty($info['hongmeng_user_id']) ? '' : $info['hongmeng_user_id'] ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">商户密钥</label>
        <div class="col-xs-12 col-sm-8">
            <input class="form-control" name="row[hongmeng_secret]" type="text" value="<?php echo empty($info['hongmeng_secret']) ? '' : $info['hongmeng_secret'] ?>">
        </div>
    </div>
</div>

