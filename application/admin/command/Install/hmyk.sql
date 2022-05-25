/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50644
Source Host           : localhost:3306
Source Database       : www_hmyk_com

Target Server Type    : MYSQL
Target Server Version : 50644
File Encoding         : 65001

Date: 2022-05-02 23:08:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for hm_admin
-- ----------------------------
DROP TABLE IF EXISTS `hm_admin`;
CREATE TABLE `hm_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '昵称',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '头像',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '电子邮箱',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '登录IP',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `token` varchar(59) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

-- ----------------------------
-- Records of hm_admin
-- ----------------------------
INSERT INTO `hm_admin` VALUES ('1', 'admin', 'admin', 'bad86cd3ee3d557e1682e02ff9215366', '66d928', '/assets/img/avatar.png', '10220739@qq.com', '0', '1651419992', '127.0.0.1', null, '1651419992', '26d3dfb6-0ba3-48b0-b5fc-63a54576d5c0', 'normal');

-- ----------------------------
-- Table structure for hm_attach
-- ----------------------------
DROP TABLE IF EXISTS `hm_attach`;
CREATE TABLE `hm_attach` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `value_json` varchar(1000) DEFAULT NULL COMMENT '内容',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='附加选项';

-- ----------------------------
-- Records of hm_attach
-- ----------------------------

-- ----------------------------
-- Table structure for hm_attachment
-- ----------------------------
DROP TABLE IF EXISTS `hm_attachment`;
CREATE TABLE `hm_attachment` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '高度',
  `imagetype` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filename` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '文件名称',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '透传数据',
  `createtime` int(10) DEFAULT NULL COMMENT '创建日期',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `uploadtime` int(10) DEFAULT NULL COMMENT '上传时间',
  `storage` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='附件表';

-- ----------------------------
-- Records of hm_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for hm_category
-- ----------------------------
DROP TABLE IF EXISTS `hm_category`;
CREATE TABLE `hm_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `flag` set('hot','index','recommend') COLLATE utf8mb4_unicode_ci DEFAULT '',
  `image` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '关键字',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '描述',
  `goods_sort` tinyint(1) DEFAULT '0' COMMENT '商品排序',
  `diyname` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '自定义名称',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `weigh` (`weigh`,`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='分类表';

-- ----------------------------
-- Records of hm_category
-- ----------------------------

-- ----------------------------
-- Table structure for hm_cdkey
-- ----------------------------
DROP TABLE IF EXISTS `hm_cdkey`;
CREATE TABLE `hm_cdkey` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` int(10) DEFAULT NULL COMMENT '关联商品',
  `type` varchar(255) DEFAULT NULL,
  `cdk` text COMMENT '内容',
  `num` int(10) DEFAULT '1' COMMENT '数量',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='库存卡密';

-- ----------------------------
-- Records of hm_cdkey
-- ----------------------------

-- ----------------------------
-- Table structure for hm_complain
-- ----------------------------
DROP TABLE IF EXISTS `hm_complain`;
CREATE TABLE `hm_complain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) DEFAULT '0' COMMENT '用户id',
  `complain_id` varchar(32) DEFAULT NULL,
  `out_trade_no` varchar(30) DEFAULT NULL COMMENT '订单号',
  `name` varchar(20) DEFAULT NULL COMMENT '称呼',
  `qq` int(11) DEFAULT NULL COMMENT '联系qq',
  `details` varchar(255) DEFAULT NULL COMMENT '投诉内容',
  `handle_result` varchar(255) DEFAULT NULL COMMENT '处理结果',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `create_time` int(10) DEFAULT NULL COMMENT '投诉十四间',
  `handel_time` int(10) DEFAULT NULL COMMENT '处理时间',
  `complete_time` int(10) DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投诉';

-- ----------------------------
-- Records of hm_complain
-- ----------------------------

