# SuperAdmin - 企业级后台管理系统

<div align="center">
    <img src="https://doc.superadmin.com/images/logo.png" alt="SuperAdmin Logo" width="120" />
    <h1 style="font-size: 36px;color: #2c3e50;font-weight: 600;margin: 0 0 6px 0;">SuperAdmin</h1>
    <p style="font-size: 17px;color: #6a8bad;margin-bottom: 10px;">基于现代技术栈的企业级后台管理系统框架</p>
    <a href="https://uni.superadmin.com" target="_blank">官网</a> |
    <a href="https://demo.superadmin.com" target="_blank">演示</a> |
    <a href="https://ask.superadmin.com" target="_blank">社区</a> |
    <a href="https://doc.superadmin.com/" target="_blank">文档</a> |
    <a href="http://qm.qq.com/cgi-bin/qm/qr?_wv=1027&k=paVQA1dlpsVNHTla-ZAts6e4pPK4va9R&authKey=Eto0dq9DOuYldJPl6URFAXXHlG2AFQtPUBxNHEByEiuSg9OraxMniXIaWFt46OKi&noverify=0&group_code=1039646575" target="_blank">加群</a> |
    <a href="https://doc.superadmin.com/guide/" target="_blank">视频介绍</a> |
    <a href="https://github.com/kevinsuperme/SuperAdmin" target="_blank">GitHub仓库</a>
</div>

<p align="center">
    <a href="https://www.thinkphp.cn/" target="_blank">
        <img src="https://img.shields.io/badge/ThinkPHP-%3E8.1-brightgreen?color=91aac3&labelColor=439EFD" alt="ThinkPHP">
    </a>
    <a href="https://v3.vuejs.org/" target="_blank">
        <img src="https://img.shields.io/badge/Vue-3.5.22-brightgreen?color=91aac3&labelColor=439EFD" alt="Vue">
    </a>
    <a href="https://element-plus.org/zh-CN/guide/changelog.html" target="_blank">
        <img src="https://img.shields.io/badge/Element--Plus-%3E2.9-brightgreen?color=91aac3&labelColor=439EFD" alt="Element Plus">
    </a>
    <a href="https://www.tslang.cn/" target="_blank">
        <img src="https://img.shields.io/badge/TypeScript-%3E5.7-blue?color=91aac3&labelColor=439EFD" alt="TypeScript">
    </a>
    <a href="https://vitejs.dev/" target="_blank">
        <img src="https://img.shields.io/badge/Vite-%3E6.0-blue?color=91aac3&labelColor=439EFD" alt="Vite">
    </a>
    <a href="https://pinia.vuejs.org/" target="_blank">
        <img src="https://img.shields.io/badge/Pinia-%3E2.3-blue?color=91aac3&labelColor=439EFD" alt="Pinia">
    </a>
    <a href="https://github.com/kevinsuperme/SuperAdmin/blob/master/LICENSE" target="_blank">
        <img src="https://img.shields.io/badge/Apache2.0-license-blue?color=91aac3&labelColor=439EFD" alt="License">
    </a>
</p>

<br>
<div align="center">
  <img src="https://doc.superadmin.com/images/readme/dashboard-radius.png" alt="SuperAdmin Dashboard" />
</div>
<br>

## 目录

