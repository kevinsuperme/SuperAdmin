# 自动化测试

本项目使用PHPUnit作为单元测试框架，提供了完整的测试环境配置和示例测试用例。

## 目录结构

```
tests/
├── TestCase.php          # 测试基类
├── Unit/                 # 单元测试目录
│   └── AuthServiceTest.php
└── Feature/              # 功能测试目录
    └── UserApiTest.php
```

## 运行测试

### 使用命令行

1. 运行所有测试：
```bash
vendor/bin/phpunit
```

2. 运行单元测试：
```bash
vendor/bin/phpunit --testsuite Unit
```

3. 运行功能测试：
```bash
vendor/bin/phpunit --testsuite Feature
```

4. 生成覆盖率报告：
```bash
vendor/bin/phpunit --coverage-html build/coverage --coverage-text
```

5. 运行特定测试：
```bash
vendor/bin/phpunit --filter AuthServiceTest
```

### 使用脚本

1. 运行所有测试：
```bash
php test
```

2. 运行单元测试：
```bash
php test --unit
```

3. 运行功能测试：
```bash
php test --feature
```

4. 生成覆盖率报告：
```bash
php test --coverage
```

5. 运行特定测试：
```bash
php test --filter AuthServiceTest
```

### 使用Windows批处理

1. 运行所有测试：
```cmd
test.bat
```

2. 运行单元测试：
```cmd
test.bat --unit
```

3. 运行功能测试：
```cmd
test.bat --feature
```

4. 生成覆盖率报告：
```cmd
test.bat --coverage
```

5. 运行特定测试：
```cmd
test.bat --filter AuthServiceTest
```

## 编写测试

### 单元测试

单元测试用于测试单个类或方法的功能。示例：

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        $result = some_function();
        $this->assertEquals('expected', $result);
    }
}
```

### 功能测试

功能测试用于测试API接口或完整的功能流程。示例：

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleApiTest extends TestCase
{
    public function testApiEndpoint(): void
    {
        $response = $this->get('/api/example');
        
        $response->assertStatus(200);
        $response->assertJson(['code' => 1]);
    }
}
```

## 测试环境配置

测试环境使用独立的数据库配置，在`tests/TestCase.php`中设置：

```php
protected function setupTestEnvironment(): void
{
    // 设置测试数据库配置
    $databaseConfig = [
        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'type' => 'mysql',
                'hostname' => env('DB_HOST', '127.0.0.1'),
                'database' => env('DB_DATABASE', 'fantastic_admin_test'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'hostport' => env('DB_PORT', '3306'),
                'charset' => 'utf8mb4',
                'prefix' => '',
                'debug' => true,
            ],
        ],
    ];
    
    Config::set($databaseConfig, 'database');
}
```

## 测试辅助方法

测试基类提供了一些辅助方法：

- `createTestUser(array $data = []): int` - 创建测试用户
- `createTestAdmin(array $data = []): int` - 创建测试管理员
- `cleanupTestData(): void` - 清理测试数据

## 持续集成

测试可以在CI/CD流程中自动运行，例如在GitHub Actions中：

```yaml
- name: Run Tests
  run: |
    composer install --prefer-dist --no-progress --no-suggest
    php test --coverage
```

## 最佳实践

1. 保持测试独立：每个测试应该独立运行，不依赖于其他测试的状态
2. 使用描述性的测试方法名：测试方法名应该清楚地描述测试的内容
3. 测试边界条件：不仅要测试正常情况，还要测试边界条件和异常情况
4. 使用模拟对象：对于外部依赖，使用模拟对象来隔离测试
5. 保持测试快速：单元测试应该快速运行，避免在测试中进行耗时操作

## 覆盖率报告

测试覆盖率报告生成在`build/coverage`目录下，可以通过浏览器打开`index.html`查看详细的覆盖率信息。