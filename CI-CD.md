# CI/CD 流程说明

本项目已配置完整的CI/CD流程，使用GitHub Actions实现自动化构建、测试和部署。

## CI/CD 流程概述

CI/CD流程包含以下阶段：

1. **代码质量检查** - 检查代码风格、语法错误等
2. **后端测试** - 运行PHPUnit单元测试和功能测试
3. **前端测试** - 运行前端测试（如果配置了）
4. **构建部署** - 构建应用并部署到目标环境
5. **安全扫描** - 扫描安全漏洞

## 文件结构

```
.github/workflows/
└── ci-cd.yml              # GitHub Actions工作流配置

scripts/
├── deploy.sh              # Linux环境部署脚本
└── deploy.ps1             # Windows PowerShell部署脚本

tests/
├── TestCase.php           # 测试基类
├── Unit/                  # 单元测试目录
│   └── AuthServiceTest.php
├── Feature/               # 功能测试目录
│   └── UserApiTest.php
└── README.md              # 测试说明文档

phpunit.xml                # PHPUnit配置文件
```

## 使用方法

### 1. 代码质量检查

代码质量检查会自动在每次提交时运行，检查以下内容：

- PHP代码风格（使用PHP_CodeSniffer）
- PHP语法错误（使用PHP -l）
- 前端代码风格（使用ESLint）

### 2. 运行测试

#### 手动运行测试

在项目根目录下执行以下命令：

```bash
# 运行所有测试
php think test

# 运行单元测试
php think test --type=unit

# 运行功能测试
php think test --type=feature

# 生成覆盖率报告
php think test --coverage

# 详细输出
php think test --verbose

# 运行特定测试
php think test --filter=AuthServiceTest
```

#### Windows批处理文件

在Windows环境下，可以使用批处理文件：

```cmd
# 运行所有测试
test.bat

# 运行单元测试
test.bat unit

# 运行功能测试
test.bat feature

# 生成覆盖率报告
test.bat coverage

# 详细输出
test.bat verbose

# 运行特定测试
test.bat filter AuthServiceTest
```

### 3. 部署

#### Linux环境部署

在Linux服务器上，使用部署脚本：

```bash
# 给脚本执行权限
chmod +x scripts/deploy.sh

# 执行部署
./scripts/deploy.sh /path/to/release.tar.gz /var/www/html
```

#### Windows环境部署

在Windows服务器上，使用PowerShell部署脚本：

```powershell
# 执行部署
.\scripts\deploy.ps1 -ReleasePackagePath "C:\path\to\release.zip" -WebRootPath "C:\inetpub\wwwroot"
```

### 4. 健康检查

部署完成后，可以通过健康检查接口验证应用状态：

```bash
curl http://your-domain.com/api/health
```

健康检查接口会返回以下信息：

- 数据库连接状态
- 缓存服务状态
- 文件系统状态
- 系统负载状态

## 环境变量配置

在GitHub仓库设置中配置以下环境变量：

- `DEPLOY_HOST`: 部署服务器地址
- `DEPLOY_USER`: 部署用户名
- `DEPLOY_PATH`: 部署路径
- `DEPLOY_KEY`: SSH私钥（用于部署）

## 分支策略

- `main` 分支：自动部署到生产环境
- `develop` 分支：自动部署到测试环境
- 其他分支：仅运行测试，不部署

## 故障排除

### 测试失败

1. 检查测试环境配置是否正确
2. 确保数据库连接正常
3. 检查依赖是否安装完整

### 部署失败

1. 检查部署脚本权限
2. 确认服务器连接信息
3. 查看部署日志获取详细错误信息

### 健康检查失败

1. 检查应用是否正常运行
2. 确认数据库连接配置
3. 检查缓存服务状态

## 最佳实践

1. **频繁提交**：小步快跑，频繁提交代码，便于快速发现问题
2. **编写测试**：为新功能编写测试，确保代码质量
3. **环境隔离**：使用不同环境变量区分开发、测试和生产环境
4. **监控部署**：部署后及时检查应用状态和日志
5. **回滚准备**：保留上一版本，便于快速回滚

## 自定义配置

### 修改测试配置

编辑 `phpunit.xml` 文件，修改测试配置：

```xml
<!-- 修改测试数据库配置 -->
<env name="DB_DATABASE" value="test_db"/>
```

### 修改部署脚本

根据实际需求修改 `scripts/deploy.sh` 或 `scripts/deploy.ps1` 文件。

### 添加新的测试步骤

在 `.github/workflows/ci-cd.yml` 文件中添加新的步骤：

```yaml
- name: Custom Step
  run: |
    echo "执行自定义步骤"
    # 添加自定义命令
```

## 联系支持

如果遇到问题，请：

1. 查看GitHub Actions日志
2. 检查相关配置文件
3. 联系开发团队获取支持