- [项目概述](#项目概述)
- [技术架构](#技术架构)
  - [整体架构](#整体架构)
  - [前端架构](#前端架构)
  - [后端架构](#后端架构)
  - [数据层架构](#数据层架构)
- [核心特性](#核心特性)
- [技术栈](#技术栈)
- [系统要求](#系统要求)
- [快速开始](#快速开始)
- [架构设计](#架构设计)
  - [分层架构](#分层架构)
  - [Service层架构](#service层架构)
  - [认证与授权](#认证与授权)
  - [数据安全](#数据安全)
- [开发指南](#开发指南)
- [部署指南](#部署指南)
- [性能优化](#性能优化)
- [安全架构](#安全架构)
- [扩展性设计](#扩展性设计)
- [常见问题](#常见问题)
- [贡献指南](#贡献指南)
- [开源协议](#开源协议)

## 项目概述

SuperAdmin 是一个基于现代技术栈构建的企业级后台管理系统框架，采用前后端分离架构，提供完整的RBAC权限管理、用户管理、数据安全等企业级功能。系统设计遵循SOLID原则，采用分层架构模式，确保代码的可维护性和可扩展性。

### 设计理念

- **企业级标准**：遵循企业级应用开发最佳实践
- **前后端分离**：清晰的职责划分，支持独立部署和扩展
- **安全第一**：内置多层次安全防护机制
- **高性能**：支持常驻内存运行，提供卓越性能
- **易于扩展**：模块化设计，支持插件和主题扩展
- **开发友好**：提供完善的开发工具和文档

## 技术架构

### 整体架构

```
┌─────────────────────────────────────────────────────────────┐
│                        前端层 (Vue 3)                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │  表现层     │  │  组件层     │  │  状态管理   │         │
│  │   Views     │  │ Components  │  │   Pinia     │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP/HTTPS
                              │
┌─────────────────────────────────────────────────────────────┐
│                      后端层 (ThinkPHP 8)                     │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │  控制器层   │  │  服务层     │  │  模型层     │         │
│  │ Controller  │  │  Service    │  │   Model     │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
                              │
                              │
┌─────────────────────────────────────────────────────────────┐
│                        数据层                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │    MySQL    │  │    Redis    │  │  文件存储   │         │
│  │   主数据库   │  │   缓存      │  │  Filesystem │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

### 前端架构

前端采用Vue 3生态系统，结合TypeScript提供类型安全，使用Vite作为构建工具实现快速开发。

```
web/src/
├── api/              # API接口层
├── assets/           # 静态资源
├── components/       # 公共组件
│   ├── table/        # 表格组件
│   ├── form/         # 表单组件
│   └── icon/         # 图标组件
├── layouts/          # 布局组件
├── router/           # 路由配置
├── stores/           # 状态管理
├── styles/           # 样式文件
├── utils/            # 工具函数
└── views/            # 页面视图
```

**核心特性**：
- 组件化开发，提高代码复用性
- 路由懒加载，优化首屏加载速度
- 状态管理集中化，数据流清晰
- TypeScript支持，提供类型安全
- 响应式设计，适配多种设备

### 后端架构

后端基于ThinkPHP 8框架，采用MVC+Service分层架构，确保业务逻辑与数据访问分离。

```
app/
├── admin/            # 后台管理模块
│   ├── controller/   # 控制器
│   ├── model/        # 模型
│   └── validate/     # 验证器
├── api/              # API接口模块
│   ├── controller/   # 控制器
│   └── validate/     # 验证器
└── common/           # 公共模块
    ├── controller/   # 基础控制器
    ├── model/        # 公共模型
    ├── service/      # 业务逻辑层
    ├── library/       # 公共类库
    └── middleware/    # 中间件
```

**核心特性**：
- 分层架构，职责清晰
- 服务层封装业务逻辑
- 中间件机制，横切关注点
- 依赖注入，降低耦合度
- 验证器层，数据验证统一管理

### 数据层架构

数据层采用MySQL作为主数据库，Redis作为缓存和会话存储，支持文件存储系统。

```
┌─────────────────────────────────────────────────────────────┐
│                        数据层架构                            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐      ┌──────────────┐      ┌──────────┐  │
│  │    MySQL     │      │    Redis     │      │  文件存储  │  │
│  │   主数据库    │◄────►│   缓存/会话   │◄────►│文件/图片  │  │
│  │              │      │              │      │          │  │
│  │ - 用户数据    │      │ - 会话存储    │      │ - 附件管理│  │
│  │ - 权限数据    │      │ - 缓存数据    │      │ - 备份文件│  │
│  │ - 业务数据    │      │ - 队列任务    │      │          │  │
│  └──────────────┘      └──────────────┘      └──────────┘  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 核心特性

### 🔥 最新特性 (v2.4.0)
- **CI/CD 自动化部署**: GitHub Actions 自动化测试、构建和部署流程
- **健康检查 API**: 完整的系统健康状态检测接口
- **API 文档生成器**: 自动生成和管理 API 文档
- **增强认证中间件**: 支持多端认证和 JWT Token 刷新
- **限流中间件**: API 访问频率限制保护
- **单元测试框架**: PHPUnit 测试环境配置和示例

### 🚀 CRUD 代码生成
- 图形化拖拽生成后台增删改查代码
- 自动创建数据表结构
- 24种表单组件支持
- 行拖拽排序功能
- 权限控制的编辑和删除
- 支持关联表操作

### 💥 内置 WEB 终端
- 深度集成于本地开发环境
- 自动执行安装和构建命令
- 支持调用环境变量中的任意命令
- 代码生成后自动格式化
- 可扩展的终端命令系统

### 👍 现代化技术栈
- 基于ThinkPHP 8前后端分离架构
- Vue 3.5.22 + Composition API
- TypeScript提供类型安全
- Pinia状态管理
- Vite构建工具
- Element Plus UI框架

### 🎨 模块市场
- 一键安装功能模块
- 自动维护依赖关系
- 支持数据导入导出
- 短信发送集成
- 支付系统集成
- 云存储支持
- 富文本编辑器

### 🔀 前后端分离
- 独立的前后端代码库
- 支持独立部署
- RESTful API设计
- 统一的接口规范
- 前端开发者友好

### ⚡️ 常驻内存
- 基于Workerman的常驻内存服务
- HTTP服务性能提升数十倍
- WebSocket服务支持
- 热重载开发体验

### 🚚 按需加载
- 前端组件和语言包异步加载
- 服务端基于PSR规范的按需加载
- 无需使用的功能可隐藏
- 资源使用效率高

### 🌴 数据安全
- 全局数据回收站
- 字段级数据修改记录
- 数据修改对比
- 一键数据回滚
- 操作日志追踪

### ✨ 高颜值UI
- 三种布局模式
- 无边框设计风格
- 悬浮式功能版块
- 合理的屏幕空间利用
- 响应式设计

### 🔐 权限管理
- 可视化权限管理
- 动态路由注册
- 菜单权限控制
- 按钮级权限控制
- 无限父子级权限分组
- 前后端双重鉴权

### 🏗️ Service层架构
- 业务逻辑从Controller分离
- BaseService基础服务类
- 事务管理统一处理
- 异常处理机制
- 依赖注入支持
- 代码结构清晰

## 技术栈

### 后端技术栈

| 技术 | 版本 | 说明 |
|-----|------|------|
| **框架** | ThinkPHP 8.x | PHP企业级开发框架 |
| **语言** | PHP 8.0+ | 服务端编程语言 |
| **架构模式** | MVC + Service | 分层架构模式 |
| **数据库** | MySQL 5.7+ / MariaDB 10.2+ | 关系型数据库 |
| **ORM** | ThinkPHP ORM | 数据库抽象层 |
| **数据库迁移** | Phinx | 数据库版本控制 |
| **缓存** | Redis / 文件缓存 | 高性能缓存系统 |
| **队列** | ThinkPHP Queue | 异步任务处理 |
| **认证** | JWT | 无状态认证 |
| **测试框架** | PHPUnit | 单元测试和功能测试 |
| **CI/CD** | GitHub Actions | 自动化测试和部署 |

### 前端技术栈

| 技术 | 版本 | 说明 |
|-----|------|------|
| **框架** | Vue 3.5.22 | 渐进式JavaScript框架 |
| **语言** | TypeScript 5.x+ | JavaScript超集 |
| **构建工具** | Vite 6.x+ | 下一代前端构建工具 |
| **UI框架** | Element Plus 2.x+ | Vue 3组件库 |
| **状态管理** | Pinia 2.x+ | Vue状态管理库 |
| **路由** | Vue Router 4.x+ | 官方路由管理器 |
| **HTTP客户端** | Axios | Promise based HTTP客户端 |
| **代码规范** | ESLint, Prettier | 代码质量和格式化工具 |
| **CSS预处理器** | Sass/SCSS | CSS扩展语言 |
| **图表库** | ECharts | 数据可视化库 |
| **工具库** | Lodash, VueUse | 实用工具库 |

## 系统要求

### 服务器环境

| 组件 | 最低要求 | 推荐配置 |
|-----|---------|---------|
| **PHP** | >= 8.0 | 8.1+ |
| **Web服务器** | Apache / Nginx / IIS | Nginx 1.18+ |
| **数据库** | MySQL >= 5.7 或 MariaDB >= 10.2 | MySQL 8.0+ |
| **缓存** | Redis (可选) | Redis 6.0+ |
| **内存** | PHP >= 128M | PHP >= 256M |
| **上传大小** | PHP >= 20M | PHP >= 50M |
| **执行时间** | PHP >= 300s | PHP >= 300s |

### PHP扩展要求

- PDO (数据库连接)
- Mbstring (多字节字符串处理)
- OpenSSL (加密功能)
- JSON (JSON处理)
- Curl (HTTP客户端)
- GD (图像处理)
- Fileinfo (文件信息)
- BCMath (精确数学计算)
- Iconv (字符集转换)

### 开发环境

| 工具 | 最低要求 | 推荐配置 |
|-----|---------|---------|
| **包管理器** | Composer >= 2.0 | Composer 2.5+ |
| **Node.js** | >= 16.0 | 18.0+ |
| **npm** | >= 8.0 | 9.0+ |
| **yarn** | >= 1.22 | 1.22+ |

## 快速开始

### 环境检查

在开始之前,请确保您的环境满足[系统要求](#系统要求)。可以使用健康检查 API 验证环境配置:

```bash
# 启动开发服务器后访问
curl http://localhost:8000/api/health/check
```

### 1. 克隆项目

```bash
git clone https://github.com/kevinsuperme/SuperAdmin.git
cd SuperAdmin
```

### 2. 安装后端依赖

```bash
composer install
```

### 3. 配置环境变量

```bash
cp .env-example .env
# 编辑 .env 文件，配置数据库连接等信息
```

### 4. 安装前端依赖

```bash
cd web
npm install
```

### 5. 启动开发服务器

```bash
# 后端服务
php think run

# 前端服务(新开终端)
cd web
npm run dev
```

### 6. 访问安装向导

```
前端: http://localhost:5173
后端: http://localhost:8000
安装向导: http://localhost:8000/install
健康检查: http://localhost:8000/api/health/check
API 文档: http://localhost:5173/admin/api-doc
```

### 7. 运行测试(可选)

```bash
# 运行所有测试
./test

# Windows 环境
test.bat

# 运行特定测试
./vendor/bin/phpunit tests/Unit/AuthServiceTest.php
```

## 架构设计

### 分层架构

SuperAdmin采用经典的分层架构模式，确保各层职责清晰，降低系统耦合度。

```
┌─────────────────────────────────────────────────────────────┐
│                      表现层 (Presentation)                    │
│                    Vue 3 + Element Plus                     │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  页面组件  │  布局组件  │  业务组件  │  公共组件    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP/JSON API
                              │
┌─────────────────────────────────────────────────────────────┐
│                      控制器层 (Controller)                    │
│                    ThinkPHP 8 Controllers                    │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  请求接收  │  参数验证  │  调用服务  │  响应返回    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ 业务调用
                              │
┌─────────────────────────────────────────────────────────────┐
│                       服务层 (Service)                       │
│                   Business Logic Layer                       │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  业务逻辑  │  事务管理  │  权限控制  │  缓存处理    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ 数据访问
                              │
┌─────────────────────────────────────────────────────────────┐
│                       数据访问层 (Model)                      │
│                    ThinkPHP 8 Models                        │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  数据映射  │  关联定义  │  数据验证  │  软删除      │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ 持久化
                              │
┌─────────────────────────────────────────────────────────────┐
│                        数据存储层                            │
│                  MySQL + Redis + Filesystem                 │
└─────────────────────────────────────────────────────────────┘
```

### Service层架构

Service层是系统的核心业务逻辑层，负责处理复杂的业务规则、事务管理和数据处理。

#### 核心组件

1. **BaseService**: 基础服务类，提供通用CRUD操作
2. **UserService**: 用户管理服务
3. **AuthService**: 认证服务
4. **MenuService**: 菜单管理服务
5. **LogService**: 日志管理服务

#### 设计原则

- **单一职责**: 每个服务类只负责一个业务领域
- **依赖注入**: 服务间通过构造函数注入依赖
- **事务管理**: 复杂操作使用事务确保数据一致性
- **异常处理**: 统一的异常处理机制

#### 示例代码

```php
<?php
namespace app\common\service;

use app\common\model\User;
use think\facade\Db;
use think\facade\Cache;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = new User();
    }
    
    /**
     * 创建用户
     */
    public function createUser(array $data): User
    {
        // 验证用户名唯一性
        if ($this->usernameExists($data['username'])) {
            throw new \Exception('用户名已存在');
        }
        
        // 密码加密
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return Db::transaction(function() use ($data) {
            // 创建用户
            $user = $this->create($data);
            
            // 分配默认角色
            $this->assignDefaultRole($user->id);
            
            // 记录日志
            LogService::record('创建用户', $user->toArray());
            
            // 清除相关缓存
            $this->clearUserCache($user->id);
            
            return $user;
        });
    }
    
    /**
     * 检查用户名是否存在
     */
    public function usernameExists(string $username): bool
    {
        $cacheKey = "user:username:{$username}";
        
        return Cache::remember($cacheKey, function() use ($username) {
            return $this->model->where('username', $username)->count() > 0;
        }, 3600);
    }
}
```

### 认证与授权

系统采用基于JWT的无状态认证机制，支持双端认证（管理员端和用户端）。

#### 认证流程

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│    客户端    │      │   后端API    │      │    Redis    │
│             │      │             │      │             │
│ 1.登录请求  ├─────►│ 2.验证凭据   │             │             │
│             │      │             │             │             │
│             │◄─────┤ 3.生成Token  │             │             │
│             │      │             │             │             │
│ 4.存储Token │      │             ├─────►│ 5.存储Token  │
│             │      │             │      │             │
│ 6.携带Token ├─────►│ 7.验证Token  │             │             │
│             │      │             │             │             │
│             │◄─────┤ 8.返回数据   │             │             │
└─────────────┘      └─────────────┘      └─────────────┘
```

#### 权限控制

系统实现了四层权限控制：

1. **路由权限**: URL级别的访问控制
2. **菜单权限**: 菜单显示控制
3. **按钮权限**: 页面按钮级控制
4. **数据权限**: 行级数据访问控制

#### 数据权限实现

```php
// 数据权限配置
protected bool|string|int $dataLimit = false;

// 权限级别:
// false           - 关闭数据限制
// 'personal'      - 仅限个人数据
// 'allAuth'       - 拥有某管理员所有权限时
// 'allAuthAndOthers' - 拥有某管理员所有权限并有其他权限
// 'parent'        - 上级分组管理员可查
// 数字 (如 2)      - 指定分组管理员可查

// 查询构建器自动添加数据权限过滤
$dataLimitAdminIds = $this->getDataLimitAdminIds();
if ($dataLimitAdminIds) {
    $where[] = [$mainTableAlias . $this->dataLimitField, 'in', $dataLimitAdminIds];
}
```

### 数据安全

系统内置了完善的数据安全机制，包括敏感数据审计和数据回收站功能。

#### 敏感数据审计

- 自动记录敏感字段修改前后值
- 支持数据回滚
- 操作日志追踪

#### 数据回收站

- 软删除实现
- 数据恢复
- 自动清理过期数据

## 开发指南

### 环境搭建

1. **安装PHP环境**
   ```bash
   # Windows
   # 下载并安装PHP 8.1+
   
   # macOS
   brew install php@8.1
   
   # Linux (Ubuntu)
   sudo apt update
   sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-redis
   ```

2. **安装Composer**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   mv composer.phar /usr/local/bin/composer
   ```

3. **安装Node.js**
   ```bash
   # 使用nvm安装
   curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
   nvm install 18
   nvm use 18
   ```

4. **安装Redis**
   ```bash
   # Ubuntu
   sudo apt install redis-server
   
   # macOS
   brew install redis
   ```

### 代码规范

#### PHP代码规范

遵循PSR-1、PSR-2、PSR-4编码规范：

```php
<?php
namespace app\admin\controller;

use app\BaseController;
use app\admin\service\UserService;
use think\facade\Log;

class User extends BaseController
{
    protected UserService $userService;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->userService = new UserService();
    }
    
    /**
     * 获取用户列表
     */
    public function index(): void
    {
        $params = $this->request->get();
        
        try {
            $list = $this->userService->getUserList($params);
            $this->success('获取成功', $list);
        } catch (\Throwable $e) {
            Log::error('获取用户列表失败: ' . $e->getMessage());
            $this->error('获取失败');
        }
    }
}
```

#### TypeScript代码规范

```typescript
// API接口定义
interface ApiResponse<T = any> {
    success: boolean;
    code: string;
    message: string;
    data: T;
    timestamp: number;
}

// 用户类型定义
interface User {
    id: number;
    username: string;
    email: string;
    status: 'enabled' | 'disabled';
    createTime: number;
}

// API请求函数
export function getUserList(params: any): Promise<ApiResponse<User[]>> {
    return createAxios({
        url: 'admin/user/index',
        method: 'get',
        params,
    });
}
```

### 测试指南

#### 1. 运行测试

```bash
# 运行所有测试
./test

# Windows 环境
test.bat

# 运行特定测试套件
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature

# 运行单个测试文件
./vendor/bin/phpunit tests/Unit/AuthServiceTest.php

# 带代码覆盖率报告
./vendor/bin/phpunit --coverage-html coverage
```

#### 2. 编写测试

```php
<?php
namespace tests\Unit;

use tests\TestCase;
use app\common\library\Auth;

class AuthServiceTest extends TestCase
{
    public function testLogin()
    {
        $auth = new Auth();
        $result = $auth->login('admin', 'password');
        $this->assertTrue($result);
    }
}
```

#### 3. API 测试

```php
<?php
namespace tests\Feature;

use tests\TestCase;

class UserApiTest extends TestCase
{
    public function testUserList()
    {
        $response = $this->get('/api/user/index');
        $response->assertSuccessful();
        $this->assertArrayHasKey('data', $response->json());
    }
}
```

### API 文档生成

系统提供自动化的 API 文档生成功能:

```php
// 在控制器中添加文档注释
/**
 * @title 获取用户列表
 * @description 获取分页的用户列表数据
 * @auth true
 * @method GET
 * @param page int 页码
 * @param limit int 每页数量
 * @return array {
 *   "code": 1,
 *   "message": "success",
 *   "data": {
 *     "list": [],
 *     "total": 0
 *   }
 * }
 */
public function index()
{
    // 实现代码
}
```

访问 `/admin/api-doc` 查看生成的 API 文档。

### 健康检查

系统提供了完整的健康检查接口:

```bash
# 基础健康检查
curl http://localhost:8000/api/health/check

# 详细系统信息
curl http://localhost:8000/api/health/info

# 响应示例
{
  "status": "healthy",
  "timestamp": "2025-10-26T08:00:00+08:00",
  "services": {
    "database": "ok",
    "cache": "ok",
    "filesystem": "ok"
  },
  "system": {
    "php_version": "8.1.0",
    "memory_usage": "15.2MB",
    "disk_usage": "45%"
  }
}
```

### CI/CD 配置

项目内置了 GitHub Actions 配置,支持自动化测试和部署:

```yaml
# .github/workflows/ci-cd.yml
name: CI/CD

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Run Tests
        run: ./vendor/bin/phpunit
```

### 调试技巧

#### 后端调试

1. **开启调试模式**
   ```php
   // .env文件
   APP_DEBUG = true
   ```

2. **使用ThinkPHP调试工具**
   ```php
   // 打印变量
   dump($variable);
   
   // 中断执行并打印
   halt($variable);
   ```

3. **日志记录**
   ```php
   use think\facade\Log;
   
   // 记录信息
   Log::info('用户登录', ['user_id' => $userId]);
   
   // 记录错误
   Log::error('数据库错误', ['error' => $e->getMessage()]);
   ```

#### 前端调试

1. **使用Vue DevTools**
   - 安装浏览器扩展
   - 检查组件状态和Vuex状态

2. **控制台调试**
   ```javascript
   // 打印变量
   console.log(variable);
   
   // 条件断点
   debugger;
   ```

3. **网络请求调试**
   ```javascript
   // 请求拦截器
   axios.interceptors.request.use(config => {
       console.log('请求配置:', config);
       return config;
   });
   
   // 响应拦截器
   axios.interceptors.response.use(response => {
       console.log('响应数据:', response);
       return response;
   });
   ```

## 部署指南

### 单机部署

#### 环境准备

1. **安装LNMP环境**
   ```bash
   # Nginx
   sudo apt install nginx
   
   # MySQL
   sudo apt install mysql-server
   
   # PHP
   sudo apt install php8.1-fpm php8.1-mysql php8.1-redis php8.1-gd
   ```

2. **配置Nginx**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/superadmin/public;
       index index.php index.html;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

3. **配置PHP-FPM**
   ```ini
   ; /etc/php/8.1/fpm/php.ini
   memory_limit = 256M
   upload_max_filesize = 50M
   post_max_size = 50M
   max_execution_time = 300
   ```

#### 部署步骤

1. **克隆代码**
   ```bash
   git clone https://github.com/kevinsuperme/SuperAdmin.git /var/www/superadmin
   cd /var/www/superadmin
   ```

2. **安装依赖**
   ```bash
   composer install --no-dev --optimize-autoloader
   cd web && npm install && npm run build
   ```

3. **配置环境**
   ```bash
   cp .env-example .env
   php think key:generate
   ```

4. **设置权限**
   ```bash
   chown -R www-data:www-data /var/www/superadmin
   chmod -R 755 runtime/ public/
   ```

5. **初始化数据库**
   ```bash
   php think migrate:run
   ```

### 分布式部署

#### 架构设计

```
┌─────────────────────────────────────────────────────────────┐
│                        负载均衡层                            │
│                      Nginx/HAProxy                         │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      Web服务器集群                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │  Web Server  │  │  Web Server  │  │  Web Server  │         │
│  │     #1       │  │     #2       │  │     #3       │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      数据库集群                             │
│  ┌─────────────┐      ┌─────────────┐      ┌─────────────┐ │
│  │   Master    │◄────►│    Slave    │◄────►│    Slave    │ │
│  │   MySQL     │      │   MySQL     │      │   MySQL     │ │
│  └─────────────┘      └─────────────┘      └─────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      缓存集群                               │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Redis     │  │   Redis     │  │   Redis     │         │
│  │  Master     │  │   Slave     │  │   Slave     │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

#### 配置要点

1. **会话共享**
   ```php
   // config/session.php
   'type'       => 'redis',
   'host'       => 'redis-cluster',
   'port'       => 6379,
   'password'   => '',
   'select'     => 0,
   'timeout'    => 0,
   'expire'     => 86400,
   'persistent' => false,
   ```

2. **数据库读写分离**
   ```php
   // config/database.php
   'connections' => [
       'mysql' => [
           // 主库配置
           'master' => [
               'hostname' => 'mysql-master',
               'database' => 'superadmin',
               'username' => 'root',
               'password' => 'password',
           ],
           // 从库配置
           'slave' => [
               'hostname' => 'mysql-slave',
               'database' => 'superadmin',
               'username' => 'root',
               'password' => 'password',
           ],
       ],
   ],
   ```

### 容器化部署

#### Dockerfile

```dockerfile
FROM php:8.1-fpm

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

# 安装PHP扩展
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www

# 复制应用代码
COPY . /var/www

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 构建前端
RUN cd web && npm install && npm run build

# 设置权限
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/runtime /var/www/public

# 复制Nginx配置
COPY docker/nginx.conf /etc/nginx/sites-available/default

# 启动脚本
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
```

#### Docker Compose

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "80:80"
    environment:
      - APP_ENV=production
    depends_on:
      - mysql
      - redis
    volumes:
      - ./runtime:/var/www/runtime
      - ./public/uploads:/var/www/public/uploads

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: superadmin
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:6.2-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

volumes:
  mysql_data:
  redis_data:
```

## 性能优化

### 后端优化

#### 1. PHP优化

```ini
; php.ini
; 开启OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1

; 内存限制
memory_limit=256M

; 执行时间
max_execution_time=300
```

#### 2. 数据库优化

```sql
-- 添加索引
ALTER TABLE `admin` ADD INDEX idx_status_create_time (`status`, `create_time`);
ALTER TABLE `user` ADD INDEX idx_group_status (`group_id`, `status`);

-- 查询优化
EXPLAIN SELECT * FROM `admin` WHERE `status` = 1 ORDER BY `create_time` DESC LIMIT 10;

-- 分区表（大数据量）
ALTER TABLE `admin_log` PARTITION BY RANGE (createtime) (
    PARTITION p202401 VALUES LESS THAN (UNIX_TIMESTAMP('2024-02-01')),
    PARTITION p202402 VALUES LESS THAN (UNIX_TIMESTAMP('2024-03-01')),
    PARTITION pmax VALUES LESS THAN MAXVALUE
);
```

#### 3. 缓存策略

```php
// 缓存配置
'cache' => [
    'default' => 'redis',
    'stores'  => [
        'redis' => [
            'type'   => 'redis',
            'host'   => '127.0.0.1',
            'port'   => '6379',
            'password' => '',
            'select' => '0',
            'timeout' => 0,
            'expire'  => 0,
            'persistent' => false,
            'prefix'  => 'superadmin:',
        ],
    ],
],

// 缓存使用示例
use think\facade\Cache;

// 带缓存的数据查询
public function getUserList(array $where = []): array
{
    $cacheKey = 'user:list:' . md5(serialize($where));
    
    return Cache::remember($cacheKey, function() use ($where) {
        return $this->model->where($where)->select()->toArray();
    }, 3600);
}
```

### 前端优化

#### 1. 构建优化

```javascript
// vite.config.ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  build: {
    // 代码分割
    rollupOptions: {
      output: {
        manualChunks: {
          'element-plus': ['element-plus'],
          'vue-vendor': ['vue', 'vue-router', 'pinia'],
          'utils': ['axios', 'lodash-es'],
        },
      },
    },
    // 压缩选项
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
  },
  // 服务器配置
  server: {
    hmr: {
      overlay: false,
    },
  },
});
```

#### 2. 路由懒加载

```typescript
// router/index.ts
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/dashboard/index.vue'),
    meta: { title: '仪表盘' },
  },
  {
    path: '/user',
    name: 'User',
    component: () => import('@/views/user/index.vue'),
    meta: { title: '用户管理' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
```

#### 3. 组件懒加载

```vue
<template>
  <div>
    <Suspense>
      <template #default>
        <LazyComponent />
      </template>
      <template #fallback>
        <div>Loading...</div>
      </template>
    </Suspense>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent } from 'vue';

const LazyComponent = defineAsyncComponent(() => 
  import('@/components/HeavyComponent.vue')
);
</script>
```

### 系统优化

#### 1. 服务器配置

```nginx
# nginx.conf
worker_processes auto;
worker_connections 1024;

http {
    # 开启gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    
    # 开启缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # 连接保持
    keepalive_timeout 65;
    keepalive_requests 100;
    
    # 缓冲区大小
    client_body_buffer_size 128k;
    client_max_body_size 50m;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
}
```

#### 2. 常驻内存运行

```php
// 使用Workerman实现常驻内存
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

// 创建一个Worker监听8080端口，使用http协议通讯
$http_worker = new Worker('http://0.0.0.0:8080');

// 启动4个进程对外提供服务
$http_worker->count = 4;

// 接收到浏览器发送的数据时回复hello world给浏览器
$http_worker->onMessage = function(TcpConnection $connection, Request $request) {
    // 加载ThinkPHP应用
    $response = think\Response::create($request->path());
    $connection->send($response);
};

// 运行worker
Worker::runAll();
```

## 安全架构

### 认证安全

#### 1. JWT认证

```php
// JWT配置
'jwt' => [
    'secret' => env('JWT_SECRET', 'your-secret-key'),
    'expire' => env('JWT_EXPIRE', 86400), // 24小时
    'refresh_expire' => env('JWT_REFRESH_EXPIRE', 2592000), // 30天
],

// JWT生成
public function generateToken(array $payload): string
{
    $payload['iat'] = time();
    $payload['exp'] = time() + $this->expire;
    
    return JWT::encode($payload, $this->secret, 'HS256');
}

// JWT验证
public function verifyToken(string $token): array
{
    try {
        $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
        return (array)$decoded;
    } catch (\Exception $e) {
        throw new \Exception('Token无效');
    }
}
```

#### 2. 密码安全

```php
// 密码加密
public function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost'   => 4,
        'threads'     => 3,
    ]);
}

// 密码验证
public function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}
```

### 数据安全

#### 1. XSS防护

```php
// 输入过滤
use voku\anti-xss\AntiXSS;

$antiXss = new AntiXSS();
$cleanInput = $antiXss->xss_clean($input);

// 输出转义
htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
```

#### 2. SQL注入防护

```php
// 使用参数绑定
$user = Db::name('user')->where('id', $userId)->find();

// 使用预处理语句
$sql = "SELECT * FROM user WHERE username = ? AND password = ?";
$result = Db::query($sql, [$username, $password]);
```

#### 3. CSRF防护

```php
// 中间件验证
class CsrfTokenMiddleware
{
    public function handle($request, \Closure $next)
    {
        if ($request->isPost()) {
            $token = $request->param('__token__');
            if (!$token || !token($token)) {
                throw new \Exception('CSRF令牌验证失败');
            }
        }
        
        return $next($request);
    }
}
```

### 接口安全

#### 1. 请求限流

```php
// 使用ThinkPHP限流中间件
'throttle' => [
    'visit_rate'    => '60/m', // 每分钟60次
    'visit_enable'   => true,
    'visit_methods'  => ['GET', 'POST'],
],
```

#### 2. IP白名单

```php
// IP白名单中间件
class IpWhitelistMiddleware
{
    protected $allowedIps = [
        '127.0.0.1',
        '192.168.1.100',
    ];
    
    public function handle($request, \Closure $next)
    {
        $ip = $request->ip();
        
        if (!in_array($ip, $this->allowedIps)) {
            throw new \Exception('IP地址不在白名单中');
        }
        
        return $next($request);
    }
}
```

## 扩展性设计

### 模块化设计

系统采用模块化设计，支持功能模块的独立开发和部署。

```
modules/
├── user/                 # 用户模块
│   ├── controller/       # 控制器
│   ├── model/           # 模型
│   ├── service/         # 服务
│   ├── validate/        # 验证器
│   └── config/          # 配置
├── order/               # 订单模块
├── payment/             # 支付模块
└── notification/        # 通知模块
```

### 插件系统

系统支持插件扩展，可以在不修改核心代码的情况下添加新功能。

```php
// 插件基类
abstract class Plugin
{
    abstract public function getName(): string;
    abstract public function getVersion(): string;
    abstract public function install(): bool;
    abstract public function uninstall(): bool;
    abstract public function enable(): bool;
    abstract public function disable(): bool;
}

// 插件管理器
class PluginManager
{
    protected array $plugins = [];
    
    public function register(Plugin $plugin): void
    {
        $this->plugins[$plugin->getName()] = $plugin;
    }
    
    public function getPlugin(string $name): ?Plugin
    {
        return $this->plugins[$name] ?? null;
    }
    
    public function install(string $name): bool
    {
        $plugin = $this->getPlugin($name);
        if ($plugin) {
            return $plugin->install();
        }
        return false;
    }
}
```

### 主题系统

系统支持多主题切换，满足不同场景的UI需求。

```
web/src/themes/
├── default/             # 默认主题
│   ├── index.scss       # 主题样式
│   ├── variables.scss   # 变量定义
│   └── components/      # 组件样式
├── dark/                # 暗色主题
└── custom/              # 自定义主题
```

## 常见问题

### Q: 如何自定义Service类？

A: 创建Service类并继承BaseService：

```php
<?php
namespace app\admin\service;

use app\common\service\BaseService;
use app\admin\model\Article;

class ArticleService extends BaseService
{
    public function __construct()
    {
        $this->model = new Article();
    }
    
    public function createArticle(array $data): Article
    {
        // 业务逻辑处理
        return $this->create($data);
    }
}
```

### Q: 如何实现数据权限控制？

A: 在控制器中设置dataLimit属性：

```php
class User extends Backend
{
    protected bool|string|int $dataLimit = 'personal'; // 仅限个人数据
    
    // 或指定分组
    protected bool|string|int $dataLimit = 2; // 指定分组管理员可查
}
```

### Q: 如何添加新的API接口？

A: 按照以下步骤添加API：

1. 创建控制器方法
2. 添加路由规则
3. 创建前端API接口文件
4. 在页面中调用API

```php
// 控制器
public function profile(): void
{
    $userId = $this->auth->id;
    $info = $this->userService->getUserInfo($userId);
    $this->success('获取成功', $info);
}

// 路由
Route::get('admin/user/profile', 'admin.User/profile');

// 前端API
export function getUserProfile() {
    return createAxios({
        url: 'admin/user/profile',
        method: 'get',
    });
}
```

### Q: 如何优化查询性能？

A: 可以通过以下方式优化查询：

1. 添加适当的索引
2. 使用缓存机制
3. 优化查询语句
4. 使用分页查询
5. 避免N+1查询问题

```php
// 添加索引
Db::name('user')->execute('ALTER TABLE `user` ADD INDEX idx_status_create_time (`status`, `create_time`)');

// 使用缓存
public function getUserList(array $where = []): array
{
    $cacheKey = 'user:list:' . md5(serialize($where));
    
    return Cache::remember($cacheKey, function() use ($where) {
        return $this->model->where($where)->select()->toArray();
    }, 3600);
}

// 优化查询
$list = $this->model
    ->with(['roles', 'department'])
    ->where($where)
    ->field('id,username,email,status,create_time')
    ->order('create_time', 'desc')
    ->paginate($pageSize);
```

## 贡献指南

我们欢迎所有形式的贡献，包括但不限于：

- 🐛 报告Bug
- 💡 提出新功能建议
- 📝 改进文档
- 🔧 提交代码修复
- 🎨 改进UI/UX

### 开发流程

1. Fork项目到你的GitHub账户
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建Pull Request

### 代码规范

- 遵循PSR编码规范
- 编写单元测试
- 添加适当的注释
- 确保代码通过所有检查

### 文档贡献

- 修正错误和不准确之处
- 添加使用示例
- 翻译文档到其他语言
- 改进文档结构和可读性

## 开源协议

本项目采用 [Apache License 2.0](https://github.com/kevinsuperme/SuperAdmin/blob/master/LICENSE) 开源协议。

### 协议摘要

- ✅ 商业使用
- ✅ 修改
- ✅ 分发
- ✅ 私人使用
- ⚠️ 需要包含版权和许可证声明
- ⚠️ 需要说明修改内容
- ❌ 不提供责任担保
- ❌ 不提供商标授权

### 版权声明

```
Copyright 2023 SuperAdmin

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```

---

<div align="center">
  <p>如果这个项目对你有帮助，请给我们一个 ⭐️</p>
  <p>Made with ❤️ by SuperAdmin Team</p>
</div>