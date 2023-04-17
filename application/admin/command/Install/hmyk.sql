/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50726 (5.7.26)
 Source Host           : localhost:3306
 Source Schema         : hm_test_com

 Target Server Type    : MySQL
 Target Server Version : 50726 (5.7.26)
 File Encoding         : 65001

 Date: 17/04/2023 23:33:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for hm_admin
-- ----------------------------
DROP TABLE IF EXISTS `hm_admin`;
CREATE TABLE `hm_admin`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '手机号码',
  `loginfailure` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '失败次数',
  `logintime` bigint(16) NULL DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '登录IP',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `token` varchar(59) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_admin
-- ----------------------------
INSERT INTO `hm_admin` VALUES (1, 'admin', 'Admin', 'b01ac0e226d8377c3f02f933ca3d2f73', 'ab900d', 'http://hm.test.com/assets/img/avatar.png', 'admin@admin.com', '', 0, 1681717960, '127.0.0.1', 1491635035, 1681717960, 'b77a131f-9af7-4363-a661-9109c2c0c36a', 'normal');

-- ----------------------------
-- Table structure for hm_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `hm_admin_log`;
CREATE TABLE `hm_admin_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '日志标题',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员日志表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_admin_log
-- ----------------------------
INSERT INTO `hm_admin_log` VALUES (1, 1, 'admin', '/admin/plugin/myplugin/status/cmd/enable', '', '{\"action\":\"\",\"ids\":\"sheshou\",\"params\":\"\",\"cmd\":\"enable\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36', 1681745582);
INSERT INTO `hm_admin_log` VALUES (2, 1, 'admin', '/admin/plugin/myplugin/delp/plugin_name/sheshou/ids/3', '', '{\"plugin_name\":\"sheshou\",\"ids\":\"3\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36', 1681745584);

-- ----------------------------
-- Table structure for hm_area
-- ----------------------------
DROP TABLE IF EXISTS `hm_area`;
CREATE TABLE `hm_area`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(10) NULL DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '简称',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '全称',
  `level` tinyint(4) NULL DEFAULT NULL COMMENT '层级:1=省,2=市,3=区/县',
  `pinyin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '长途区号',
  `zip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '首字母',
  `lng` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '经度',
  `lat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '地区表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_area
-- ----------------------------

