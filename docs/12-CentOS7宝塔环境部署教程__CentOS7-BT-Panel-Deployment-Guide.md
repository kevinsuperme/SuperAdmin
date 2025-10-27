# CentOS 7 宝塔环境部署详细教程

## 目录
1. [环境准备](#环境准备)
2. [安装宝塔面板](#安装宝塔面板)
3. [配置Web环境](#配置web环境)
4. [部署项目](#部署项目)
5. [常见问题解决](#常见问题解决)
6. [性能优化建议](#性能优化建议)

## 环境准备

### 1. 系统要求
- CentOS 7.6+ (推荐7.9)
- 内存：最低1GB，推荐2GB以上
- 硬盘：最低20GB可用空间
- 网络：能够访问互联网

### 2. 更新系统
```bash
# 更新系统到最新版本
sudo yum update -y

# 安装必要的基础工具
sudo yum install -y wget curl vim unzip
```

### 3. 防火墙设置
```bash
# 查看防火墙状态
sudo firewall-cmd --state

# 临时关闭防火墙（不推荐生产环境）
sudo systemctl stop firewalld

# 永久关闭防火墙（不推荐生产环境）
sudo systemctl disable firewalld

# 或者开放必要端口（推荐）
sudo firewall-cmd --permanent --add-port=8888/tcp  # 宝塔面板端口
sudo firewall-cmd --permanent --add-port=80/tcp    # HTTP
sudo firewall-cmd --permanent --add-port=443/tcp   # HTTPS
sudo firewall-cmd --permanent --add-port=3306/tcp  # MySQL
sudo firewall-cmd --permanent --add-port=6379/tcp  # Redis
sudo firewall-cmd --reload
```

## 安装宝塔面板

### 1. 下载并安装宝塔面板
```bash
# 下载宝塔面板安装脚本
wget -O install.sh http://download.bt.cn/install/install_6.0.sh

# 执行安装脚本
sudo bash install.sh ed8484bec

# 或者使用官方一键安装命令
yum install -y wget && wget -O install.sh http://download.bt.cn/install/install_6.0.sh && sh install.sh ed8484bec
```

### 2. 安装过程中的注意事项
- 安装过程中会提示输入y/n，全部输入y
- 安装完成后会显示面板地址、用户名和密码，请务必保存
- 默认面板端口为8888，可在安装后修改

### 3. 获取宝塔面板信息
```bash
# 查看宝塔面板信息
sudo bt default

# 修改面板端口（例如改为8889）
sudo bt 8

# 修改面板用户名
sudo bt 5

# 修改面板密码
sudo bt 6
```

## 配置Web环境

### 1. 登录宝塔面板
- 浏览器访问：`http://服务器IP:8888`
- 输入安装时显示的用户名和密码登录

### 2. 安装Web环境
1. 登录后进入"软件商店"
2. 选择"一键安装"或"LNMP/LAMP"环境
3. 推荐配置：
   - Nginx 1.20+
   - MySQL 5.7/8.0
   - PHP 7.4/8.0
   - Redis 6.0+

### 3. 配置PHP环境
1. 进入"软件商店" → "已安装"
2. 点击PHP设置
3. 安装必要扩展：
   - opcache（性能优化）
   - redis（缓存支持）
   - imagemagick（图像处理）
   - fileinfo（文件信息）
   - exif（图片信息）

### 4. 配置MySQL
1. 修改MySQL密码
2. 设置字符集为utf8mb4
3. 配置远程访问（如需要）
4. 优化MySQL配置

### 5. 配置Redis
1. 设置Redis密码
2. 修改Redis配置
3. 开启Redis持久化

## 部署项目

### 1. 创建网站
1. 进入"网站"页面
2. 点击"添加站点"
3. 填写域名信息
4. 选择PHP版本
5. 创建数据库（可选）

### 2. 上传项目文件
```bash
# 方法1：通过宝塔文件管理器上传
# 登录宝塔面板 → 文件 → 进入网站根目录 → 上传

# 方法2：通过Git克隆
cd /www/wwwroot/your-domain.com
git clone https://github.com/kevinsuperme/SuperAdmin.git .

# 方法3：通过FTP上传
# 使用FTP客户端上传到网站根目录
```

### 3. 设置文件权限
```bash
# 设置网站目录权限
sudo chown -R www:www /www/wwwroot/your-domain.com
sudo chmod -R 755 /www/wwwroot/your-domain.com

# 设置特定目录权限（如需要）
sudo chmod -R 777 /www/wwwroot/your-domain.com/runtime
sudo chmod -R 777 /www/wwwroot/your-domain.com/public/uploads
```

### 4. 配置Nginx
1. 进入"网站" → "设置" → "伪静态"
2. 选择适合的伪静态规则或添加自定义规则

```nginx
# ThinkPHP伪静态规则
location / {
    if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;   break;
    }
}
```

3. 配置SSL证书（可选）
   - 进入"SSL"页面
   - 选择"Let's Encrypt"或上传自有证书

### 5. 配置数据库
1. 进入"数据库"页面
2. 创建数据库和用户
3. 导入数据库文件
4. 修改项目数据库配置

```php
// 修改项目配置文件
// config/database.php
return [
    'type'            => 'mysql',
    'hostname'        => 'localhost',
    'database'        => 'your_database_name',
    'username'        => 'your_username',
    'password'        => 'your_password',
    'hostport'        => '3306',
    // 其他配置...
];
```

### 6. 配置环境变量
```bash
# 复制环境变量文件
cp .env.example .env

# 编辑环境变量文件
vim .env
```

### 7. 安装项目依赖
```bash
# 安装PHP依赖
composer install --no-dev --optimize-autoloader

# 安装前端依赖（如果需要）
cd web
npm install
npm run build
```

## 常见问题解决

### 1. 宝塔面板无法访问
```bash
# 检查防火墙设置
sudo firewall-cmd --list-ports

# 检查面板服务状态
sudo systemctl status bt

# 重启面板服务
sudo systemctl restart bt

# 查看面板端口
sudo bt 14
```

### 2. 网站无法访问
```bash
# 检查Nginx状态
sudo systemctl status nginx

# 检查Nginx配置
sudo nginx -t

# 重启Nginx
sudo systemctl restart nginx

# 查看Nginx错误日志
sudo tail -f /www/server/nginx/logs/error.log
```

### 3. 数据库连接失败
```bash
# 检查MySQL状态
sudo systemctl status mysqld

# 检查MySQL错误日志
sudo tail -f /www/server/mysql/mysql-error.log

# 重置MySQL密码
sudo bt 10
```

### 4. PHP错误
```bash
# 查看PHP错误日志
sudo tail -f /www/server/php/74/var/log/php-fpm.log

# 重启PHP服务
sudo systemctl restart php-fpm-74
```

### 5. 权限问题
```bash
# 重置网站权限
sudo chown -R www:www /www/wwwroot/your-domain.com
sudo chmod -R 755 /www/wwwroot/your-domain.com
```

## 性能优化建议

### 1. 系统优化
```bash
# 优化系统参数
echo 'vm.swappiness=10' >> /etc/sysctl.conf
echo 'net.core.somaxconn=65535' >> /etc/sysctl.conf
sysctl -p
```

### 2. MySQL优化
```bash
# 编辑MySQL配置文件
vim /etc/my.cnf

# 添加以下配置
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 1000
query_cache_size = 64M
query_cache_type = 1
```

### 3. PHP优化
```bash
# 编辑PHP配置文件
vim /www/server/php/74/etc/php.ini

# 优化配置
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 50M
post_max_size = 50M
```

### 4. Nginx优化
```bash
# 编辑Nginx配置文件
vim /www/server/nginx/conf/nginx.conf

# 优化配置
worker_processes auto;
worker_connections 1024;
keepalive_timeout 65;
```

### 5. 开启缓存
1. 配置Redis缓存
2. 开启Nginx缓存
3. 配置浏览器缓存
4. 使用CDN加速

## 安全建议

### 1. 系统安全
```bash
# 修改SSH端口
vim /etc/ssh/sshd_config
# Port 22 改为其他端口

# 禁用root登录
# PermitRootLogin no

# 重启SSH服务
sudo systemctl restart sshd
```

### 2. 宝塔面板安全
1. 修改默认端口
2. 修改默认用户名和密码
3. 开启面板SSL
4. 绑定域名访问
5. 开启BasicAuth认证

### 3. 网站安全
1. 开启防火墙
2. 定期备份数据
3. 更新系统和软件
4. 安装安全插件

## 备份与恢复

### 1. 网站备份
1. 进入"计划任务"
2. 添加备份任务
3. 设置备份周期和保留天数

### 2. 数据库备份
```bash
# 手动备份数据库
mysqldump -u用户名 -p 数据库名 > 备份文件.sql

# 恢复数据库
mysql -u用户名 -p 数据库名 < 备份文件.sql
```

### 3. 文件备份
```bash
# 打包网站文件
tar -zcvf website_backup.tar.gz /www/wwwroot/your-domain.com

# 解压网站文件
tar -zxvf website_backup.tar.gz -C /www/wwwroot/
```

## 监控与日志

### 1. 系统监控
1. 安装宝塔监控插件
2. 设置告警阈值
3. 配置邮件通知

### 2. 日志查看
```bash
# 系统日志
sudo journalctl -f

# Nginx访问日志
sudo tail -f /www/wwwlogs/your-domain.com.log

# MySQL错误日志
sudo tail -f /www/server/mysql/mysql-error.log
```

## 总结

通过以上步骤，您应该能够在CentOS 7上成功部署宝塔面板并运行SuperAdmin项目。如果在部署过程中遇到问题，可以参考宝塔官方文档或社区寻求帮助。

### 推荐后续操作
1. 定期备份数据
2. 监控系统性能
3. 及时更新系统和软件
4. 优化网站性能
5. 加强安全防护

### 相关链接
- 宝塔官网：https://www.bt.cn/
- 宝塔论坛：https://forum.bt.cn/
- CentOS官方文档：https://docs.centos.org/