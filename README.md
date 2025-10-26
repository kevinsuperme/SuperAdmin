# SuperAdmin v2.4.0 - 企业级后台管理系统

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

## 📋 目录

- [项目概述](#项目概述)
- [技术架构](#技术架构)
- [核心特性](#核心特性)
- [技术栈](#技术栈)
- [系统要求](#系统要求)
- [快速开始](#快速开始)
- [架构设计](#架构设计)
- [开发指南](#开发指南)
- [部署指南](#部署指南)
- [性能优化](#性能优化)
- [安全机制](#安全机制)
- [测试框架](#测试框架)
- [CI/CD流程](#cicd流程)
- [API文档](#api文档)
- [版本历史](#版本历史)
- [常见问题](#常见问题)
- [贡献指南](#贡献指南)
- [开源协议](#开源协议)

## 📋 项目概述

SuperAdmin 是一个基于 Vue 3.5.22 + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的现代化后台管理系统。它支持常驻内存运行、可视化CRUD代码生成、自带WEB终端、自适应多端，同时提供Web、WebNuxt、Server端等多种部署方式。系统内置全局数据回收站和字段级数据修改保护、自动注册路由、无限子级权限管理等特性，无需授权即可免费商用。

### 🎯 项目定位

- **现代化架构**: 采用前后端分离架构，前端Vue3+TypeScript，后端ThinkPHP8
- **企业级特性**: 支持大型企业级应用开发，具备完整的权限管理、数据安全、系统监控
- **开发效率**: 可视化CRUD代码生成，大幅提高开发效率
- **高性能**: 支持常驻内存运行，享受比传统框架快上数十倍的性能提升
- **多端适配**: 自适应PC、平板、手机等多种设备

### 🏆 核心优势

1. **技术先进**: 采用最新稳定版本的主流技术栈
2. **架构清晰**: 前后端分离，模块化设计，易于扩展和维护
3. **功能完整**: 内置完整的后台管理功能，开箱即用
4. **安全可靠**: 多层安全防护，全面的数据保护机制
5. **性能卓越**: 多级缓存，常驻内存运行，响应迅速
6. **易于部署**: 支持多种部署方式，适应不同环境需求

## 技术架构

### 整体架构

```text
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

```text
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

```text
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

```text
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

```text
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

```text
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

```text
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

### 🧪 测试框架

SuperAdmin 提供了完整的测试框架，支持单元测试、集成测试和端到端测试，确保代码质量和系统稳定性。

### 测试架构

```text
测试框架/
├── 后端测试 (PHPUnit)
│   ├── 单元测试 (Unit Tests)
│   │   ├── Service层测试
│   │   ├── Model层测试
│   │   └── Library类测试
│   ├── 功能测试 (Feature Tests)
│   │   ├── API接口测试
│   │   ├── 控制器测试
│   │   └── 中间件测试
│   └── 集成测试 (Integration Tests)
│       ├── 数据库交互测试
│       └── 第三方服务集成测试
├── 前端测试 (Vitest + Vue Test Utils)
│   ├── 单元测试 (Unit Tests)
│   │   ├── 组件测试
│   │   ├── 工具函数测试
│   │   └── 状态管理测试
│   └── 端到端测试 (E2E Tests)
│       ├── 页面流程测试
│       └── 用户交互测试
└── 性能测试 (Performance Tests)
    ├── 负载测试
    └── 压力测试
```

### 测试覆盖率目标

- **Service层**: 90%以上
- **Controller层**: 85%以上
- **Model层**: 80%以上
- **前端组件**: 85%以上
- **整体覆盖率**: 80%以上

### 测试命令

```bash
# 后端测试
composer test                    # 运行所有测试
composer test:unit              # 只运行单元测试
composer test:feature           # 只运行功能测试
composer test:coverage          # 运行测试并生成覆盖率报告

# 前端测试
pnpm test                       # 运行所有测试
pnpm test:unit                  # 只运行单元测试
pnpm test:e2e                   # 只运行端到端测试
pnpm test:coverage              # 运行测试并生成覆盖率报告
```

### 测试示例

#### 后端单元测试示例

```php
<?php
namespace tests\Unit;

use app\common\service\UserService;
use tests\TestCase;

class UserServiceTest extends TestCase
{
    protected UserService $userService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }
    
    public function testCreateUser()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $user = $this->userService->create($userData);
        
        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('test@example.com', $user->email);
    }
    
    public function testGetUserList()
    {
        $params = [
            'page' => 1,
            'limit' => 10,
            'status' => 'enabled'
        ];
        
        $result = $this->userService->getList($params);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
    }
}
```

#### 前端组件测试示例

```typescript
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import UserTable from '@/components/table/UserTable.vue'

describe('UserTable.vue', () => {
  it('renders user data correctly', () => {
    const users = [
      { id: 1, username: 'user1', email: 'user1@example.com' },
      { id: 2, username: 'user2', email: 'user2@example.com' }
    ]
    
    const wrapper = mount(UserTable, {
      props: { users }
    })
    
    expect(wrapper.findAll('.user-row')).toHaveLength(2)
    expect(wrapper.text()).toContain('user1')
    expect(wrapper.text()).toContain('user2')
  })
  
  it('emits edit event when edit button is clicked', async () => {
    const users = [{ id: 1, username: 'user1', email: 'user1@example.com' }]
    const wrapper = mount(UserTable, {
      props: { users }
    })
    
    await wrapper.find('.edit-button').trigger('click')
    
    expect(wrapper.emitted()).toHaveProperty('edit')
    expect(wrapper.emitted().edit[0]).toEqual([users[0]])
  })
})
```

## 🚀 CI/CD流程

SuperAdmin 提供了完整的CI/CD流程，支持自动化测试、构建和部署，确保代码质量和交付效率。

### CI/CD架构

```text
CI/CD流程/
├── 持续集成 (Continuous Integration)
│   ├── 代码检查 (Code Quality)
│   │   ├── 静态代码分析
│   │   ├── 代码规范检查
│   │   └── 安全漏洞扫描
│   ├── 自动化测试 (Automated Testing)
│   │   ├── 单元测试
│   │   ├── 集成测试
│   │   └── 端到端测试
│   └── 构建打包 (Build & Package)
│       ├── 前端构建
│       └── 后端打包
├── 持续交付 (Continuous Delivery)
│   ├── 环境部署 (Environment Deployment)
│   │   ├── 测试环境
│   │   ├── 预发布环境
│   │   └── 生产环境
│   └── 数据库迁移 (Database Migration)
│       ├── 结构迁移
│       └── 数据迁移
└── 持续部署 (Continuous Deployment)
    ├── 自动化部署 (Automated Deployment)
    │   ├── 蓝绿部署
    │   └── 滚动更新
    └── 健康检查 (Health Check)
        ├── 服务监控
        └── 自动回滚
```

### GitHub Actions配置

```yaml
# .github/workflows/ci-cd.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  # 代码质量检查
  code-quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, xml, ctype, iconv, intl, pdo, pdo_mysql, dom, filter, gd, iconv, json, mbstring, pdo
          
      - name: Get Composer Cache Directory
        id: composer-cache
        run: |
          echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT
          
      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-
            
      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader
        
      - name: Run PHP CodeSniffer
        run: composer run cs-check
        
      - name: Run PHPStan
        run: composer run phpstan
        
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'
          cache-dependency-path: web/package-lock.json
          
      - name: Install npm dependencies
        working-directory: ./web
        run: npm ci
        
      - name: Run ESLint
        working-directory: ./web
        run: npm run lint
        
      - name: Run TypeScript check
        working-directory: ./web
        run: npm run type-check

  # 后端测试
  backend-tests:
    runs-on: ubuntu-latest
    needs: code-quality
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: superadmin_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
      redis:
        image: redis:6
        ports:
          - 6379:6379
        options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3
        
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, xml, ctype, iconv, intl, pdo, pdo_mysql, dom, filter, gd, iconv, json, mbstring, pdo
          
      - name: Get Composer Cache Directory
        id: composer-cache
        run: |
          echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT
          
      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-
            
      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader
        
      - name: Copy environment file
        run: cp .env.example .env
        
      - name: Generate application key
        run: php think key:generate
        
      - name: Run migrations
        run: php think migrate
        
      - name: Run tests
        run: composer test
        
      - name: Upload coverage reports to Codecov
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml
          flags: backend
          name: backend-coverage

  # 前端测试
  frontend-tests:
    runs-on: ubuntu-latest
    needs: code-quality
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'
          cache-dependency-path: web/package-lock.json
          
      - name: Install npm dependencies
        working-directory: ./web
        run: npm ci
        
      - name: Run tests
        working-directory: ./web
        run: npm run test:unit
        
      - name: Build application
        working-directory: ./web
        run: npm run build
        
      - name: Upload coverage reports to Codecov
        uses: codecov/codecov-action@v3
        with:
          file: ./web/coverage/lcov.info
          flags: frontend
          name: frontend-coverage

  # 部署到测试环境
  deploy-staging:
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-tests]
    if: github.ref == 'refs/heads/develop'
    environment: staging
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to staging
        run: |
          echo "Deploying to staging environment"
          # 部署脚本
          
  # 部署到生产环境
  deploy-production:
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-tests]
    if: github.ref == 'refs/heads/main'
    environment: production
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to production
        run: |
          echo "Deploying to production environment"
          # 部署脚本
```

### 部署策略

#### 蓝绿部署

蓝绿部署是一种零停机部署策略，通过维护两个相同的生产环境（蓝色和绿色）来实现无缝切换。

```bash
# 部署脚本示例
#!/bin/bash

# 当前活跃环境
ACTIVE_ENV=$(curl -s http://api.example.com/health | jq -r '.environment')

# 确定目标环境
if [ "$ACTIVE_ENV" = "blue" ]; then
    TARGET_ENV="green"
else
    TARGET_ENV="blue"
fi

# 部署到目标环境
echo "Deploying to $TARGET_ENV environment"
docker-compose -f docker-compose.$TARGET_ENV.yml up -d

# 健康检查
sleep 30
HEALTH_CHECK=$(curl -s http://api-$TARGET_ENV.example.com/health | jq -r '.status')

if [ "$HEALTH_CHECK" = "healthy" ]; then
    # 切换流量
    echo "Switching traffic to $TARGET_ENV environment"
    # 更新负载均衡器配置
    
    # 停止旧环境
    docker-compose -f docker-compose.$ACTIVE_ENV.yml down
    echo "Deployment successful"
else
    # 回滚
    echo "Health check failed, rolling back"
    docker-compose -f docker-compose.$TARGET_ENV.yml down
    exit 1
fi
```

#### 滚动更新

滚动更新是一种逐步替换旧实例的部署策略，适用于有状态服务或资源受限的环境。

```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    image: superadmin:latest
    deploy:
      replicas: 3
      update_config:
        parallelism: 1
        delay: 10s
        failure_action: rollback
      restart_policy:
        condition: on-failure
```

### 监控与告警

#### 健康检查端点

```php
<?php
namespace app\api\controller;

use think\facade\Db;
use think\facade\Cache;

class Health
{
    public function check()
    {
        $status = 'healthy';
        $services = [];
        
        // 检查数据库连接
        try {
            Db::query('SELECT 1');
            $services['database'] = 'ok';
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $services['database'] = 'error: ' . $e->getMessage();
        }
        
        // 检查缓存连接
        try {
            Cache::set('health_check', 'ok', 10);
            $cacheResult = Cache::get('health_check');
            if ($cacheResult === 'ok') {
                $services['cache'] = 'ok';
            } else {
                $status = 'unhealthy';
                $services['cache'] = 'error: cache read/write failed';
            }
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $services['cache'] = 'error: ' . $e->getMessage();
        }
        
        // 检查文件系统
        $uploadDir = runtime_path() . 'uploads';
        if (is_dir($uploadDir) && is_writable($uploadDir)) {
            $services['filesystem'] = 'ok';
        } else {
            $status = 'unhealthy';
            $services['filesystem'] = 'error: upload directory not writable';
        }
        
        return json([
            'status' => $status,
            'timestamp' => date('c'),
            'services' => $services,
            'system' => [
                'php_version' => PHP_VERSION,
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
                'disk_usage' => round(disk_free_space('/') / disk_total_space('/') * 100, 2) . '%'
            ]
        ]);
    }
}
```

#### 告警配置

```yaml
# prometheus.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'superadmin'
    static_configs:
      - targets: ['app:80']
    metrics_path: '/metrics'
    scrape_interval: 5s

rule_files:
  - "alert_rules.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093
```

```yaml
# alert_rules.yml
groups:
  - name: superadmin_alerts
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.1
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "High error rate detected"
          description: "Error rate is {{ $value }} errors per second"
          
      - alert: HighResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 1
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High response time detected"
          description: "95th percentile response time is {{ $value }} seconds"
          
      - alert: DatabaseConnectionFailed
        expr: up{job="mysql"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Database connection failed"
          description: "Cannot connect to MySQL database"
```

## 🧪 测试指南

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
}
```

## 📚 版本历史

### v2.4.0 (当前开发版本)
- 🚀 新增完整的CI/CD流程支持
- 🧪 引入全面的测试框架
- 📊 新增API文档自动生成功能
- 🔍 新增健康检查API端点
- 🛡️ 增强认证中间件
- 📈 优化系统性能和稳定性
- 🎨 改进UI/UX设计
- 📝 完善项目文档

### v2.3.3 (稳定版本)
- 🔧 修复已知问题
- 📦 更新依赖包版本
- 🐛 修复安全漏洞
- 🌐 优化多语言支持
- 📱 改进移动端适配

### v2.3.2
- 🎨 优化主题系统
- 🔍 改进搜索功能
- 📊 增强数据可视化
- 🚀 提升系统性能
- 🛠️ 优化开发体验

### v2.3.1
- 🐛 修复关键bug
- 🔧 优化配置管理
- 📚 更新文档
- 🌟 新增示例代码
- 🔄 改进数据导入导出

### v2.3.0
- 🎉 重大功能更新
- 🏗️ 重构核心架构
- 📱 优化移动端体验
- 🔐 增强安全特性
- 🎨 全新UI设计

### v2.2.x 系列
- 📊 增强数据分析功能
- 🔍 改进搜索和过滤
- 📝 优化表单处理
- 🚀 提升性能表现
- 🛡️ 加强安全防护

### v2.1.x 系列
- 🎨 引入主题系统
- 📱 改进响应式设计
- 🔧 优化配置管理
- 📚 完善文档系统
- 🌟 增加新组件

### v2.0.x 系列
- 🏗️ 全面重构项目架构
- 🚀 迁移至Vue 3 + TypeScript
- 📦 重构后端为ThinkPHP 8
- 🔐 重新设计权限系统
- 🎨 全新UI界面

### v1.x 系列
- 🎉 项目初始版本
- 📦 基础功能实现
- 🎨 基本UI设计
- 📝 初版文档
- 🌟 核心特性开发

## 🚀 路线图

### v2.5.0 (计划中)
- 🤖 引入AI辅助功能
- 📊 高级数据分析与可视化
- 🌐 多租户支持
- 🔄 工作流引擎
- 📱 移动端APP

### v2.6.0 (规划中)
- 🌍 国际化全面支持
- 🔌 插件市场
- 📊 实时数据监控
- 🎨 可视化页面构建器
- 🛡️ 高级安全特性

### v3.0.0 (长期规划)
- 🏗️ 微服务架构
- 🤖 智能化运维
- 🌐 云原生支持
- 📊 大数据处理能力
- 🎯 低代码开发平台

---

## 🤝 贡献

我们欢迎所有形式的贡献！无论是提交代码、报告问题、改进文档还是提出建议，您的参与都是宝贵的。

### 开发流程

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 提交 Pull Request

### 代码规范

- 遵循PSR-12 PHP编码规范
- 使用ESLint和Prettier格式化前端代码
- 编写单元测试覆盖新功能
- 提交信息遵循[Conventional Commits](https://conventionalcommits.org/)规范

### 文档贡献

- 改进现有文档
- 添加使用示例
- 翻译文档到其他语言
- 更新API文档

---

## 📄 许可证

本项目采用 [Apache License 2.0](LICENSE) 许可证。

---

## 🙏 致谢

感谢所有为SuperAdmin项目做出贡献的开发者和用户！

---

## ⭐ Star History

如果这个项目对您有帮助，请给我们一个Star！

[![Star History Chart](https://api.star-history.com/svg?repos=fantastic-admin/super-admin&type=Date)](https://star-history.com/#fantastic-admin/super-admin&Date)

---

## 📞 联系我们

- 📧 邮箱: support@fantastic-admin.com
- 🐛 问题反馈: [GitHub Issues](https://github.com/fantastic-admin/super-admin/issues)
- 💬 讨论交流: [GitHub Discussions](https://github.com/fantastic-admin/super-admin/discussions)
- 📖 文档: [官方文档](https://doc.fantastic-admin.com)

---

<div align="center">
  <p>由 ❤️ 和 ☕ 驱动开发</p>
  <p>© 2024 Fantastic Admin Team</p>
</div>