-- ----------------------------
-- Table structure for hm_config
-- ----------------------------
DROP TABLE IF EXISTS `hm_config`;
CREATE TABLE `hm_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量名',
  `group` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '分组',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量标题',
  `tip` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '变量值',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '变量字典数据',
  `rule` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '扩展属性',
  `setting` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '配置',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='系统配置';

-- ----------------------------
-- Records of hm_config
-- ----------------------------
INSERT INTO `hm_config` VALUES ('1', 'shop_title', 'basic', '网站标题', '', 'string', '红盟云卡 - 基于PHP + MySQL打造的商城建站系统', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('2', 'shop_pet_name', 'basic', '网站名称', '', 'string', '红盟云卡', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('3', 'beian', 'basic', 'Beian', '苏ICP备15000000号-1', 'string', '', '', '', '', null);
INSERT INTO `hm_config` VALUES ('4', 'version', 'basic', '后台静态文件版本', '如果静态资源有变动请重新配置该值', 'string', '1648775325', '', 'required', '', null);
INSERT INTO `hm_config` VALUES ('5', 'min_cashout', 'money', '最低提现金额', '0则不限制金额', 'number', '0', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('6', 'max_cashout_num', 'money', '每日最多提现次数', '0则不限制次数', 'number', '3', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('7', 'cashout_charged', 'money', '提现手续费%', '按照百分比填写', 'number', '1', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('8', 'tourist_buy', 'basic', '游客购买', '', 'switch', '1', '{\"1\":\"开启\",\"0\":\"关闭\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('9', 'login', 'basic', '登录功能', '', 'switch', '1', '{\"1\":\"开启\",\"0\":\"关闭\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('10', 'register', 'basic', '注册功能', '', 'switch', '1', '{\"1\":\"开启\",\"0\":\"关闭\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('11', 'statistics', 'basic', '统计代码', '第三方流量统计代码', 'text', '', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('12', 'diy_name', 'buy', '商品自定义支付名称', '此选项可以替换官方支付接口的商品名称，留空使用原商品名称。', 'string', '商品购买', '', '', '', null);
INSERT INTO `hm_config` VALUES ('13', 'buy_account', 'buy', '下单账号', '开启后 用户下单时显示该输入框', 'radio', '0', '{\"1\":\"开启\",\"0\":\"关闭\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('14', 'buy_password', 'buy', '查单密码', '关闭后购买商品和查询订单时不需要填写密码', 'radio', '0', '{\"1\":\"开启\",\"0\":\"关闭\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('15', 'tip_account', 'buy', '下单账号提示', '购买商品处填写下单账号表单内的提示信息 非必填', 'string', '', '', '', '', null);
INSERT INTO `hm_config` VALUES ('16', 'tip_password', 'buy', '查单密码提示', '购买商品处填写查单密码表单内的提示内容 非必填', 'string', '游客查询订单时的凭证', '', '', '', null);
INSERT INTO `hm_config` VALUES ('18', 'eject_goods', 'eject', '商品页弹窗内容', '', 'editor', '', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('19', 'mail_smtp_host', 'email', 'SMTP服务器', '错误的配置发送邮件会导致服务器超时', 'string', 'smtp.qq.com', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('20', 'mail_verify_type', 'email', 'SMTP验证方式', '', 'select', '2', '{\"\":\"无\",\"1\":\"TLS\",\"2\":\"SSL\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('21', 'mail_smtp_port', 'email', 'SMTP端口', '(不加密默认25,SSL默认465,TLS默认587)', 'number', '465', null, '', '', null);
INSERT INTO `hm_config` VALUES ('22', 'mail_smtp_pass', 'email', 'SMTP授权码', '<a href=\"https://service.mail.qq.com/cgi-bin/help?subtype=1&&no=1001256&&id=28\" target=\"_blank\">什么是SMTP授权码？</a>', 'string', '', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('23', 'mail_smtp_user', 'email', '发件人名称', '', 'string', '红盟云卡', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('24', 'mail_from', 'email', '发件人邮箱', '', 'string', '', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('25', 'admin_order_email', 'email', '站长接收新订单邮件通知', '', 'switch', '0', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('26', 'user_order_email', 'email', '向用户发送订单信息邮件', '暂时只发送自动发货的商品订单', 'switch', '0', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('27', 'ico', 'other', '网站ico图标', '', 'image', '/assets/img/favicon.png', '', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');
INSERT INTO `hm_config` VALUES ('29', 'cdk_order', 'buy', '卡密发货顺序', '', 'select', 'asc', '{\"asc\":\"正序\",\"desc\":\"倒序\",\"random\":\"随机\"}', '', '', '{\"table\":\"\",\"conditions\":\"\",\"key\":\"\",\"value\":\"\"}');

-- ----------------------------
-- Table structure for hm_coupon
-- ----------------------------
DROP TABLE IF EXISTS `hm_coupon`;
CREATE TABLE `hm_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT '0',
  `discount` varchar(10) DEFAULT NULL,
  `single` tinyint(1) DEFAULT '0',
  `max_use` int(10) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `create_time` int(10) DEFAULT NULL,
  `expire_time` int(10) DEFAULT NULL,
  `apply` tinyint(1) DEFAULT '0',
  `category_ids` varchar(255) DEFAULT NULL,
  `goods_ids` varchar(500) DEFAULT NULL,
  `use_num` int(10) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='优惠券';

