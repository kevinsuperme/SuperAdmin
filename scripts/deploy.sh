#!/bin/bash

# 自动化部署脚本

set -e

# 配置变量
PROJECT_NAME="fantastic-admin"
DEPLOY_DIR="/var/www/${PROJECT_NAME}"
BACKUP_DIR="/var/backups/${PROJECT_NAME}"
RELEASE_DIR="${DEPLOY_DIR}/releases"
CURRENT_DIR="${DEPLOY_DIR}/current"
SHARED_DIR="${DEPLOY_DIR}/shared"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查参数
if [ $# -lt 1 ]; then
    log_error "缺少参数，用法: $0 <版本号> [环境]"
    exit 1
fi

VERSION=$1
ENVIRONMENT=${2:-production}

log_info "开始部署 ${PROJECT_NAME} 版本 ${VERSION} 到 ${ENVIRONMENT} 环境"

# 创建必要的目录
mkdir -p ${RELEASE_DIR}
mkdir -p ${BACKUP_DIR}
mkdir -p ${SHARED_DIR}/{storage,logs,config}

# 检查版本文件是否存在
if [ ! -f "${PROJECT_NAME}-${VERSION}.tar.gz" ]; then
    log_error "版本文件 ${PROJECT_NAME}-${VERSION}.tar.gz 不存在"
    exit 1
fi

# 创建新版本目录
NEW_RELEASE_DIR="${RELEASE_DIR}/${VERSION}"
mkdir -p ${NEW_RELEASE_DIR}

# 解压版本文件
log_info "解压版本文件 ${PROJECT_NAME}-${VERSION}.tar.gz"
tar -xzf "${PROJECT_NAME}-${VERSION}.tar.gz" -C ${NEW_RELEASE_DIR}

# 创建共享目录的软链接
log_info "创建共享目录软链接"
ln -sf ${SHARED_DIR}/storage ${NEW_RELEASE_DIR}/runtime
ln -sf ${SHARED_DIR}/logs ${NEW_RELEASE_DIR}/logs
ln -sf ${SHARED_DIR}/config/.env ${NEW_RELEASE_DIR}/.env

# 安装PHP依赖
log_info "安装PHP依赖"
cd ${NEW_RELEASE_DIR}
composer install --no-dev --optimize-autoloader

# 设置文件权限
log_info "设置文件权限"
chmod -R 755 ${NEW_RELEASE_DIR}
chmod -R 777 ${NEW_RELEASE_DIR}/runtime
chmod -R 777 ${NEW_RELEASE_DIR}/public

# 运行数据库迁移
log_info "运行数据库迁移"
php think migrate:run

# 清理缓存
log_info "清理缓存"
php think clear

# 备份当前版本（如果存在）
if [ -L ${CURRENT_DIR} ]; then
    CURRENT_VERSION=$(basename $(readlink ${CURRENT_DIR}))
    log_info "备份当前版本 ${CURRENT_VERSION}"
    cp -r ${CURRENT_DIR} ${BACKUP_DIR}/${CURRENT_VERSION}-$(date +%Y%m%d%H%M%S)
fi

# 切换到新版本
log_info "切换到新版本 ${VERSION}"
ln -sfn ${NEW_RELEASE_DIR} ${CURRENT_DIR}

# 重启服务
log_info "重启服务"
if command -v systemctl &> /dev/null; then
    systemctl reload nginx
    systemctl reload php-fpm
elif command -v service &> /dev/null; then
    service nginx reload
    service php-fpm reload
else
    log_warn "无法自动重启服务，请手动重启nginx和php-fpm"
fi

# 健康检查
log_info "执行健康检查"
sleep 5
if curl -f -s http://localhost/api/health > /dev/null; then
    log_info "健康检查通过"
else
    log_error "健康检查失败，回滚到上一个版本"
    if [ -L ${CURRENT_DIR} ] && [ -n "${CURRENT_VERSION}" ]; then
        ln -sfn ${RELEASE_DIR}/${CURRENT_VERSION} ${CURRENT_DIR}
        log_info "已回滚到版本 ${CURRENT_VERSION}"
    fi
    exit 1
fi

# 清理旧版本（保留最近5个版本）
log_info "清理旧版本"
cd ${RELEASE_DIR}
ls -t | tail -n +6 | xargs -r rm -rf

log_info "部署完成！当前版本: ${VERSION}"