<div class="dock-config dock-config-kakayun">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">网站域名</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-kakayun_host" class="form-control" name="row[kakayun_host]" type="text" placeholder="请输入卡卡云的域名" value="<?php echo empty($info['kakayun_host']) ? '' : $info['kakayun_host'] ?>">
            <span class="input-notice">必须以http开头，斜线(/)结尾</span>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">商户ID</label>
        <div class="col-xs-12 col-sm-8">
            <input class="form-control" name="row[kakayun_id]" type="text" value="<?php echo empty($info['kakayun_id']) ? '' : $info['kakayun_id'] ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">商户key</label>
        <div class="col-xs-12 col-sm-8">
            <input class="form-control" name="row[kakayun_key]" type="text" value="<?php echo empty($info['kakayun_key']) ? '' : $info['kakayun_key'] ?>">
        </div>
    </div>
</div>

