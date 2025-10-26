-- SuperAdmin 数据库结构
-- 数据库名: superadmin_com
-- 表前缀: ba_

-- 创建数据库
CREATE DATABASE IF NOT EXISTS `superadmin_com` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `superadmin_com`;

-- -------------------------------------
-- 表结构: ba_admin - 管理员表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_admin`;
CREATE TABLE `ba_admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `login_failure` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `last_login_time` bigint unsigned DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `join_time` bigint unsigned DEFAULT NULL COMMENT '加入时间',
  `join_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '加入IP',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- -------------------------------------
-- 表结构: ba_admin_access - 管理员权限表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_admin_access`;
CREATE TABLE `ba_admin_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '权限类型',
  `data` varchar(255) NOT NULL DEFAULT '' COMMENT '权限数据',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员权限表';

-- -------------------------------------
-- 表结构: ba_admin_group - 管理员分组表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_admin_group`;
CREATE TABLE `ba_admin_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分组名称',
  `rules` text COMMENT '权限节点',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员分组表';

-- -------------------------------------
-- 表结构: ba_admin_log - 管理员操作日志表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_admin_log`;
CREATE TABLE `ba_admin_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作URL',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `data` text COMMENT '请求参数',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员操作日志表';

-- -------------------------------------
-- 表结构: ba_attachment - 附件表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_attachment`;
CREATE TABLE `ba_attachment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '物理路径',
  `full_url` varchar(255) NOT NULL DEFAULT '' COMMENT '完整URL',
  `preview_url` varchar(255) NOT NULL DEFAULT '' COMMENT '预览URL',
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `filesize` int NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(255) NOT NULL DEFAULT '' COMMENT 'Mime类型',
  `extension` varchar(255) NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `upload_type` enum('local','alioss','qcos','qiniu','upyun') NOT NULL DEFAULT 'local' COMMENT '上传类型',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件表';

-- -------------------------------------
-- 表结构: ba_config - 配置表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_config`;
CREATE TABLE `ba_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `group` varchar(50) NOT NULL DEFAULT '' COMMENT '分组',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '键名',
  `value` longtext COMMENT '值',
  `content` text COMMENT '内容',
  `rule` varchar(255) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配置表';

-- -------------------------------------
-- 表结构: ba_config_group - 配置分组表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_config_group`;
CREATE TABLE `ba_config_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '分组描述',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配置分组表';

-- -------------------------------------
-- 表结构: ba_curd_log - CRUD记录表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_curd_log`;
CREATE TABLE `ba_curd_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `table_name` varchar(200) NOT NULL DEFAULT '' COMMENT '数据表名',
  `table` text COMMENT '数据表数据',
  `fields` text COMMENT '字段数据',
  `status` enum('delete','success','error','start') NOT NULL DEFAULT 'start' COMMENT '状态:delete=已删除,success=成功,error=失败,start=生成中',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRUD记录表';

-- -------------------------------------
-- 表结构: ba_data_recycle - 数据回收站表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_data_recycle`;
CREATE TABLE `ba_data_recycle` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `data_table` varchar(64) NOT NULL DEFAULT '' COMMENT '数据表名',
  `primary_key` varchar(64) NOT NULL DEFAULT '' COMMENT '主键名',
  `data_id` int unsigned NOT NULL DEFAULT '0' COMMENT '数据ID',
  `data` longtext COMMENT '数据详情',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `delete_time` bigint unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据回收站表';

-- -------------------------------------
-- 表结构: ba_faq - 常见问题表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_faq`;
CREATE TABLE `ba_faq` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `keyword_rows` varchar(100) NOT NULL DEFAULT '' COMMENT '关键词',
  `content` text COMMENT '内容',
  `views` int unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `likes` int unsigned NOT NULL DEFAULT '0' COMMENT '有帮助数',
  `dislikes` int unsigned NOT NULL DEFAULT '0' COMMENT '无帮助数',
  `note_textarea` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态:0=隐藏,1=正常',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='常见问题表';

-- -------------------------------------
-- 表结构: ba_feedback - 用户反馈表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_feedback`;
CREATE TABLE `ba_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'URL',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  `feedback_images` text COMMENT '反馈图片',
  `reply` text COMMENT '回复',
  `reply_time` bigint unsigned DEFAULT NULL COMMENT '回复时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户反馈表';

-- -------------------------------------
-- 表结构: ba_route - 路由表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_route`;
CREATE TABLE `ba_route` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '路由规则',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '路由地址',
  `method` varchar(10) NOT NULL DEFAULT '*' COMMENT '请求类型',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='路由表';