-- ----------------------------
-- Records of hm_coupon
-- ----------------------------

-- ----------------------------
-- Table structure for hm_coupon_log
-- ----------------------------
DROP TABLE IF EXISTS `hm_coupon_log`;
CREATE TABLE `hm_coupon_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(10) DEFAULT NULL,
  `order_id` int(10) DEFAULT NULL,
  `uid` int(10) DEFAULT '0',
  `create_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='优惠券使用记录';

-- ----------------------------
-- Records of hm_coupon_log
-- ----------------------------

-- ----------------------------
-- Table structure for hm_dock
-- ----------------------------
DROP TABLE IF EXISTS `hm_dock`;
CREATE TABLE `hm_dock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL COMMENT '类型',
  `info` text COMMENT '对接网站所需信息',
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of hm_dock
-- ----------------------------

-- ----------------------------
-- Table structure for hm_goods
-- ----------------------------
DROP TABLE IF EXISTS `hm_goods`;
CREATE TABLE `hm_goods` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `dock_id` int(10) DEFAULT '0' COMMENT '对接站点 ID',
  `dock_data` text COMMENT '对接商品数据',
  `remote_id` int(10) DEFAULT '0' COMMENT '对接商品 ID',
  `inputs` varchar(500) DEFAULT NULL COMMENT '对接站附加表单',
  `category_id` int(10) DEFAULT '0' COMMENT '商品分类 ID',
  `attach_id` int(10) DEFAULT '0' COMMENT '附加选项 ID',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `price` decimal(10,2) DEFAULT NULL COMMENT '出售价格',
  `buy_price` decimal(10,2) DEFAULT '0.00' COMMENT '进货价格',
  `increase_id` int(10) NOT NULL DEFAULT '0' COMMENT '加价模板 ID',
  `buy_min` int(10) NOT NULL DEFAULT '1' COMMENT '最小购买',
  `buy_max` int(10) NOT NULL DEFAULT '0' COMMENT '0为不限制',
  `buy_default` int(10) NOT NULL DEFAULT '0' COMMENT '在对接站默认下单数量',
  `quota` int(10) DEFAULT '0' COMMENT '单IP限购数量',
  `sales` int(11) DEFAULT '0' COMMENT '销量',
  `sales_money` decimal(10,2) DEFAULT '0.00' COMMENT '销售额',
  `stock` int(10) DEFAULT NULL COMMENT '库存',
  `images` varchar(255) DEFAULT NULL COMMENT '封面图',
  `details` text COMMENT '详细内容',
  `eject` text COMMENT '弹窗内容',
  `shelf` tinyint(1) DEFAULT '0' COMMENT '是否下架',
  `goods_type` varchar(30) DEFAULT '0' COMMENT '0 卡密 1 激活码 2账号和密码 3图片 4其他 ds代刷',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `buy_msg` text COMMENT '购买后提示内容',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '修改时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of hm_goods
-- ----------------------------

-- ----------------------------
-- Table structure for hm_increase
-- ----------------------------
DROP TABLE IF EXISTS `hm_increase`;
CREATE TABLE `hm_increase` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `type` varchar(255) DEFAULT NULL COMMENT '加价方式',
  `value` varchar(255) DEFAULT NULL COMMENT '加价',
  `effect` varchar(255) DEFAULT NULL COMMENT '模板生效场景 1超过 2变动',
  `expire` int(10) DEFAULT '0' COMMENT '价格检测过期时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='加价模板';

-- ----------------------------
-- Records of hm_increase
-- ----------------------------

-- ----------------------------
-- Table structure for hm_information
-- ----------------------------
DROP TABLE IF EXISTS `hm_information`;
CREATE TABLE `hm_information` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `cover` varchar(255) DEFAULT NULL COMMENT '封面',
  `content` text COMMENT '内容',
  `views` int(11) DEFAULT '0',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of hm_information
-- ----------------------------

-- ----------------------------
-- Table structure for hm_money_bill
-- ----------------------------
DROP TABLE IF EXISTS `hm_money_bill`;
CREATE TABLE `hm_money_bill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `createtime` int(10) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL COMMENT '类型',
  `money` decimal(10,2) DEFAULT '0.00',
  `actual` decimal(10,2) DEFAULT NULL,
  `charged` int(10) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `handletime` int(10) DEFAULT NULL,
  `pay_type` varchar(255) DEFAULT NULL,
  `order_no` varchar(35) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='余额账单';

