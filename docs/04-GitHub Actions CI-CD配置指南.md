# GitHub Actions CI/CD 配置指南

本文档详细说明如何为 SuperAdmin 项目配置 GitHub Actions 实现自动化测试和部署。

## 目录

- [前置要求](#前置要求)
- [配置 GitHub Secrets](#配置-github-secrets)
- [配置服务器环境](#配置服务器环境)
- [工作流说明](#工作流说明)
- [常见问题](#常见问题)
- [安全最佳实践](#安全最佳实践)

## 前置要求

### 1. GitHub 仓库

确保你的项目已推送到 GitHub 仓库。

### 2. 服务器环境

- **操作系统**: Linux (推荐 Ubuntu 20.04+)
- **PHP**: 8.1+
- **MySQL**: 8.0+
- **Redis**: 6.0+ (可选)
- **Web 服务器**: Nginx 或 Apache
- **SSH 访问**: 配置 SSH 密钥认证

### 3. 本地开发环境

- Git
- Composer
- Node.js 16+
- PHP 8.1+

## 配置 GitHub Secrets

在 GitHub 仓库中配置以下 Secrets (Settings → Secrets and variables → Actions → New repository secret):

### 必需的 Secrets

#### 1. `DEPLOY_HOST`
**说明**: 部署服务器的 IP 地址或域名

**示例值**:
```
192.168.1.100
```
或
```
example.com
```

#### 2. `DEPLOY_USER`
**说明**: SSH 登录用户名

**示例值**:
```
deploy
```

**注意**: 建议创建专门的部署用户,而不是使用 root 用户。

#### 3. `DEPLOY_PATH`
**说明**: 项目在服务器上的绝对路径

**示例值**:
```
/var/www/superadmin
```

#### 4. `DEPLOY_KEY`
**说明**: SSH 私钥内容,用于无密码登录服务器

**生成步骤**:

1. 在本地生成 SSH 密钥对:
```bash
ssh-keygen -t ed25519 -C "deploy@superadmin" -f ~/.ssh/deploy_key
```

2. 将公钥添加到服务器的 `~/.ssh/authorized_keys`:
```bash
ssh-copy-id -i ~/.ssh/deploy_key.pub deploy@your-server
```

3. 查看私钥内容:
```bash
cat ~/.ssh/deploy_key
```

4. 复制完整的私钥内容 (包括 `-----BEGIN` 和 `-----END` 行) 并添加到 GitHub Secret。

**私钥示例**:
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACDxxx...
-----END OPENSSH PRIVATE KEY-----
```

### 可选的 Secrets

#### 5. `DB_PASSWORD`
**说明**: 生产环境数据库密码

如果需要在部署时自动配置数据库连接,可以添加此 Secret。

#### 6. `REDIS_PASSWORD`
**说明**: Redis 密码 (如果使用 Redis)

## 配置服务器环境

### 1. 创建部署用户

```bash
# 创建部署用户
sudo useradd -m -s /bin/bash deploy

# 设置密码 (可选)
sudo passwd deploy

# 添加到 sudo 组 (如果需要)
sudo usermod -aG sudo deploy
```

### 2. 配置 SSH 密钥认证

```bash
# 切换到部署用户
su - deploy

# 创建 .ssh 目录
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# 添加公钥到 authorized_keys
echo "你的公钥内容" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### 3. 创建项目目录

```bash
# 创建项目目录
sudo mkdir -p /var/www/superadmin

# 设置权限
sudo chown -R deploy:deploy /var/www/superadmin
```

### 4. 安装依赖

```bash
# 安装 PHP 和扩展
sudo apt update
sudo apt install -y php8.1-fpm php8.1-mysql php8.1-redis php8.1-gd \
  php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安装 Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 安装 MySQL (如果需要)
sudo apt install -y mysql-server

# 安装 Redis (如果需要)
sudo apt install -y redis-server
```

### 5. 配置 Web 服务器

#### Nginx 配置示例

```bash
# 创建配置文件
sudo nano /etc/nginx/sites-available/superadmin
```

配置内容:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/superadmin/public;
    index index.php index.html;

    # 日志
    access_log /var/log/nginx/superadmin-access.log;
    error_log /var/log/nginx/superadmin-error.log;

    # PHP 处理
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}
```

启用站点:

```bash
# 创建符号链接
sudo ln -s /etc/nginx/sites-available/superadmin /etc/nginx/sites-enabled/

# 测试配置
sudo nginx -t

# 重启 Nginx
sudo systemctl restart nginx
```

### 6. 配置 PHP-FPM

```bash
# 编辑 PHP-FPM 配置
sudo nano /etc/php/8.1/fpm/php.ini
```

关键配置:

```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

重启 PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

## 工作流说明

### 工作流文件

项目的 GitHub Actions 工作流配置在 `.github/workflows/ci-cd.yml`。

### 工作流触发条件

```yaml
on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]
```

- **main 分支**: 推送时触发完整的测试和生产部署
- **develop 分支**: 推送时触发测试和测试环境部署
- **Pull Request**: 仅运行测试,不部署

### 工作流阶段

#### 1. 代码质量检查

```yaml
- name: Check Code Quality
  run: |
    composer require --dev phpstan/phpstan
    vendor/bin/phpstan analyse app
```

**检查内容**:
- PHP 语法错误
- 代码风格 (PSR-12)
- 静态分析 (PHPStan)

#### 2. 运行测试

```yaml
- name: Run Tests
  run: |
    cp .env.example .env
    php think migrate:run
    ./test
```

**测试内容**:
- 单元测试
- 功能测试
- API 测试

#### 3. 构建前端

```yaml
- name: Build Frontend
  run: |
    cd web
    npm install
    npm run build
```

#### 4. 部署

```yaml
- name: Deploy to Server
  run: |
    ./scripts/deploy.sh release.tar.gz ${{ secrets.DEPLOY_PATH }}
```

**部署步骤**:
1. 打包发布文件
2. 上传到服务器
3. 备份当前版本
4. 解压新版本
5. 运行迁移
6. 重启服务

## 常见问题

### Q1: SSH 连接失败

**错误信息**:
```
Permission denied (publickey)
```

**解决方案**:
1. 确认私钥格式正确 (包含完整的 BEGIN 和 END 标记)
2. 确认公钥已添加到服务器的 `authorized_keys`
3. 检查服务器的 SSH 配置:
```bash
sudo nano /etc/ssh/sshd_config
```

确保以下配置正确:
```
PubkeyAuthentication yes
AuthorizedKeysFile .ssh/authorized_keys
```

重启 SSH 服务:
```bash
sudo systemctl restart sshd
```

### Q2: 部署脚本权限不足

**错误信息**:
```
Permission denied: /var/www/superadmin
```

**解决方案**:
```bash
# 设置正确的目录权限
sudo chown -R deploy:deploy /var/www/superadmin
sudo chmod -R 755 /var/www/superadmin
```

### Q3: Composer 安装失败

**错误信息**:
```
Your requirements could not be resolved to an installable set of packages
```

**解决方案**:
```bash
# 更新 Composer
composer self-update

# 清理缓存
composer clear-cache

# 使用国内镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

### Q4: 前端构建失败

**错误信息**:
```
ENOSPC: no space left on device
```

**解决方案**:
```bash
# 增加监视文件数限制
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf
sudo sysctl -p

# 清理 node_modules 和重新安装
cd web
rm -rf node_modules package-lock.json
npm install
```

### Q5: 数据库迁移失败

**错误信息**:
```
SQLSTATE[HY000] [2002] Connection refused
```

**解决方案**:
1. 确认 MySQL 服务运行中:
```bash
sudo systemctl status mysql
```

2. 检查数据库配置:
```bash
mysql -u root -p
CREATE DATABASE IF NOT EXISTS superadmin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON superadmin.* TO 'deploy'@'localhost' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

3. 更新 `.env` 文件中的数据库配置

## 安全最佳实践

### 1. Secrets 管理

- ❌ **不要**在代码中硬编码敏感信息
- ✅ **使用** GitHub Secrets 存储敏感信息
- ✅ **定期轮换** SSH 密钥和密码
- ✅ **限制** Secret 的访问权限

### 2. SSH 密钥

- ✅ **使用** ed25519 算法生成密钥 (更安全)
- ✅ **为每个项目** 创建独立的 SSH 密钥
- ✅ **启用** SSH 密钥密码保护 (本地)
- ❌ **不要** 重复使用个人 SSH 密钥

### 3. 服务器安全

- ✅ **禁用** root 用户 SSH 登录
- ✅ **使用** 防火墙限制访问
- ✅ **定期更新** 系统和软件包
- ✅ **启用** 失败登录限制 (fail2ban)

```bash
# 安装 fail2ban
sudo apt install -y fail2ban

# 配置 fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 4. 应用安全

- ✅ **设置** 合适的文件权限
```bash
# 设置目录权限
find /var/www/superadmin -type d -exec chmod 755 {} \;

# 设置文件权限
find /var/www/superadmin -type f -exec chmod 644 {} \;

# runtime 目录需要写权限
chmod -R 777 /var/www/superadmin/runtime
```

- ✅ **配置** 环境变量而不是硬编码
- ✅ **使用** HTTPS (配置 SSL 证书)
- ✅ **启用** 日志记录和监控

### 5. 备份策略

```bash
# 创建自动备份脚本
sudo nano /usr/local/bin/backup-superadmin.sh
```

备份脚本内容:

```bash
#!/bin/bash

# 配置
BACKUP_DIR="/backups/superadmin"
PROJECT_DIR="/var/www/superadmin"
DB_NAME="superadmin"
DB_USER="deploy"
DB_PASS="your_password"
DATE=$(date +%Y%m%d_%H%M%S)

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# 备份文件
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $PROJECT_DIR .

# 保留最近7天的备份
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

设置定时任务:

```bash
# 添加 crontab
crontab -e

# 每天凌晨2点执行备份
0 2 * * * /usr/local/bin/backup-superadmin.sh >> /var/log/backup.log 2>&1
```

## 监控和告警

### 配置健康检查

```bash
# 创建健康检查脚本
sudo nano /usr/local/bin/health-check.sh
```

脚本内容:

```bash
#!/bin/bash

# 检查应用健康状态
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health/check)

if [ $response -ne 200 ]; then
    echo "Health check failed: HTTP $response"
    # 发送告警邮件或其他通知
    echo "SuperAdmin health check failed" | mail -s "Alert: SuperAdmin Down" admin@example.com
fi
```

设置定时检查:

```bash
# 每5分钟检查一次
*/5 * * * * /usr/local/bin/health-check.sh
```

## 总结

通过本指南,你应该能够成功配置 GitHub Actions 实现 SuperAdmin 的自动化测试和部署。记住:

1. ✅ 妥善保管所有 Secrets
2. ✅ 遵循安全最佳实践
3. ✅ 定期备份数据
4. ✅ 监控系统健康状态
5. ✅ 保持系统和依赖更新

如有问题,请参考:
- [GitHub Actions 文档](https://docs.github.com/en/actions)
- [SuperAdmin 官方文档](https://doc.superadmin.com)
- [项目 Issue](https://github.com/kevinsuperme/SuperAdmin/issues)

## 相关文档

- [CI/CD 流程说明](../CI-CD.md)
- [部署脚本说明](../scripts/README.md)
- [系统架构文档](01-项目技术架构评估与规划.md)
- [v2.4.0 新特性](09-V2.4.0功能特性.md)