-- -------------------------------------
-- 表结构: ba_user - 用户表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_user`;
CREATE TABLE `ba_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '性别:0=未知,1=男,2=女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `money` int unsigned NOT NULL DEFAULT '0' COMMENT '余额',
  `score` int unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `lastlogintime` bigint unsigned DEFAULT NULL COMMENT '上次登录时间',
  `lastloginip` varchar(50) NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `loginfailure` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `joinip` varchar(50) NOT NULL DEFAULT '' COMMENT '加入IP',
  `jointime` bigint unsigned DEFAULT NULL COMMENT '加入时间',
  `motto` varchar(255) NOT NULL DEFAULT '' COMMENT '签名',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  `updatetime` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- -------------------------------------
-- 表结构: ba_user_group - 用户组表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_user_group`;
CREATE TABLE `ba_user_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text COMMENT '权限节点',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `updatetime` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户组表';

-- -------------------------------------
-- 表结构: ba_user_money_log - 用户余额变动表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_user_money_log`;
CREATE TABLE `ba_user_money_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `money` int NOT NULL DEFAULT '0' COMMENT '变更余额',
  `before` int NOT NULL DEFAULT '0' COMMENT '变更前余额',
  `after` int NOT NULL DEFAULT '0' COMMENT '变更后余额',
  `memo` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户余额变动表';

-- -------------------------------------
-- 表结构: ba_user_rule - 用户菜单权限规则表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_user_rule`;
CREATE TABLE `ba_user_rule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` enum('route','menu_dir','menu','nav_user_menu','nav','button') NOT NULL DEFAULT 'menu' COMMENT '类型:route=路由,menu_dir=菜单目录,menu=菜单项,nav_user_menu=顶栏用户菜单下拉项,nav=顶栏菜单项,button=页面按钮',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') NOT NULL DEFAULT 'tab' COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) NOT NULL DEFAULT '' COMMENT '组件路径',
  `no_login_valid` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '未登录有效:0=否,1=是',
  `extend` enum('none','add_rules_only','add_menu_only') NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `updatetime` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户菜单权限规则表';

-- -------------------------------------
-- 表结构: ba_user_score_log - 用户积分变动表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_user_score_log`;
CREATE TABLE `ba_user_score_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `score` int NOT NULL DEFAULT '0' COMMENT '变更积分',
  `before` int NOT NULL DEFAULT '0' COMMENT '变更前积分',
  `after` int NOT NULL DEFAULT '0' COMMENT '变更后积分',
  `memo` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户积分变动表';

-- -------------------------------------
-- 表结构: ba_admin_rule - 管理员菜单规则表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_admin_rule`;
CREATE TABLE `ba_admin_rule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` enum('route','menu_dir','menu','nav_user_menu','nav','button') NOT NULL DEFAULT 'menu' COMMENT '类型:route=路由,menu_dir=菜单目录,menu=菜单项,nav_user_menu=顶栏用户菜单下拉项,nav=顶栏菜单项,button=页面按钮',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') NOT NULL DEFAULT 'tab' COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) NOT NULL DEFAULT '' COMMENT '组件路径',
  `no_login_valid` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '未登录有效:0=否,1=是',
  `extend` enum('none','add_rules_only','add_menu_only') NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `updatetime` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员菜单规则表';