-- ----------------------------
-- Records of hm_money_bill
-- ----------------------------

-- ----------------------------
-- Table structure for hm_notice
-- ----------------------------
DROP TABLE IF EXISTS `hm_notice`;
CREATE TABLE `hm_notice` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) DEFAULT '0' COMMENT '用户ID',
  `type` int(10) DEFAULT '0' COMMENT '消息类型',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `status` tinyint(1) DEFAULT '0',
  `createtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of hm_notice
-- ----------------------------

-- ----------------------------
-- Table structure for hm_options
-- ----------------------------
DROP TABLE IF EXISTS `hm_options`;
CREATE TABLE `hm_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `option_name` varchar(255) DEFAULT '0',
  `option_content` text,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of hm_options
-- ----------------------------
INSERT INTO `hm_options` VALUES ('1', 'version', '2.0.5');
INSERT INTO `hm_options` VALUES ('2', 'user_total', '2');
INSERT INTO `hm_options` VALUES ('3', 'order_total', '0');
INSERT INTO `hm_options` VALUES ('4', 'money_total', '0');
INSERT INTO `hm_options` VALUES ('5', 'goods_total', '0');
INSERT INTO `hm_options` VALUES ('6', 'active_plugin', 'a:0:{}');
INSERT INTO `hm_options` VALUES ('7', 'active_template', 'a:2:{s:2:\"pc\";s:7:\"default\";s:6:\"mobile\";s:7:\"default\";}');
INSERT INTO `hm_options` VALUES ('8', 'stock_show', '[{\"less\":\"-1\",\"greater\":\"1\",\"content\":\"\\u552e\\u78d0\"},{\"less\":\"0\",\"greater\":\"10\",\"content\":\"\\u5c11\\u91cf\"},{\"less\":\"10\",\"greater\":\"1000000\",\"content\":\"\\u5145\\u8db3\"}]');
INSERT INTO `hm_options` VALUES ('9', 'stock_show_switch', '0');
INSERT INTO `hm_options` VALUES ('10', 'active_pay', '[]');
INSERT INTO `hm_options` VALUES ('11', 'coupon', '0');