-- ----------------------------
-- Table structure for hm_attachment
-- ----------------------------
DROP TABLE IF EXISTS `hm_attachment`;
CREATE TABLE `hm_attachment`  (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '类别',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '高度',
  `imagetype` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片帧数',
  `filename` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件名称',
  `filesize` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `mimetype` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '透传数据',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建日期',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `uploadtime` bigint(16) NULL DEFAULT NULL COMMENT '上传时间',
  `storage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '附件表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for hm_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `hm_auth_group`;
CREATE TABLE `hm_auth_group`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父组别',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '组名',
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则ID',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分组表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_auth_group
-- ----------------------------
INSERT INTO `hm_auth_group` VALUES (1, 0, 'Admin group', '*', 1491635035, 1491635035, 'normal');
INSERT INTO `hm_auth_group` VALUES (2, 1, 'Second group', '13,14,16,15,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,1,9,10,11,7,6,8,2,4,5', 1491635035, 1491635035, 'normal');
INSERT INTO `hm_auth_group` VALUES (3, 2, 'Third group', '1,4,9,10,11,13,14,15,16,17,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,5', 1491635035, 1491635035, 'normal');
INSERT INTO `hm_auth_group` VALUES (4, 1, 'Second group 2', '1,4,13,14,15,16,17,55,56,57,58,59,60,61,62,63,64,65', 1491635035, 1491635035, 'normal');
INSERT INTO `hm_auth_group` VALUES (5, 2, 'Third group 2', '1,2,6,7,8,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34', 1491635035, 1491635035, 'normal');

-- ----------------------------
-- Table structure for hm_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `hm_auth_group_access`;
CREATE TABLE `hm_auth_group_access`  (
  `uid` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `group_id` int(10) UNSIGNED NOT NULL COMMENT '级别ID',
  UNIQUE INDEX `uid_group_id`(`uid`, `group_id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '权限分组表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_auth_group_access
-- ----------------------------
INSERT INTO `hm_auth_group_access` VALUES (1, 1);

-- ----------------------------
-- Table structure for hm_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `hm_auth_rule`;
CREATE TABLE `hm_auth_rule`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('menu','file') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图标',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '规则URL',
  `condition` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为菜单',
  `menutype` enum('addtabs','blank','dialog','ajax') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '菜单类型',
  `extend` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '扩展属性',
  `py` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '拼音首字母',
  `pinyin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '拼音',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE,
  INDEX `weigh`(`weigh`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 115 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '节点表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_auth_rule
-- ----------------------------
INSERT INTO `hm_auth_rule` VALUES (1, 'file', 0, 'dashboard', 'Dashboard', 'fa fa-dashboard', '', '', 'Dashboard tips', 1, NULL, '', 'kzt', 'kongzhitai', 1491635035, 1491635035, 143, 'normal');
INSERT INTO `hm_auth_rule` VALUES (3, 'file', 92, 'category', '分类管理', 'fa fa-leaf', '', '', '分类类型请在常规管理->系统配置->字典配置中添加', 1, 'addtabs', '', 'flgl', 'fenleiguanli', 1491635035, 1678966363, 119, 'normal');
INSERT INTO `hm_auth_rule` VALUES (4, 'file', 0, 'addon', '插件管理', 'fa fa-rocket', '', '', '可在线安装、卸载、禁用、启用、配置、升级插件，插件升级前请做好备份。', 1, 'addtabs', '', 'cjgl', 'chajianguanli', 1491635035, 1680888144, 0, 'hidden');
INSERT INTO `hm_auth_rule` VALUES (6, 'file', 92, 'general/config', '系统配置', 'fa fa-cog', '', '', '可以在此增改系统的变量和分组,也可以自定义分组和变量', 1, 'addtabs', '', 'xtpz', 'xitongpeizhi', 1491635035, 1678966495, 60, 'normal');
INSERT INTO `hm_auth_rule` VALUES (7, 'file', 91, 'general/attachment', '附件管理', 'fa fa-file-image-o', '', '', '主要用于管理上传到服务器或第三方存储的数据', 1, 'addtabs', '', 'fjgl', 'fujianguanli', 1491635035, 1678966488, 34, 'normal');
INSERT INTO `hm_auth_rule` VALUES (8, 'file', 91, 'general/profile', '个人资料', 'fa fa-user', '', '', '', 1, 'addtabs', '', 'grzl', 'gerenziliao', 1491635035, 1678966337, 53, 'normal');
INSERT INTO `hm_auth_rule` VALUES (9, 'file', 93, 'auth/admin', '管理员', 'fa fa-user', '', '', '一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成', 1, 'addtabs', '', 'gly', 'guanliyuan', 1491635035, 1678966874, 113, 'normal');
INSERT INTO `hm_auth_rule` VALUES (10, 'file', 93, 'auth/adminlog', '操作日志', 'fa fa-list-alt', '', '', '管理员可以查看自己所拥有的权限的管理员日志', 1, 'addtabs', '', 'czrz', 'caozuorizhi', 1491635035, 1678966886, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (11, 'file', 93, 'auth/group', '权限组', 'fa fa-group', '', '', '角色组可以有多个,角色有上下级层级关系,如果子角色有角色组和管理员的权限则可以派生属于自己组别的下级角色组或管理员', 1, 'addtabs', '', 'qxz', 'quanxianzu', 1491635035, 1678966870, 109, 'normal');
INSERT INTO `hm_auth_rule` VALUES (12, 'file', 92, 'auth/rule', '菜单规则', 'fa fa-bars', '', '', '菜单规则通常对应一个控制器的方法,同时菜单栏数据也从规则中获取', 1, 'addtabs', '', 'cdgz', 'caidanguize', 1491635035, 1678966291, 104, 'normal');
INSERT INTO `hm_auth_rule` VALUES (13, 'file', 1, 'dashboard/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 136, 'normal');
INSERT INTO `hm_auth_rule` VALUES (14, 'file', 1, 'dashboard/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 135, 'normal');
INSERT INTO `hm_auth_rule` VALUES (15, 'file', 1, 'dashboard/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 133, 'normal');
INSERT INTO `hm_auth_rule` VALUES (16, 'file', 1, 'dashboard/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 134, 'normal');
INSERT INTO `hm_auth_rule` VALUES (17, 'file', 1, 'dashboard/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 132, 'normal');
INSERT INTO `hm_auth_rule` VALUES (18, 'file', 6, 'general/config/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 52, 'normal');
INSERT INTO `hm_auth_rule` VALUES (19, 'file', 6, 'general/config/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 51, 'normal');
INSERT INTO `hm_auth_rule` VALUES (20, 'file', 6, 'general/config/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 50, 'normal');
INSERT INTO `hm_auth_rule` VALUES (21, 'file', 6, 'general/config/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 49, 'normal');
INSERT INTO `hm_auth_rule` VALUES (22, 'file', 6, 'general/config/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 48, 'normal');
INSERT INTO `hm_auth_rule` VALUES (23, 'file', 7, 'general/attachment/index', 'View', 'fa fa-circle-o', '', '', 'Attachment tips', 0, NULL, '', '', '', 1491635035, 1491635035, 59, 'normal');
INSERT INTO `hm_auth_rule` VALUES (24, 'file', 7, 'general/attachment/select', 'Select attachment', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 58, 'normal');
INSERT INTO `hm_auth_rule` VALUES (25, 'file', 7, 'general/attachment/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 57, 'normal');
INSERT INTO `hm_auth_rule` VALUES (26, 'file', 7, 'general/attachment/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 56, 'normal');
INSERT INTO `hm_auth_rule` VALUES (27, 'file', 7, 'general/attachment/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 55, 'normal');
INSERT INTO `hm_auth_rule` VALUES (28, 'file', 7, 'general/attachment/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 54, 'normal');
INSERT INTO `hm_auth_rule` VALUES (29, 'file', 8, 'general/profile/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 33, 'normal');
INSERT INTO `hm_auth_rule` VALUES (30, 'file', 8, 'general/profile/update', 'Update profile', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 32, 'normal');
INSERT INTO `hm_auth_rule` VALUES (31, 'file', 8, 'general/profile/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 31, 'normal');
INSERT INTO `hm_auth_rule` VALUES (32, 'file', 8, 'general/profile/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 30, 'normal');
INSERT INTO `hm_auth_rule` VALUES (33, 'file', 8, 'general/profile/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 29, 'normal');
INSERT INTO `hm_auth_rule` VALUES (34, 'file', 8, 'general/profile/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 28, 'normal');
INSERT INTO `hm_auth_rule` VALUES (35, 'file', 3, 'category/index', 'View', 'fa fa-circle-o', '', '', 'Category tips', 0, NULL, '', '', '', 1491635035, 1491635035, 142, 'normal');
INSERT INTO `hm_auth_rule` VALUES (36, 'file', 3, 'category/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 141, 'normal');
INSERT INTO `hm_auth_rule` VALUES (37, 'file', 3, 'category/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 140, 'normal');
INSERT INTO `hm_auth_rule` VALUES (38, 'file', 3, 'category/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 139, 'normal');
INSERT INTO `hm_auth_rule` VALUES (39, 'file', 3, 'category/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 138, 'normal');
INSERT INTO `hm_auth_rule` VALUES (40, 'file', 9, 'auth/admin/index', 'View', 'fa fa-circle-o', '', '', 'Admin tips', 0, NULL, '', '', '', 1491635035, 1491635035, 117, 'normal');
INSERT INTO `hm_auth_rule` VALUES (41, 'file', 9, 'auth/admin/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 116, 'normal');
INSERT INTO `hm_auth_rule` VALUES (42, 'file', 9, 'auth/admin/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 115, 'normal');
INSERT INTO `hm_auth_rule` VALUES (43, 'file', 9, 'auth/admin/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 114, 'normal');
INSERT INTO `hm_auth_rule` VALUES (44, 'file', 10, 'auth/adminlog/index', 'View', 'fa fa-circle-o', '', '', 'Admin log tips', 0, NULL, '', '', '', 1491635035, 1491635035, 112, 'normal');
INSERT INTO `hm_auth_rule` VALUES (45, 'file', 10, 'auth/adminlog/detail', 'Detail', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 111, 'normal');
INSERT INTO `hm_auth_rule` VALUES (46, 'file', 10, 'auth/adminlog/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 110, 'normal');
INSERT INTO `hm_auth_rule` VALUES (47, 'file', 11, 'auth/group/index', 'View', 'fa fa-circle-o', '', '', 'Group tips', 0, NULL, '', '', '', 1491635035, 1491635035, 108, 'normal');
INSERT INTO `hm_auth_rule` VALUES (48, 'file', 11, 'auth/group/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 107, 'normal');
INSERT INTO `hm_auth_rule` VALUES (49, 'file', 11, 'auth/group/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 106, 'normal');
INSERT INTO `hm_auth_rule` VALUES (50, 'file', 11, 'auth/group/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 105, 'normal');
INSERT INTO `hm_auth_rule` VALUES (51, 'file', 12, 'auth/rule/index', 'View', 'fa fa-circle-o', '', '', 'Rule tips', 0, NULL, '', '', '', 1491635035, 1491635035, 103, 'normal');
INSERT INTO `hm_auth_rule` VALUES (52, 'file', 12, 'auth/rule/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 102, 'normal');
INSERT INTO `hm_auth_rule` VALUES (53, 'file', 12, 'auth/rule/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 101, 'normal');
INSERT INTO `hm_auth_rule` VALUES (54, 'file', 12, 'auth/rule/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 100, 'normal');
INSERT INTO `hm_auth_rule` VALUES (55, 'file', 4, 'addon/index', 'View', 'fa fa-circle-o', '', '', 'Addon tips', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (56, 'file', 4, 'addon/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (57, 'file', 4, 'addon/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (58, 'file', 4, 'addon/del', 'Delete', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (59, 'file', 4, 'addon/downloaded', 'Local addon', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (60, 'file', 4, 'addon/state', 'Update state', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (63, 'file', 4, 'addon/config', 'Setting', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (64, 'file', 4, 'addon/refresh', 'Refresh', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (65, 'file', 4, 'addon/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (66, 'file', 0, 'user', '用户管理', 'fa fa-user', '', '', '', 1, 'addtabs', '', 'yhgl', 'yonghuguanli', 1491635035, 1678967123, 130, 'normal');
INSERT INTO `hm_auth_rule` VALUES (67, 'file', 66, 'user/user', '用户列表', 'fa fa-male', '', '', '', 1, 'addtabs', '', 'yhlb', 'yonghuliebiao', 1491635035, 1678974744, 118, 'normal');
INSERT INTO `hm_auth_rule` VALUES (68, 'file', 67, 'user/user/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (69, 'file', 67, 'user/user/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (70, 'file', 67, 'user/user/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (71, 'file', 67, 'user/user/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (72, 'file', 67, 'user/user/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (73, 'file', 92, 'user/group', '会员分组', 'fa fa-users', '', '', '', 1, 'addtabs', '', 'hyfz', 'huiyuanfenzu', 1491635035, 1678966398, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (74, 'file', 73, 'user/group/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (75, 'file', 73, 'user/group/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (76, 'file', 73, 'user/group/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (77, 'file', 73, 'user/group/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (78, 'file', 73, 'user/group/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (79, 'file', 92, 'user/rule', '会员规则', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'hygz', 'huiyuanguize', 1491635035, 1678966407, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (80, 'file', 79, 'user/rule/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (81, 'file', 79, 'user/rule/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (82, 'file', 79, 'user/rule/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (83, 'file', 79, 'user/rule/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (84, 'file', 79, 'user/rule/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', 1491635035, 1491635035, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (85, 'file', 0, 'goods', '商品管理', 'fa fa-shopping-cart', '', '', '', 1, 'addtabs', '', 'spgl', 'shangpinguanli', 1678965834, 1678967136, 120, 'normal');
INSERT INTO `hm_auth_rule` VALUES (86, 'file', 0, 'blog', '博客管理', 'fa fa-book', '', '', '', 1, 'addtabs', '', 'bkgl', 'bokeguanli', 1678965845, 1678967286, 110, 'normal');
INSERT INTO `hm_auth_rule` VALUES (87, 'file', 85, 'goods/category/index', '商品分类', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'spfl', 'shangpinfenlei', 1678965897, 1678965897, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (88, 'file', 85, 'goods/goods/index', '商品列表', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'splb', 'shangpinliebiao', 1678965921, 1678965921, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (89, 'file', 86, 'blog/category/index', '文章分类', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'wzfl', 'wenzhangfenlei', 1678965942, 1678965981, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (90, 'file', 86, 'blog/blog/index', '文章列表', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'wzlb', 'wenzhangliebiao', 1678965971, 1678965971, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (91, 'file', 0, 'website', '系统配置', 'fa fa-cogs', '', '', '', 1, 'addtabs', '', 'xtpz', 'xitongpeizhi', 1678966054, 1678966818, 142, 'normal');
INSERT INTO `hm_auth_rule` VALUES (92, 'file', 91, 'develop', '开发专用', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'kfzy', 'kaifazhuanyong', 1678966151, 1681297843, 0, 'hidden');
INSERT INTO `hm_auth_rule` VALUES (93, 'file', 0, 'admin', '管理员管理', 'fa fa-windows', '', '', '', 1, 'addtabs', '', 'glygl', 'guanliyuanguanli', 1678966732, 1678966756, 141, 'normal');
INSERT INTO `hm_auth_rule` VALUES (94, 'file', 0, 'finance', '财务管理', 'fa fa-bookmark', '', '', '', 1, 'addtabs', '', 'cwgl', 'caiwuguanli', 1678967013, 1678967275, 100, 'normal');
INSERT INTO `hm_auth_rule` VALUES (95, 'file', 94, 'finance/order/goods/index', '商品订单', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'spdd', 'shangpindingdan', 1678967186, 1679032272, 10, 'normal');
INSERT INTO `hm_auth_rule` VALUES (97, 'file', 66, 'user/agency/index', '代理等级', 'fa fa-trello', '', '', '', 1, 'addtabs', '', 'dldj', 'dailidengji', 1678967382, 1678974716, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (98, 'file', 0, 'merchant', '分站管理', 'fa fa-window-restore', '', '', '', 1, 'addtabs', '', 'fzgl', 'fenzhanguanli', 1678967436, 1680262374, 90, 'normal');
INSERT INTO `hm_auth_rule` VALUES (99, 'file', 98, 'merchant/merchant/index', '分站列表', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'fzlb', 'fenzhanliebiao', 1678967509, 1680262400, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (100, 'file', 98, 'merchant/grade/index', '分站等级', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'fzdj', 'fenzhandengji', 1678967573, 1680262425, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (101, 'file', 0, 'complain', '投诉反馈', 'fa fa-exclamation-circle', '', '', '', 1, 'addtabs', '', 'tsfk', 'tousufankui', 1678967927, 1678968114, 80, 'hidden');
INSERT INTO `hm_auth_rule` VALUES (102, 'file', 101, 'complain/complain/index', '投诉列表', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'tslb', 'tousuliebiao', 1678967959, 1678967959, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (103, 'file', 101, 'complain/feedback/index', '意见反馈', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'yjfk', 'yijianfankui', 1678967998, 1678967998, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (104, 'file', 0, 'plugin/market/index', '插件管理', 'fa fa-plug', '', '', '', 1, 'addtabs', '', 'cjgl', 'chajianguanli', 1678968191, 1680888128, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (107, 'file', 94, 'finance/order/recharge/index', '充值订单', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'czdd', 'chongzhidingdan', 1679032117, 1679032280, 8, 'normal');
INSERT INTO `hm_auth_rule` VALUES (108, 'file', 94, 'finance/order/agency/index', '升级代理', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'sjdl', 'shengjidaili', 1679035487, 1679035622, 6, 'normal');
INSERT INTO `hm_auth_rule` VALUES (111, 'file', 98, 'merchant/domain', '分站域名', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'fzym', 'fenzhanyuming', 1680264564, 1680265019, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (112, 'file', 94, 'finance/rebate/index', '返佣配置', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'fypz', 'fanyongpeizhi', 1680279076, 1680279076, 0, 'normal');
INSERT INTO `hm_auth_rule` VALUES (113, 'file', 94, 'finance/order/cashout/index', '提现订单', 'fa fa-circle-o', '', '', '', 1, 'addtabs', '', 'txdd', 'tixiandingdan', 1680349243, 1680349400, 5, 'normal');
INSERT INTO `hm_auth_rule` VALUES (114, 'file', 91, 'system/index', '系统配置', 'fa fa-cog', '', '', '', 1, 'addtabs', '', 'xtpz', 'xitongpeizhi', 1680441716, 1680441716, 0, 'normal');

-- ----------------------------
-- Table structure for hm_bill
-- ----------------------------
DROP TABLE IF EXISTS `hm_bill`;
CREATE TABLE `hm_bill`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NULL DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `before` decimal(10, 2) NULL DEFAULT NULL,
  `after` decimal(10, 2) NULL DEFAULT NULL COMMENT '变动后',
  `value` decimal(10, 2) NULL DEFAULT NULL COMMENT '变动值',
  `create_time` bigint(16) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of hm_bill
-- ----------------------------

-- ----------------------------
-- Table structure for hm_blog
-- ----------------------------
DROP TABLE IF EXISTS `hm_blog`;
CREATE TABLE `hm_blog`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文章标题',
  `category_id` int(10) NULL DEFAULT NULL COMMENT '文章分类',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '介绍',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '文章内容',
  `cover` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '封面图',
  `weigh` int(10) NULL DEFAULT 0 COMMENT '排序',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `deletetime` bigint(16) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_blog
-- ----------------------------

-- ----------------------------
-- Table structure for hm_blog_category
-- ----------------------------
DROP TABLE IF EXISTS `hm_blog_category`;
CREATE TABLE `hm_blog_category`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `flag` set('hot','index','recommend') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `weigh`(`weigh`, `id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分类表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_blog_category
-- ----------------------------

-- ----------------------------
-- Table structure for hm_cashout
-- ----------------------------
DROP TABLE IF EXISTS `hm_cashout`;
CREATE TABLE `hm_cashout`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `out_trade_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '订单号',
  `user_id` int(10) NULL DEFAULT NULL COMMENT '用户',
  `create_time` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `money` decimal(10, 2) NULL DEFAULT NULL COMMENT '提现金额',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '账户姓名',
  `account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '账号',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `complete_time` bigint(16) NULL DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '提现记录' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_cashout
-- ----------------------------

-- ----------------------------
-- Table structure for hm_category
-- ----------------------------
DROP TABLE IF EXISTS `hm_category`;
CREATE TABLE `hm_category`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `flag` set('hot','index','recommend') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `weigh`(`weigh`, `id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分类表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_category
-- ----------------------------
INSERT INTO `hm_category` VALUES (1, 0, 'page', '官方新闻', 'news', 'recommend', '/assets/img/qrcode.png', '', '', 'news', 1491635035, 1491635035, 1, 'normal');
INSERT INTO `hm_category` VALUES (2, 0, 'page', '移动应用', 'mobileapp', 'hot', '/assets/img/qrcode.png', '', '', 'mobileapp', 1491635035, 1491635035, 2, 'normal');
INSERT INTO `hm_category` VALUES (3, 2, 'page', '微信公众号', 'wechatpublic', 'index', '/assets/img/qrcode.png', '', '', 'wechatpublic', 1491635035, 1491635035, 3, 'normal');
INSERT INTO `hm_category` VALUES (4, 2, 'page', 'Android开发', 'android', 'recommend', '/assets/img/qrcode.png', '', '', 'android', 1491635035, 1491635035, 4, 'normal');
INSERT INTO `hm_category` VALUES (5, 0, 'page', '软件产品', 'software', 'recommend', '/assets/img/qrcode.png', '', '', 'software', 1491635035, 1491635035, 5, 'normal');
INSERT INTO `hm_category` VALUES (6, 5, 'page', '网站建站', 'website', 'recommend', '/assets/img/qrcode.png', '', '', 'website', 1491635035, 1491635035, 6, 'normal');
INSERT INTO `hm_category` VALUES (7, 5, 'page', '企业管理软件', 'company', 'index', '/assets/img/qrcode.png', '', '', 'company', 1491635035, 1491635035, 7, 'normal');
INSERT INTO `hm_category` VALUES (8, 6, 'page', 'PC端', 'website-pc', 'recommend', '/assets/img/qrcode.png', '', '', 'website-pc', 1491635035, 1491635035, 8, 'normal');
INSERT INTO `hm_category` VALUES (9, 6, 'page', '移动端', 'website-mobile', 'recommend', '/assets/img/qrcode.png', '', '', 'website-mobile', 1491635035, 1491635035, 9, 'normal');
INSERT INTO `hm_category` VALUES (10, 7, 'page', 'CRM系统 ', 'company-crm', 'recommend', '/assets/img/qrcode.png', '', '', 'company-crm', 1491635035, 1491635035, 10, 'normal');
INSERT INTO `hm_category` VALUES (11, 7, 'page', 'SASS平台软件', 'company-sass', 'recommend', '/assets/img/qrcode.png', '', '', 'company-sass', 1491635035, 1491635035, 11, 'normal');
INSERT INTO `hm_category` VALUES (12, 0, 'test', '测试1', 'test1', 'recommend', '/assets/img/qrcode.png', '', '', 'test1', 1491635035, 1491635035, 12, 'normal');
INSERT INTO `hm_category` VALUES (13, 0, 'test', '测试2', 'test2', 'recommend', '/assets/img/qrcode.png', '', '', 'test2', 1491635035, 1491635035, 13, 'normal');

-- ----------------------------
-- Table structure for hm_config
-- ----------------------------
DROP TABLE IF EXISTS `hm_config`;
CREATE TABLE `hm_config`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `visible` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '可见条件',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '变量值',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '变量字典数据',
  `rule` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '扩展属性',
  `setting` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '配置',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统配置' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_config
-- ----------------------------
INSERT INTO `hm_config` VALUES (1, 'name', 'basic', 'Site name', '请填写站点名称', 'string', '', '我的网站', '', 'required', '', '');
INSERT INTO `hm_config` VALUES (2, 'beian', 'basic', 'Beian', '粤ICP备15000000号-1', 'string', '', '', '', '', '', '');
INSERT INTO `hm_config` VALUES (3, 'cdnurl', 'basic', 'Cdn url', '如果全站静态资源使用第三方云储存请配置该值', 'string', '', '', '', '', '', '');
INSERT INTO `hm_config` VALUES (4, 'version', 'basic', 'Version', '如果静态资源有变动请重新配置该值', 'string', '', '1681373627', '', 'required', '', '');
INSERT INTO `hm_config` VALUES (5, 'timezone', 'basic', 'Timezone', '', 'string', '', 'Asia/Shanghai', '', 'required', '', '');
INSERT INTO `hm_config` VALUES (6, 'forbiddenip', 'basic', 'Forbidden ip', '一行一条记录', 'text', '', '', '', '', '', '');
INSERT INTO `hm_config` VALUES (7, 'languages', 'basic', 'Languages', '', 'array', '', '{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}', '', 'required', '', '');
INSERT INTO `hm_config` VALUES (8, 'fixedpage', 'basic', 'Fixed page', '请尽量输入左侧菜单栏存在的链接', 'string', '', 'dashboard', '', 'required', '', '');
INSERT INTO `hm_config` VALUES (9, 'categorytype', 'dictionary', 'Category type', '', 'array', '', '{\"default\":\"Default\",\"page\":\"Page\",\"article\":\"Article\",\"test\":\"Test\"}', '', '', '', '');
INSERT INTO `hm_config` VALUES (10, 'configgroup', 'dictionary', 'Config group', '', 'array', '', '{\"basic\":\"Basic\",\"email\":\"Email\",\"dictionary\":\"Dictionary\",\"user\":\"User\",\"example\":\"Example\"}', '', '', '', '');
INSERT INTO `hm_config` VALUES (11, 'mail_type', 'email', 'Mail type', '选择邮件发送方式', 'select', '', '1', '[\"请选择\",\"SMTP\"]', '', '', '');
INSERT INTO `hm_config` VALUES (12, 'mail_smtp_host', 'email', 'Mail smtp host', '错误的配置发送邮件会导致服务器超时', 'string', '', 'smtp.qq.com', '', '', '', '');
INSERT INTO `hm_config` VALUES (13, 'mail_smtp_port', 'email', 'Mail smtp port', '(不加密默认25,SSL默认465,TLS默认587)', 'string', '', '465', '', '', '', '');
INSERT INTO `hm_config` VALUES (14, 'mail_smtp_user', 'email', 'Mail smtp user', '（填写完整用户名）', 'string', '', '10000', '', '', '', '');
INSERT INTO `hm_config` VALUES (15, 'mail_smtp_pass', 'email', 'Mail smtp password', '（填写您的密码或授权码）', 'string', '', 'password', '', '', '', '');
INSERT INTO `hm_config` VALUES (16, 'mail_verify_type', 'email', 'Mail vertify type', '（SMTP验证方式[推荐SSL]）', 'select', '', '2', '[\"无\",\"TLS\",\"SSL\"]', '', '', '');
INSERT INTO `hm_config` VALUES (17, 'mail_from', 'email', 'Mail from', '', 'string', '', '10000@qq.com', '', '', '', '');
INSERT INTO `hm_config` VALUES (18, 'attachmentcategory', 'dictionary', 'Attachment category', '', 'array', '', '{\"category1\":\"Category1\",\"category2\":\"Category2\",\"custom\":\"Custom\"}', '', '', '', '');

-- ----------------------------
-- Table structure for hm_deliver
-- ----------------------------
DROP TABLE IF EXISTS `hm_deliver`;
CREATE TABLE `hm_deliver`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NULL DEFAULT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `create_time` bigint(16) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '发货表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_deliver
-- ----------------------------

-- ----------------------------
-- Table structure for hm_ems
-- ----------------------------
DROP TABLE IF EXISTS `hm_ems`;
CREATE TABLE `hm_ems`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '事件',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '邮箱',
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '验证码',
  `times` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '验证次数',
  `ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'IP',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '邮箱验证码表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_ems
-- ----------------------------

-- ----------------------------
-- Table structure for hm_goods
-- ----------------------------
DROP TABLE IF EXISTS `hm_goods`;
CREATE TABLE `hm_goods`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int(10) NULL DEFAULT NULL COMMENT '上级分类',
  `type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '商品类型',
  `attach` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '附加选项',
  `wholesale` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '批发优惠',
  `quota` int(10) NULL DEFAULT NULL COMMENT '每日限购',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `agency_see` tinyint(1) NULL DEFAULT 0 COMMENT '仅代理可见',
  `invented_sales` int(10) NULL DEFAULT NULL COMMENT '虚拟销量',
  `cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '封面图',
  `images` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `is_sku` tinyint(1) NULL DEFAULT 0 COMMENT '是否多规格',
  `sku_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '详细内容',
  `shelf` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '上架:0=下架,1=上架',
  `sales` int(10) NULL DEFAULT 0 COMMENT '销量',
  `stock` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '库存',
  `start_number` int(10) NULL DEFAULT NULL COMMENT '起拍数量',
  `weigh` int(10) NULL DEFAULT NULL COMMENT '商品排序',
  `unit` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '单位',
  `course` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '使用教程',
  `pop_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '弹窗内容',
  `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '商品表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_goods
-- ----------------------------
INSERT INTO `hm_goods` VALUES (1, 1, 'alone', '[{\"title\":\"\\u8d26\\u53f7\",\"placeholder\":\"\\u8bf7\\u8f93\\u5165\\u8d26\\u53f7\"},{\"title\":\"\\u5bc6\\u7801\",\"placeholder\":\"\\u8bf7\\u8f93\\u5165\\u5bc6\\u7801\"},{\"title\":\"\\u8054\\u7cfb\\u65b9\\u5f0f\",\"placeholder\":\"\\u8bf7\\u8f93\\u5165\\u8054\\u7cfb\\u65b9\\u5f0f\"}]', '[]', NULL, '惟有长江水，无语东流。 不忍登高临远，望故乡渺邈，归思难收。', 0, NULL, '/uploads/20230416/81821c7d8aa202b64e1e5c88a3222143.jpeg', NULL, 0, NULL, NULL, 1, 7, 0, NULL, 1, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (2, 1, 'alone', '[]', '[]', NULL, '渐霜风凄紧，关河冷落，残照当楼。是处红衰翠减，苒苒物华休。', 0, NULL, '/uploads/20230415/a9ec76f4539cecbfbe5cee120760d5d8.jpeg', NULL, 1, NULL, NULL, 1, 0, 0, NULL, 2, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (3, 1, 'alone', '[]', '[]', NULL, '忍把浮名，换了浅斟低唱！', 0, NULL, '/uploads/20230415/dcdd4a2265c8edb84b09ab3e8e770c10.jpg', NULL, 1, NULL, NULL, 1, 0, 0, NULL, 3, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (4, 1, 'alone', '[]', '[]', NULL, '且恁偎红倚翠，风流事，平生畅。青春都一饷。', 0, NULL, '/uploads/20230415/cf8e64517aaa7e7c4b97e9b44b0215c9.jpeg', NULL, 1, NULL, NULL, 1, 0, 0, NULL, 4, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (5, 1, 'alone', '[]', '[]', NULL, '烟花巷陌，依约丹青屏障。幸有意中人，堪寻访。', 0, NULL, '/uploads/20230415/aac468e3080243c2f560d4133da254c6.jpeg', NULL, 1, NULL, NULL, 1, 0, 0, NULL, 5, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (6, 1, 'alone', '[]', '[]', NULL, '未遂风云便，争不恣狂荡。何须论得丧？才子词人，自是白衣卿相。', 0, NULL, '/uploads/20230416/2d1a509c088b49ab0a11199652ea7aad.jpg', NULL, 1, NULL, NULL, 1, 0, 0, NULL, 6, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (7, 1, 'alone', '[]', '[]', NULL, '皓齿信难开，沉吟碧云间。勾践徵绝艳，扬蛾入吴关。', 0, NULL, '/uploads/20230415/a0dd4db2460620ff2cb56a0caa58e54e.jpeg', NULL, 1, NULL, NULL, 1, 0, 0, NULL, 7, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (8, 1, 'alone', '[]', '[]', NULL, '黄金榜上，偶失龙头望。明代暂遗贤，如何向？', 0, NULL, '/uploads/20230415/99fec839cddce79594194fb92f222e85.jpeg', NULL, 1, NULL, NULL, 1, 3, 0, NULL, 8, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (9, 1, 'invented', '[]', '[]', NULL, '寥落古行宫，宫花寂寞红。 白头宫女在，闲坐说玄宗。', 0, NULL, '/uploads/20230415/38816bd05d729f6e996926c19e4569a1.jpeg', NULL, 0, NULL, NULL, 1, 5, 0, NULL, 9, NULL, '<p><img src=\"http://hm.test.com/uploads/20230415/99fec839cddce79594194fb92f222e85.jpeg\" style=\"\"/></p><p><img src=\"http://hm.test.com/uploads/20230415/443bab862352dad8111896ede9a7cfaa.png\" style=\"\"/></p><p><br/></p>', NULL, NULL, NULL);
INSERT INTO `hm_goods` VALUES (10, 1, 'alone', '[]', '[{\"number\":\"10\",\"offer\":\"0.5\"},{\"number\":\"20\",\"offer\":\"1\"}]', NULL, '江汉曾为客，相逢每醉还。 浮云一别后，流水十年间', 0, NULL, '/uploads/20230415/738d8b03cb8ee9fd9ea63e8f95aba5ee.jpeg', NULL, 1, NULL, '<p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\">在玩家咸鱼之王的爬塔过程中，选择一个最强的阵容是非常重要的。咸鱼之王爬塔的核心在于团队协作和技能组合。因此，在选择阵容时，应该考虑到角色之间的互补性和配合效果。从战术和技能的角度出发，我们可以提出一些最佳的建议。</p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\"><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span><span style=\"font-family: Hind, sans-serif; letter-spacing: 0.15px; white-space: normal;\">aaaaaaa</span></p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\">首先，<span style=\"box-sizing: inherit; font-weight: bolder;\">阵容中必须有一个坦克</span>，拥有高生命值和防御能力。这个角色的主要职责是在攻击时吸引敌人的火力，并保护其他队员免受伤害。在咸鱼之王中，最好的坦克角色是凯恩和阿瑞斯。</p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\"><img src=\"http://hm.test.com/uploads/20230414/c2e714fa01c4a09d0a45cbaa2e8c46bb.png\" title=\"image.png\"/></p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\">其次，<span style=\"box-sizing: inherit; font-weight: bolder;\">阵容中应该有一个治愈角色</span>，可以为队友提供治疗和复活。在爬塔的过程中，队友之间的伤害是不可避免的，因此，治愈角色是非常必要的。最好的治愈角色是皮皮和卡瓦。</p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\">第三，<span style=\"box-sizing: inherit; font-weight: bolder;\">阵容中应该有一个输出伤害高的角色</span>，可以给敌人造成巨大的伤害。这种角色的攻击力应该高，并且应该具有多种技能。最好的输出角色是魔剑士和狂战士。</p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\">最后，<span style=\"box-sizing: inherit; font-weight: bolder;\">阵容中应该有一个控制角色</span>，可以让敌人进入眩晕或冰冻状态，使他们无法攻击。这样可以为队友提供宝贵的时间，并减少受到的伤害。最好的控制角色是萨满和冰霜女巫。</p><p style=\"box-sizing: inherit; margin-top: 0px; margin-bottom: 1rem; overflow-wrap: break-word; letter-spacing: 0.15px; hyphens: auto; overflow: unset; text-overflow: unset; display: -webkit-box; -webkit-line-clamp: unset; -webkit-box-orient: unset; font-family: Hind, sans-serif; white-space: normal;\">总的来说，<span style=\"box-sizing: inherit; font-weight: bolder;\">一个最强的阵容应该由坦克、治愈、输出和控制四种角色组成。</span>它们之间的互补性和配合效果非常重要。只有在角色选择和技能组合方面取得正确平衡，才能在咸鱼之王爬塔中取得胜利。同时，还应该不断地提高角色的技能等级和装备等级，以提高整个团队的实力。</p>', 1, 26, 0, NULL, 10, NULL, NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for hm_goods_category
-- ----------------------------
DROP TABLE IF EXISTS `hm_goods_category`;
CREATE TABLE `hm_goods_category`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `flag` set('hot','index','recommend') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `weigh`(`weigh`, `id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分类表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_goods_category
-- ----------------------------
INSERT INTO `hm_goods_category` VALUES (1, 0, '', '测试分类', '', '', '', '', '', '', 1681393516, 1681409020, 8, '');
INSERT INTO `hm_goods_category` VALUES (2, 0, '', '网盘扩容', '', '', '', '', '', '', 1681403473, 1681409121, 2, '');
INSERT INTO `hm_goods_category` VALUES (3, 0, '', '影视会员', '', '', '', '', '', '', 1681403477, 1681409108, 3, '');
INSERT INTO `hm_goods_category` VALUES (4, 0, '', '瓶盖货源', '', '', '', '', '', '', 1681403482, 1681409092, 4, '');
INSERT INTO `hm_goods_category` VALUES (5, 0, '', '微商专用', '', '', '', '', '', '', 1681403496, 1681409074, 5, '');
INSERT INTO `hm_goods_category` VALUES (6, 0, '', '双开软件', '', '', '', '', '', '', 1681403500, 1681409060, 6, '');
INSERT INTO `hm_goods_category` VALUES (7, 0, '', '共享账号', '', '', '', '', '', '', 1681403504, 1681409028, 7, '');
INSERT INTO `hm_goods_category` VALUES (8, 0, '', '黑客教学', '', '', '', '', '', '', 1681409226, 1681409226, 1, '');

-- ----------------------------
-- Table structure for hm_goods_order
-- ----------------------------
DROP TABLE IF EXISTS `hm_goods_order`;
CREATE TABLE `hm_goods_order`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `trade_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `out_trade_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_id` int(10) NULL DEFAULT NULL,
  `user_id` int(10) NULL DEFAULT NULL,
  `goods_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `create_time` bigint(16) NULL DEFAULT NULL,
  `pay_time` bigint(16) NULL DEFAULT NULL,
  `pay_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sku_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sku_id` int(10) NULL DEFAULT NULL,
  `goods_money` decimal(10, 2) NULL DEFAULT NULL,
  `goods_cost` decimal(10, 2) NULL DEFAULT 0.00,
  `goods_num` int(10) NULL DEFAULT NULL,
  `money` decimal(10, 2) NULL DEFAULT NULL,
  `attach` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mobile` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '商品订单表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_goods_order
-- ----------------------------

-- ----------------------------
-- Table structure for hm_merchant
-- ----------------------------
DROP TABLE IF EXISTS `hm_merchant`;
CREATE TABLE `hm_merchant`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `grade_id` int(10) NULL DEFAULT NULL,
  `user_id` int(10) NULL DEFAULT NULL COMMENT '用户',
  `prefix` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '前缀',
  `translate` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '后缀',
  `translate_id` int(10) NULL DEFAULT NULL COMMENT '后缀ID',
  `domain` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '完整域名',
  `money` decimal(10, 2) NULL DEFAULT NULL COMMENT '开通价格',
  `create_time` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分站列表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_merchant
-- ----------------------------

-- ----------------------------
-- Table structure for hm_merchant_domain
-- ----------------------------
DROP TABLE IF EXISTS `hm_merchant_domain`;
CREATE TABLE `hm_merchant_domain`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `domain` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '域名',
  `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分站域名' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_merchant_domain
-- ----------------------------

-- ----------------------------
-- Table structure for hm_merchant_grade
-- ----------------------------
DROP TABLE IF EXISTS `hm_merchant_grade`;
CREATE TABLE `hm_merchant_grade`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `money` decimal(10, 2) NULL DEFAULT NULL COMMENT '价格',
  `domain` tinyint(1) NULL DEFAULT 0 COMMENT '独立域名',
  `rebate` int(3) NULL DEFAULT 0,
  `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分站等级' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_merchant_grade
-- ----------------------------

-- ----------------------------
-- Table structure for hm_merchant_order
-- ----------------------------
DROP TABLE IF EXISTS `hm_merchant_order`;
CREATE TABLE `hm_merchant_order`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `p_trade_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `out_trade_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_id` int(10) NULL DEFAULT NULL,
  `user_id` int(10) NULL DEFAULT NULL,
  `goods_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `create_time` bigint(16) NULL DEFAULT NULL,
  `pay_time` bigint(16) NULL DEFAULT NULL,
  `pay_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sku_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `goods_money` decimal(10, 2) NULL DEFAULT NULL,
  `goods_num` int(10) NULL DEFAULT NULL,
  `money` decimal(10, 2) NULL DEFAULT NULL,
  `attach` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分站开通订单表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_merchant_order
-- ----------------------------

-- ----------------------------
-- Table structure for hm_options
-- ----------------------------
DROP TABLE IF EXISTS `hm_options`;
CREATE TABLE `hm_options`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of hm_options
-- ----------------------------
INSERT INTO `hm_options` VALUES (1, 'rebeat_1', '10', '一级返佣');
INSERT INTO `hm_options` VALUES (2, 'rebeat_2', '5', '二级返佣');
INSERT INTO `hm_options` VALUES (3, 'rebeat_3', '2', '三级返佣');
INSERT INTO `hm_options` VALUES (4, 'version', '1.1.0', '数据表版本');
INSERT INTO `hm_options` VALUES (5, 'name', '红盟云卡', '网站名称');
INSERT INTO `hm_options` VALUES (6, 'title', '为中华之崛起而读书', '网站标题');
INSERT INTO `hm_options` VALUES (7, 'keywords', '基于Thinkphp开发的开源商城系统', '关键词');
INSERT INTO `hm_options` VALUES (8, 'description', '基于Thinkphp开发的开源商城系统', '网站说明');
INSERT INTO `hm_options` VALUES (9, 'logo', '/template/default/images/dist/logo-blue.png', '网站Logo');
INSERT INTO `hm_options` VALUES (10, 'active_plugin', 'a:1:{i:1;s:4:\"scan\";}', '启用的插件');
INSERT INTO `hm_options` VALUES (11, 'beian', '', '备案号');
INSERT INTO `hm_options` VALUES (12, 'icon', '/assets/img/favicon.png', 'icon');
INSERT INTO `hm_options` VALUES (13, 'custom_code', '<script>\r\nconsole.log(\'红盟云卡下载地址：https://blog.ysxue.net/\');\r\n</script>', '自定义代码');
INSERT INTO `hm_options` VALUES (14, 'buy_input', 'a:1:{i:0;s:5:\"email\";}', '游客下单必填项');

-- ----------------------------
-- Table structure for hm_order_agency
-- ----------------------------
DROP TABLE IF EXISTS `hm_order_agency`;
CREATE TABLE `hm_order_agency`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NULL DEFAULT NULL,
  `agency_id` int(10) NULL DEFAULT NULL,
  `money` decimal(10, 2) NULL DEFAULT NULL,
  `create_time` bigint(16) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of hm_order_agency
-- ----------------------------

-- ----------------------------
-- Table structure for hm_recharge_order
-- ----------------------------
DROP TABLE IF EXISTS `hm_recharge_order`;
CREATE TABLE `hm_recharge_order`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `out_trade_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单号',
  `trade_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '交易编号',
  `user_id` int(10) NULL DEFAULT NULL COMMENT '用户',
  `money` decimal(10, 2) NULL DEFAULT NULL COMMENT '充值金额',
  `pay_type` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付方式',
  `create_time` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `pay_time` bigint(16) NULL DEFAULT NULL COMMENT '支付时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '充值订单' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of hm_recharge_order
-- ----------------------------

-- ----------------------------
-- Table structure for hm_sku
-- ----------------------------
DROP TABLE IF EXISTS `hm_sku`;
CREATE TABLE `hm_sku`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` int(10) NULL DEFAULT NULL COMMENT '商品',
  `sku` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '规格名称',
  `price` varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '价格',
  `stock` int(10) NULL DEFAULT 0 COMMENT '库存',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 45 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of hm_sku
-- ----------------------------
INSERT INTO `hm_sku` VALUES (1, 1, NULL, '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"22.00\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (2, 2, 'aaa', '{\"crossed_price\":\"\",\"cost_price\":\"1.00\",\"sale_price\":\"11.00\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (3, 2, 'bbb', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (4, 2, 'ccc', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (5, 2, 'ddd', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (6, 2, 'eee', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (7, 3, 'aaa', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (8, 3, 'bbb', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (9, 3, 'ccc', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (10, 3, 'ddd', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (11, 3, 'eee', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (12, 4, 'aaa', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (13, 4, 'bbb', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (14, 4, 'ccc', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (15, 4, 'ddd', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (16, 4, 'eee', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (17, 5, 'aaa', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (18, 5, 'bbb', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (19, 5, 'ccc', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (20, 5, 'ddd', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (21, 5, 'eee', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (22, 6, 'aaa', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (23, 6, 'bbb', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (24, 6, 'ccc', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (25, 6, 'ddd', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (26, 6, 'eee', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (27, 7, 'aaa', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (28, 7, 'bbb', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (29, 7, 'ccc', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (30, 7, 'ddd', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (31, 7, 'eee', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (32, 8, 'aaa', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (33, 8, 'bbb', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (34, 8, 'ccc', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (35, 8, 'ddd', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (36, 8, 'eee', '{\"code\":\"\",\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (37, 9, NULL, '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (39, 10, '1111', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"10.00\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (40, 10, '22233', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (42, 10, '123', '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (43, 11, NULL, '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);
INSERT INTO `hm_sku` VALUES (44, 12, NULL, '{\"crossed_price\":\"\",\"cost_price\":\"\",\"sale_price\":\"\",\"agency_price_1\":\"\",\"agency_price_2\":\"\",\"agency_price_3\":\"\",\"agency_price_4\":\"\"}', 0);

-- ----------------------------
-- Table structure for hm_sms
-- ----------------------------
DROP TABLE IF EXISTS `hm_sms`;
CREATE TABLE `hm_sms`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '事件',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '手机号',
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '验证码',
  `times` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '验证次数',
  `ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'IP',
  `createtime` bigint(16) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '短信验证码表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_sms
-- ----------------------------

-- ----------------------------
-- Table structure for hm_stock
-- ----------------------------
DROP TABLE IF EXISTS `hm_stock`;
CREATE TABLE `hm_stock`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) NULL DEFAULT NULL,
  `sku_id` int(10) NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `num` int(10) NULL DEFAULT 1,
  `create_time` bigint(16) NULL DEFAULT NULL,
  `sale_time` bigint(16) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '库存数据' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_stock
-- ----------------------------

-- ----------------------------
-- Table structure for hm_substation_grade
-- ----------------------------
DROP TABLE IF EXISTS `hm_substation_grade`;
CREATE TABLE `hm_substation_grade`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `price` decimal(10, 2) NULL DEFAULT NULL COMMENT '开通价格',
  `weigh` int(10) NULL DEFAULT NULL COMMENT '排序',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `deletetime` bigint(16) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '#用户 - 分站等级' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_substation_grade
-- ----------------------------

-- ----------------------------
-- Table structure for hm_test
-- ----------------------------
DROP TABLE IF EXISTS `hm_test`;
CREATE TABLE `hm_test`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `create_time` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 398 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '测试表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_test
-- ----------------------------
INSERT INTO `hm_test` VALUES (385, '代码错误', 'Undefined variable: out_trade_no---99', '2023-04-11 15:04:18');
INSERT INTO `hm_test` VALUES (386, '代码错误', 'Trying to access array offset on value of type null---182', '2023-04-11 15:05:43');
INSERT INTO `hm_test` VALUES (387, '异步回调', '异步回调', '2023-04-11 15:56:04');
INSERT INTO `hm_test` VALUES (388, '异步回调', '异步回调', '2023-04-11 16:02:21');
INSERT INTO `hm_test` VALUES (389, '验签失败', '验签失败', '2023-04-11 16:02:21');
INSERT INTO `hm_test` VALUES (390, '异步回调', '异步回调', '2023-04-11 16:11:42');
INSERT INTO `hm_test` VALUES (391, '验签失败', '验签失败', '2023-04-11 16:11:42');
INSERT INTO `hm_test` VALUES (392, '异步回调', '异步回调', '2023-04-11 16:27:03');
INSERT INTO `hm_test` VALUES (393, '异步回调', '异步回调', '2023-04-11 16:39:11');
INSERT INTO `hm_test` VALUES (394, '异步回调', '异步回调', '2023-04-11 16:45:02');
INSERT INTO `hm_test` VALUES (395, '异步回调', '异步回调', '2023-04-11 16:52:47');
INSERT INTO `hm_test` VALUES (396, '异步回调', '异步回调', '2023-04-11 16:54:22');
INSERT INTO `hm_test` VALUES (397, '异步回调', '异步回调', '2023-04-12 19:30:13');

-- ----------------------------
-- Table structure for hm_user
-- ----------------------------
DROP TABLE IF EXISTS `hm_user`;
CREATE TABLE `hm_user`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '组别ID',
  `p1` int(10) NULL DEFAULT 0,
  `p2` int(10) NULL DEFAULT 0,
  `p3` int(10) NULL DEFAULT 0,
  `merchant_id` int(10) NULL DEFAULT 0,
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '头像',
  `agency_id` int(10) NULL DEFAULT 0,
  `level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '等级',
  `gender` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `bio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '格言',
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '余额',
  `consume` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '总消费',
  `score` int(10) NOT NULL DEFAULT 0 COMMENT '积分',
  `successions` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '连续登录天数',
  `maxsuccessions` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '最大连续登录天数',
  `prevtime` bigint(16) NULL DEFAULT NULL COMMENT '上次登录时间',
  `logintime` bigint(16) NULL DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '登录IP',
  `loginfailure` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '失败次数',
  `joinip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '加入IP',
  `jointime` bigint(16) NULL DEFAULT NULL COMMENT '加入时间',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'Token',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  `verification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '验证',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `username`(`username`) USING BTREE,
  INDEX `email`(`email`) USING BTREE,
  INDEX `mobile`(`mobile`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user
-- ----------------------------

-- ----------------------------
-- Table structure for hm_user_agency
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_agency`;
CREATE TABLE `hm_user_agency`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '代理名称',
  `discount` decimal(5, 2) NULL DEFAULT NULL COMMENT '折扣',
  `price` decimal(10, 2) NULL DEFAULT 0.00,
  `weigh` int(10) NULL DEFAULT NULL COMMENT '排序',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `deletetime` bigint(16) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '#用户 - 代理等级' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user_agency
-- ----------------------------
INSERT INTO `hm_user_agency` VALUES (1, '普通代理', 9.50, 15.00, 4, 1678969619, 1681478129, NULL);
INSERT INTO `hm_user_agency` VALUES (2, '精英代理', 8.00, 50.00, 3, 1678969642, 1678969642, NULL);
INSERT INTO `hm_user_agency` VALUES (3, '至尊代理', 6.50, 100.00, 2, 1678969661, 1678969661, NULL);
INSERT INTO `hm_user_agency` VALUES (4, '合作商', 5.00, 180.00, 1, 1678969668, 1678969668, NULL);

-- ----------------------------
-- Table structure for hm_user_group
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_group`;
CREATE TABLE `hm_user_group`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '组名',
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '权限节点',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员组表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user_group
-- ----------------------------
INSERT INTO `hm_user_group` VALUES (1, '默认组', '1,2,3,4,5,6,7,8,9,10,11,12', 1491635035, 1491635035, 'normal');

-- ----------------------------
-- Table structure for hm_user_money_log
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_money_log`;
CREATE TABLE `hm_user_money_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变更余额',
  `before` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变更前余额',
  `after` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变更后余额',
  `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员余额变动表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user_money_log
-- ----------------------------

-- ----------------------------
-- Table structure for hm_user_rule
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_rule`;
CREATE TABLE `hm_user_rule`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) NULL DEFAULT NULL COMMENT '父ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '标题',
  `remark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '备注',
  `ismenu` tinyint(1) NULL DEFAULT NULL COMMENT '是否菜单',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NULL DEFAULT 0 COMMENT '权重',
  `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员规则表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user_rule
-- ----------------------------
INSERT INTO `hm_user_rule` VALUES (1, 0, 'index', 'Frontend', '', 1, 1491635035, 1491635035, 1, 'normal');
INSERT INTO `hm_user_rule` VALUES (2, 0, 'api', 'API Interface', '', 1, 1491635035, 1491635035, 2, 'normal');
INSERT INTO `hm_user_rule` VALUES (3, 1, 'user', 'User Module', '', 1, 1491635035, 1491635035, 12, 'normal');
INSERT INTO `hm_user_rule` VALUES (4, 2, 'user', 'User Module', '', 1, 1491635035, 1491635035, 11, 'normal');
INSERT INTO `hm_user_rule` VALUES (5, 3, 'index/user/login', 'Login', '', 0, 1491635035, 1491635035, 5, 'normal');
INSERT INTO `hm_user_rule` VALUES (6, 3, 'index/user/register', 'Register', '', 0, 1491635035, 1491635035, 7, 'normal');
INSERT INTO `hm_user_rule` VALUES (7, 3, 'index/user/index', 'User Center', '', 0, 1491635035, 1491635035, 9, 'normal');
INSERT INTO `hm_user_rule` VALUES (8, 3, 'index/user/profile', 'Profile', '', 0, 1491635035, 1491635035, 4, 'normal');
INSERT INTO `hm_user_rule` VALUES (9, 4, 'api/user/login', 'Login', '', 0, 1491635035, 1491635035, 6, 'normal');
INSERT INTO `hm_user_rule` VALUES (10, 4, 'api/user/register', 'Register', '', 0, 1491635035, 1491635035, 8, 'normal');
INSERT INTO `hm_user_rule` VALUES (11, 4, 'api/user/index', 'User Center', '', 0, 1491635035, 1491635035, 10, 'normal');
INSERT INTO `hm_user_rule` VALUES (12, 4, 'api/user/profile', 'Profile', '', 0, 1491635035, 1491635035, 3, 'normal');

-- ----------------------------
-- Table structure for hm_user_score_log
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_score_log`;
CREATE TABLE `hm_user_score_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `score` int(10) NOT NULL DEFAULT 0 COMMENT '变更积分',
  `before` int(10) NOT NULL DEFAULT 0 COMMENT '变更前积分',
  `after` int(10) NOT NULL DEFAULT 0 COMMENT '变更后积分',
  `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员积分变动表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user_score_log
-- ----------------------------

-- ----------------------------
-- Table structure for hm_user_token
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_token`;
CREATE TABLE `hm_user_token`  (
  `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `expiretime` bigint(16) NULL DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`token`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员Token表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_user_token
-- ----------------------------
INSERT INTO `hm_user_token` VALUES ('063917baea5d24d4b07fc35a64cae9e9087bc8e2', 2, 1679907008, 1682499008);
INSERT INTO `hm_user_token` VALUES ('09a00cc8891817a231d3e1595d485b78958179c9', 2, 1679408002, 1682000002);
INSERT INTO `hm_user_token` VALUES ('0ef63c4f972c3e5b68cac466f7ac64be8003b94b', 2, 1679826460, 1682418460);
INSERT INTO `hm_user_token` VALUES ('15d0efbe4ce54f2c3fdf7f07902696d716019d12', 1, 1681476339, 1684068339);
INSERT INTO `hm_user_token` VALUES ('37a7a079d9ff22ac75bb610cb28043ba32b66032', 2, 1680155722, 1682747722);
INSERT INTO `hm_user_token` VALUES ('3a8961cbbcbda0df0b86bdedfd1a8761a5a76133', 2, 1679128526, 1681720526);
INSERT INTO `hm_user_token` VALUES ('3bed1c9df5cc35feea813c1784f30824e344b677', 2, 1680856000, 1683448000);
INSERT INTO `hm_user_token` VALUES ('42b1283429478f75456c10175646717fdad91adb', 2, 1680262287, 1682854287);
INSERT INTO `hm_user_token` VALUES ('53e3c8800046a8d6c5898e7f019ee9bf2e247638', 2, 1679206872, 1681798872);
INSERT INTO `hm_user_token` VALUES ('595b3fd575ed6df66b0550fc8c3ab638597a2b75', 10, 1680364745, 1682956745);
INSERT INTO `hm_user_token` VALUES ('6f2458f6bb4439bbecc0efdf4a602af4712507d9', 2, 1681130964, 1683722964);
INSERT INTO `hm_user_token` VALUES ('7a44c3addcd4ae1dd31584d55a11441df3ebc46e', 1, 1681381121, 1683973121);
INSERT INTO `hm_user_token` VALUES ('7e21ba443e5da7b1b0e92e1ce2116e14455ce65f', 8, 1680444359, 1683036359);
INSERT INTO `hm_user_token` VALUES ('82f4c076d12408edcd8887d132d82720eb2460f2', 12, 1680444349, 1683036349);
INSERT INTO `hm_user_token` VALUES ('87a2cfbd92ee9e043aea94938c17f939b0b656b6', 2, 1679995783, 1682587783);
INSERT INTO `hm_user_token` VALUES ('897fca704a03588e6c9d6a23d891eef230928486', 2, 1680158806, 1682750806);
INSERT INTO `hm_user_token` VALUES ('ac7b0c5f27a99f8c1aeded80a33612a978e13e2b', 2, 1679535411, 1682127411);
INSERT INTO `hm_user_token` VALUES ('acfbf9ffefd1df4fc00faaec949dfb296dee16e1', 2, 1679657616, 1682249616);
INSERT INTO `hm_user_token` VALUES ('b1ad04a203ad66ae317c5cd284dd8e28007744cb', 2, 1681116153, 1683708153);
INSERT INTO `hm_user_token` VALUES ('b35fd128c379341fd05e0ca65499a1f4d33d1d4a', 2, 1680053680, 1682645680);
INSERT INTO `hm_user_token` VALUES ('be1175c4a759cd38d4d00c132682a790d4c60094', 2, 1679578547, 1682170547);
INSERT INTO `hm_user_token` VALUES ('c463312698c16238c410e8f5cceaa595ff54e1e0', 1, 1681455740, 1684047740);
INSERT INTO `hm_user_token` VALUES ('c747c929c8789d97ce9b921f9bbfd66a0c9875e7', 2, 1679755103, 1682347103);
INSERT INTO `hm_user_token` VALUES ('e2640b4952f6e485666eaa772c43ba00c283b8c2', 8, 1680436274, 1683028274);
INSERT INTO `hm_user_token` VALUES ('e398b04f20db46189304ce5ae87005ad0300321d', 11, 1680362443, 1682954443);
INSERT INTO `hm_user_token` VALUES ('f587667bdb9b2eefe337ec76798e05c189a5160d', 13, 1681288854, 1683880854);
INSERT INTO `hm_user_token` VALUES ('f9aee7244d65bed0efd89113ede6841880f410a5', 14, 1681296435, 1683888435);
INSERT INTO `hm_user_token` VALUES ('fc04b47dd043db51edf630c9b132f80181b00156', 2, 1679571414, 1682163414);
INSERT INTO `hm_user_token` VALUES ('ff4f0e6708b17bca5c57e50dd539517af3a7d82a', 10, 1680436944, 1683028944);

-- ----------------------------
-- Table structure for hm_version
-- ----------------------------
DROP TABLE IF EXISTS `hm_version`;
CREATE TABLE `hm_version`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `oldversion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '旧版本号',
  `newversion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '新版本号',
  `packagesize` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '包大小',
  `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '升级内容',
  `downloadurl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '下载地址',
  `enforce` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '强制更新',
  `createtime` bigint(16) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) NULL DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '版本表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of hm_version
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