-- -------------------------------------
-- 表结构: ba_token - 用户Token表
-- -------------------------------------
DROP TABLE IF EXISTS `ba_token`;
CREATE TABLE `ba_token` (
  `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'Token',
  `type` varchar(15) NOT NULL DEFAULT '' COMMENT '类型',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `createtime` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `expiretime` bigint unsigned DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户Token表';

-- -------------------------------------
-- 初始化管理员账号 (用户名: admin, 密码: admin123)
-- -------------------------------------
INSERT INTO `ba_admin` (`id`, `username`, `nickname`, `password`, `salt`, `avatar`, `email`, `mobile`, `login_failure`, `last_login_time`, `last_login_ip`, `join_time`, `join_ip`, `status`, `remark`, `update_time`, `create_time`) VALUES
(1, 'admin', '超级管理员', 'c4ca4238a0b923820dcc509a6f75849b', 'abcdefg', '', 'admin@example.com', '13888888888', 0, 0, '', 1672531200, '', 'normal', '', 1672531200, 1672531200);

-- -------------------------------------
-- 初始化管理员组
-- -------------------------------------
INSERT INTO `ba_admin_group` (`id`, `pid`, `name`, `rules`, `status`, `update_time`, `create_time`) VALUES
(1, 0, '超级管理员', '*', 'normal', 1672531200, 1672531200),
(2, 0, '管理员组', '1,2,3', 'normal', 1672531200, 1672531200);

-- -------------------------------------
-- 初始化用户组
-- -------------------------------------
INSERT INTO `ba_user_group` (`id`, `name`, `rules`, `status`, `updatetime`, `createtime`) VALUES
(1, '普通用户', '', '1', 1672531200, 1672531200),
(2, 'VIP用户', '1,2,3', '1', 1672531200, 1672531200);

-- -------------------------------------
-- 初始化配置
-- -------------------------------------
INSERT INTO `ba_config` (`id`, `type`, `name`, `group`, `key`, `value`, `content`, `rule`, `extend`, `weigh`, `create_time`, `update_time`) VALUES
(1, 'all', '网站名称', 'basic', 'site_name', 'SuperAdmin', '网站名称', 'required', '', 100, 1672531200, 1672531200),
(2, 'all', '网站描述', 'basic', 'site_description', '基于ThinkPHP和Vue构建的后台管理系统', '网站描述', '', '', 99, 1672531200, 1672531200),
(3, 'all', '网站关键词', 'basic', 'site_keywords', 'SuperAdmin,后台管理系统,ThinkPHP,Vue', '网站关键词', '', '', 98, 1672531200, 1672531200),
(4, 'all', '网站备案号', 'basic', 'site_icp', '', '网站备案号', '', '', 97, 1672531200, 1672531200);

-- -------------------------------------
-- 初始化配置分组
-- -------------------------------------
INSERT INTO `ba_config_group` (`id`, `type`, `name`, `description`, `weigh`, `create_time`, `update_time`) VALUES
(1, 'all', '基础配置', '网站基础配置', 100, 1672531200, 1672531200),
(2, 'all', '上传配置', '文件上传配置', 99, 1672531200, 1672531200),
(3, 'all', '邮件配置', '邮件发送配置', 98, 1672531200, 1672531200);

-- -------------------------------------
-- 初始化管理员菜单
-- -------------------------------------
INSERT INTO `ba_admin_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `updatetime`, `createtime`) VALUES
(1, 0, 'menu_dir', '控制台', 'dashboard', '/dashboard', 'layui-icon-home', 'tab', '', 'dashboard/index', 0, 'none', '', 100, '1', 1672531200, 1672531200),
(2, 1, 'menu', '控制台', 'dashboard/console', '/dashboard/console', 'layui-icon-console', 'tab', '', 'dashboard/console', 0, 'none', '', 99, '1', 1672531200, 1672531200),
(3, 0, 'menu_dir', '系统管理', 'system', '/system', 'layui-icon-set', 'tab', '', '', 0, 'none', '', 99, '1', 1672531200, 1672531200),
(4, 3, 'menu', '管理员管理', 'system/admin', '/system/admin', 'layui-icon-username', 'tab', '', 'system/admin/index', 0, 'none', '', 98, '1', 1672531200, 1672531200),
(5, 3, 'menu', '角色管理', 'system/admin_group', '/system/admin_group', 'layui-icon-group', 'tab', '', 'system/admin_group/index', 0, 'none', '', 97, '1', 1672531200, 1672531200),
(6, 3, 'menu', '菜单管理', 'system/admin_rule', '/system/admin_rule', 'layui-icon-menu-fill', 'tab', '', 'system/admin_rule/index', 0, 'none', '', 96, '1', 1672531200, 1672531200),
(7, 3, 'menu', '系统配置', 'system/config', '/system/config', 'layui-icon-set-fill', 'tab', '', 'system/config/index', 0, 'none', '', 95, '1', 1672531200, 1672531200);

-- -------------------------------------
-- 初始化用户菜单
-- -------------------------------------
INSERT INTO `ba_user_rule` (`id`, `pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `no_login_valid`, `extend`, `remark`, `weigh`, `status`, `updatetime`, `createtime`) VALUES
(1, 0, 'menu_dir', '个人中心', 'user', '/user', 'layui-icon-user', 'tab', '', '', 0, 'none', '', 100, '1', 1672531200, 1672531200),
(2, 1, 'menu', '个人资料', 'user/profile', '/user/profile', 'layui-icon-user', 'tab', '', 'user/profile', 0, 'none', '', 99, '1', 1672531200, 1672531200),
(3, 1, 'menu', '修改密码', 'user/password', '/user/password', 'layui-icon-password', 'tab', '', 'user/password', 0, 'none', '', 98, '1', 1672531200, 1672531200);

-- 更新待办事项状态
-- -------------------------------------