-- ----------------------------
-- Table structure for hm_order
-- ----------------------------
DROP TABLE IF EXISTS `hm_order`;
CREATE TABLE `hm_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `ip` varchar(15) DEFAULT NULL COMMENT '客户端IP',
  `station_id` int(10) DEFAULT '0',
  `order_no` varchar(30) DEFAULT NULL,
  `remote_order_no` varchar(30) DEFAULT NULL,
  `uid` varchar(20) DEFAULT '0' COMMENT '用户id',
  `attach` varchar(800) DEFAULT NULL COMMENT '附件内容',
  `inputs` varchar(500) DEFAULT NULL COMMENT '对接订单所需的数据',
  `goods_id` int(10) DEFAULT '0' COMMENT '商品id',
  `buy_num` int(10) DEFAULT '0' COMMENT '购买数量',
  `goods_money` decimal(10,2) DEFAULT '0.00' COMMENT '商品单价',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '订单金额',
  `remote_money` decimal(10,2) DEFAULT '0.00' COMMENT '进货价格',
  `pay_type` varchar(255) DEFAULT NULL COMMENT '支付方式',
  `pay_plugin` varchar(255) DEFAULT NULL COMMENT '支付插件',
  `status` varchar(20) DEFAULT 'wait-pay' COMMENT '订单状态 1代发货 2待收货 9已完成',
  `dock_status` varchar(255) DEFAULT NULL COMMENT '状态',
  `dock_explain` varchar(255) DEFAULT NULL COMMENT '说明',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `pay_time` int(10) DEFAULT '0' COMMENT '支付时间',
  `qr_code` varchar(255) DEFAULT NULL,
  `account` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of hm_order
-- ----------------------------

-- ----------------------------
-- Table structure for hm_price
-- ----------------------------
DROP TABLE IF EXISTS `hm_price`;
CREATE TABLE `hm_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) DEFAULT NULL,
  `grade_id` int(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品价格表';

-- ----------------------------
-- Records of hm_price
-- ----------------------------

-- ----------------------------
-- Table structure for hm_recharge
-- ----------------------------
DROP TABLE IF EXISTS `hm_recharge`;
CREATE TABLE `hm_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT '0',
  `out_trade_no` varchar(32) DEFAULT NULL,
  `remote_trade_no` varchar(50) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT '0.00',
  `create_time` int(10) DEFAULT NULL,
  `pay_time` int(10) DEFAULT NULL,
  `pay_type` varchar(15) DEFAULT NULL,
  `pay_plugin` varchar(30) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值订单';

-- ----------------------------
-- Records of hm_recharge
-- ----------------------------

-- ----------------------------
-- Table structure for hm_shop_station
-- ----------------------------
DROP TABLE IF EXISTS `hm_shop_station`;
CREATE TABLE `hm_shop_station` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) DEFAULT '0' COMMENT '用户ID',
  `pid` int(10) DEFAULT '0' COMMENT '上级ID',
  `son_num` int(10) DEFAULT '0' COMMENT '子站数量',
  `domain` varchar(255) DEFAULT NULL COMMENT '域名',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  `deletetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商城子站';

-- ----------------------------
-- Records of hm_shop_station
-- ----------------------------

-- ----------------------------
-- Table structure for hm_sold
-- ----------------------------
DROP TABLE IF EXISTS `hm_sold`;
CREATE TABLE `hm_sold` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(10) NOT NULL COMMENT '关联订单',
  `content` text COMMENT '发货内容',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='已售卡密';

-- ----------------------------
-- Records of hm_sold
-- ----------------------------

-- ----------------------------
-- Table structure for hm_test
-- ----------------------------
DROP TABLE IF EXISTS `hm_test`;
CREATE TABLE `hm_test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `createtime` varchar(30) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of hm_test
-- ----------------------------

-- ----------------------------
-- Table structure for hm_user
-- ----------------------------
DROP TABLE IF EXISTS `hm_user`;
CREATE TABLE `hm_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `consume` decimal(10,2) DEFAULT '0.00' COMMENT '消费金额',
  `tourist` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '游客标识',
  `secret` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密钥',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '昵称',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '头像',
  `agent` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '代理',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `score` int(10) NOT NULL DEFAULT '0' COMMENT '积分',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `email` (`email`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员表';

-- ----------------------------
-- Records of hm_user
-- ----------------------------

-- ----------------------------
-- Table structure for hm_user_grade
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_grade`;
CREATE TABLE `hm_user_grade` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='代理等级';

-- ----------------------------
-- Records of hm_user_grade
-- ----------------------------
INSERT INTO `hm_user_grade` VALUES ('1', '普通', '10.00', '1648176573');
INSERT INTO `hm_user_grade` VALUES ('2', '精英', '20.00', '1648256768');
INSERT INTO `hm_user_grade` VALUES ('3', '至尊', '35.00', '1648260107');
INSERT INTO `hm_user_grade` VALUES ('4', '合作商', '50.00', '1648260104');
