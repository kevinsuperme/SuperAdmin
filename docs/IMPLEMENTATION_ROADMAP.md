# SuperAdmin 卓越级别提升实施方案

**目标**: 将项目从 4.25/5 提升到 5/5（卓越）水平
**周期**: 8-12周
**负责人**: 技术团队
**开始日期**: 2025-10-26

---

## 📊 当前状态 vs 目标状态

| 维度 | 当前评分 | 目标评分 | 差距 | 优先级 |
|------|---------|---------|------|--------|
| 测试覆盖率 | ⭐☆☆☆☆ (1/5) | ⭐⭐⭐⭐⭐ (5/5) | 🔴 极大 | P0 |
| CI/CD | ⭐⭐☆☆☆ (2/5) | ⭐⭐⭐⭐⭐ (5/5) | 🔴 较大 | P0 |
| 监控告警 | ⭐☆☆☆☆ (1/5) | ⭐⭐⭐⭐⭐ (5/5) | 🔴 极大 | P0 |
| API文档 | ⭐⭐☆☆☆ (2/5) | ⭐⭐⭐⭐⭐ (5/5) | 🟡 中等 | P1 |
| 性能优化 | ⭐⭐⭐⭐☆ (4/5) | ⭐⭐⭐⭐⭐ (5/5) | 🟢 较小 | P2 |
| 安全加固 | ⭐⭐⭐⭐⭐ (5/5) | ⭐⭐⭐⭐⭐ (5/5) | ✅ 无 | - |

---

## 🎯 总体实施计划

### 阶段一：基础设施建设（Week 1-4）

```
Week 1-2: 测试框架搭建 + 核心用例编写
Week 3-4: CI/CD流程 + 监控告警系统
```

### 阶段二：质量提升（Week 5-8）

```
Week 5-6: 测试覆盖率达标 + API文档生成
Week 7-8: 性能优化 + 安全加固
```

### 阶段三：验证与上线（Week 9-12）

```
Week 9-10: 压力测试 + 安全审计
Week 11-12: 文档完善 + 团队培训 + 上线
```

---

## 📋 详细实施方案

---

## 一、测试框架搭建与用例编写（Week 1-2）

**目标**: 测试覆盖率从 5% 提升到 80%

### 1.1 后端测试框架搭建

#### Step 1: 安装PHPUnit和依赖

```bash
# 进入项目根目录
cd /path/to/superadmin

# 安装PHPUnit
composer require --dev phpunit/phpunit ^10.0

# 安装Mockery（Mock框架）
composer require --dev mockery/mockery

# 安装数据库工厂
composer require --dev fakerphp/faker
```

#### Step 2: 配置PHPUnit

```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnRisky="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">app</directory>
        </include>
        <exclude>
            <directory>app/common/model</directory>
            <file>app/common.php</file>
        </exclude>
        <report>
            <html outputDirectory="tests/coverage/html"/>
            <clover outputFile="tests/coverage/clover.xml"/>
        </report>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="superadmin_test"/>
    </php>
</phpunit>
```

#### Step 3: 创建测试基类

```php
<?php
// tests/TestCase.php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use think\facade\Db;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 初始化Think应用
        $this->app = app();

        // 开始数据库事务
        Db::startTrans();
    }

    protected function tearDown(): void
    {
        // 回滚数据库事务
        Db::rollback();

        parent::tearDown();
    }

    /**
     * 创建Mock对象
     */
    protected function mock(string $class): \Mockery\MockInterface
    {
        return \Mockery::mock($class);
    }

    /**
     * 断言数组包含键值
     */
    protected function assertArrayHasKeys(array $keys, array $array): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }
}
```

#### Step 4: 编写Service层单元测试

```php
<?php
// tests/Unit/Service/UserServiceTest.php

namespace Tests\Unit\Service;

use Tests\TestCase;
use app\common\service\UserService;
use app\common\model\User;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    /**
     * 测试查询单条记录
     */
    public function testFindById(): void
    {
        // 创建测试数据
        $user = User::create([
            'username' => 'testuser',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'email' => 'test@example.com',
            'status' => 1,
        ]);

        // 执行查询
        $result = $this->userService->find($user->id);

        // 断言
        $this->assertNotNull($result);
        $this->assertEquals('testuser', $result->username);
        $this->assertEquals('test@example.com', $result->email);
    }

    /**
     * 测试创建用户
     */
    public function testCreateUser(): void
    {
        $data = [
            'username' => 'newuser',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'newuser@example.com',
            'status' => 1,
        ];

        $user = $this->userService->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('newuser', $user->username);

        // 验证数据库中存在
        $dbUser = User::where('username', 'newuser')->find();
        $this->assertNotNull($dbUser);
    }

    /**
     * 测试更新用户
     */
    public function testUpdateUser(): void
    {
        // 创建测试用户
        $user = User::create([
            'username' => 'oldname',
            'email' => 'old@example.com',
            'status' => 1,
        ]);

        // 更新
        $result = $this->userService->update($user->id, [
            'username' => 'newname',
            'email' => 'new@example.com',
        ]);

        $this->assertTrue($result);

        // 验证更新后的数据
        $updatedUser = User::find($user->id);
        $this->assertEquals('newname', $updatedUser->username);
        $this->assertEquals('new@example.com', $updatedUser->email);
    }

    /**
     * 测试删除用户
     */
    public function testDeleteUser(): void
    {
        $user = User::create([
            'username' => 'deleteuser',
            'email' => 'delete@example.com',
            'status' => 1,
        ]);

        $result = $this->userService->delete($user->id);

        $this->assertTrue($result);

        // 验证已删除（软删除）
        $deletedUser = User::withTrashed()->find($user->id);
        $this->assertNotNull($deletedUser->delete_time);
    }

    /**
     * 测试分页查询
     */
    public function testPaginate(): void
    {
        // 创建多个测试用户
        for ($i = 1; $i <= 25; $i++) {
            User::create([
                'username' => "user{$i}",
                'email' => "user{$i}@example.com",
                'status' => 1,
            ]);
        }

        // 分页查询
        $result = $this->userService->paginate(['status' => 1], ['id' => 'desc'], 1, 10);

        $this->assertArrayHasKeys(['list', 'total', 'page', 'limit'], $result);
        $this->assertCount(10, $result['list']);
        $this->assertEquals(25, $result['total']);
        $this->assertEquals(1, $result['page']);
    }

    /**
     * 测试统计数量
     */
    public function testCount(): void
    {
        // 创建测试数据
        User::create(['username' => 'active1', 'status' => 1]);
        User::create(['username' => 'active2', 'status' => 1]);
        User::create(['username' => 'inactive1', 'status' => 0]);

        $activeCount = $this->userService->count(['status' => 1]);
        $this->assertEquals(2, $activeCount);

        $inactiveCount = $this->userService->count(['status' => 0]);
        $this->assertEquals(1, $inactiveCount);
    }
}
```

#### Step 5: 编写AuthService测试

```php
<?php
// tests/Unit/Service/AuthServiceTest.php

namespace Tests\Unit\Service;

use Tests\TestCase;
use app\common\service\AuthService;
use app\common\library\Auth;
use app\common\model\Admin;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建Auth实例的Mock
        $auth = $this->mock(Auth::class);
        $this->authService = new AuthService($auth);
    }

    /**
     * 测试登录成功
     */
    public function testLoginSuccess(): void
    {
        // 创建测试管理员
        $admin = Admin::create([
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@example.com',
            'status' => 1,
        ]);

        // Mock Auth行为
        $this->authService->auth
            ->shouldReceive('isLogin')->andReturn(false)
            ->shouldReceive('login')->with('admin', 'admin123', false)->andReturn(true)
            ->shouldReceive('getUserInfo')->andReturn([
                'id' => $admin->id,
                'username' => 'admin',
            ]);

        $result = $this->authService->login('admin', 'admin123', false, []);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login succeeded!', $result['msg']);
        $this->assertArrayHasKey('userInfo', $result['data']);
    }

    /**
     * 测试登录失败
     */
    public function testLoginFailure(): void
    {
        $this->authService->auth
            ->shouldReceive('isLogin')->andReturn(false)
            ->shouldReceive('login')->with('admin', 'wrongpassword', false)->andReturn(false)
            ->shouldReceive('getError')->andReturn('Invalid credentials');

        $result = $this->authService->login('admin', 'wrongpassword', false, []);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid credentials', $result['msg']);
    }

    /**
     * 测试已登录状态
     */
    public function testAlreadyLoggedIn(): void
    {
        $this->authService->auth
            ->shouldReceive('isLogin')->andReturn(true)
            ->shouldReceive('getUserInfo')->andReturn([
                'id' => 1,
                'username' => 'admin',
            ]);

        $result = $this->authService->login('admin', 'password', false, []);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('already logged in', $result['msg']);
    }

    /**
     * 测试登出
     */
    public function testLogout(): void
    {
        $this->authService->auth
            ->shouldReceive('logout')->once();

        $result = $this->authService->logout();

        $this->assertTrue($result['success']);
        $this->assertEquals('Logout succeeded!', $result['msg']);
    }
}
```

#### Step 6: 编写Controller集成测试

```php
<?php
// tests/Feature/Api/UserApiTest.php

namespace Tests\Feature\Api;

use Tests\TestCase;
use app\common\model\User;
use app\common\model\Admin;

class UserApiTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试管理员并获取Token
        $admin = Admin::create([
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'status' => 1,
        ]);

        // 模拟登录获取Token
        $this->token = $this->loginAndGetToken('admin', 'admin123');
    }

    /**
     * 测试获取用户列表
     */
    public function testGetUserList(): void
    {
        // 创建测试数据
        User::create(['username' => 'user1', 'status' => 1]);
        User::create(['username' => 'user2', 'status' => 1]);

        $response = $this->get('/admin/user/index', [
            'page' => 1,
            'limit' => 10,
        ], [
            'batoken' => $this->token,
        ]);

        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('list', $response['data']);
        $this->assertGreaterThanOrEqual(2, count($response['data']['list']));
    }

    /**
     * 测试创建用户
     */
    public function testCreateUser(): void
    {
        $userData = [
            'username' => 'newuser',
            'password' => 'password123',
            'email' => 'newuser@example.com',
            'status' => 1,
        ];

        $response = $this->post('/admin/user/add', $userData, [
            'batoken' => $this->token,
        ]);

        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['success']);

        // 验证数据库
        $user = User::where('username', 'newuser')->find();
        $this->assertNotNull($user);
        $this->assertEquals('newuser@example.com', $user->email);
    }

    /**
     * 测试更新用户
     */
    public function testUpdateUser(): void
    {
        $user = User::create([
            'username' => 'oldname',
            'email' => 'old@example.com',
            'status' => 1,
        ]);

        $response = $this->put("/admin/user/edit/{$user->id}", [
            'username' => 'newname',
            'email' => 'new@example.com',
        ], [
            'batoken' => $this->token,
        ]);

        $this->assertEquals(200, $response['code']);

        $updatedUser = User::find($user->id);
        $this->assertEquals('newname', $updatedUser->username);
    }

    /**
     * 测试删除用户
     */
    public function testDeleteUser(): void
    {
        $user = User::create([
            'username' => 'deleteuser',
            'status' => 1,
        ]);

        $response = $this->delete("/admin/user/del/{$user->id}", [], [
            'batoken' => $this->token,
        ]);

        $this->assertEquals(200, $response['code']);

        // 验证软删除
        $deletedUser = User::withTrashed()->find($user->id);
        $this->assertNotNull($deletedUser->delete_time);
    }

    /**
     * 测试API限流
     */
    public function testRateLimiting(): void
    {
        $maxRequests = 60;

        // 连续发送超过限制的请求
        for ($i = 0; $i < $maxRequests + 1; $i++) {
            $response = $this->get('/admin/user/index', [], [
                'batoken' => $this->token,
            ]);

            if ($i < $maxRequests) {
                $this->assertEquals(200, $response['code']);
            } else {
                // 第61次请求应该被限流
                $this->assertEquals(429, $response['code']);
                $this->assertArrayHasKey('retry_after', $response['data']);
            }
        }
    }

    /**
     * 测试权限验证
     */
    public function testUnauthorizedAccess(): void
    {
        $response = $this->get('/admin/user/index', [], []);

        $this->assertEquals(401, $response['code']);
        $this->assertStringContainsString('Unauthorized', $response['msg']);
    }

    /**
     * 辅助方法：模拟登录获取Token
     */
    private function loginAndGetToken(string $username, string $password): string
    {
        $response = $this->post('/admin/login', [
            'username' => $username,
            'password' => $password,
        ]);

        return $response['data']['token'] ?? '';
    }

    /**
     * 辅助方法：发送GET请求
     */
    private function get(string $url, array $params = [], array $headers = []): array
    {
        // 实现HTTP GET请求
        // ...
    }

    /**
     * 辅助方法：发送POST请求
     */
    private function post(string $url, array $data = [], array $headers = []): array
    {
        // 实现HTTP POST请求
        // ...
    }
}
```

---

### 1.2 前端测试框架搭建

#### Step 1: 安装Vitest和测试依赖

```bash
cd web

# 安装Vitest（Vue官方推荐）
npm install --save-dev vitest

# 安装Vue测试工具
npm install --save-dev @vue/test-utils

# 安装jsdom（模拟DOM环境）
npm install --save-dev jsdom

# 安装测试覆盖率工具
npm install --save-dev @vitest/coverage-v8

# 安装Playwright（E2E测试）
npm install --save-dev @playwright/test
```

#### Step 2: 配置Vitest

```typescript
// web/vitest.config.ts
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.d.ts',
        '**/*.config.*',
        '**/mockData',
        'src/main.ts',
      ],
      thresholds: {
        lines: 70,
        functions: 70,
        branches: 70,
        statements: 70,
      },
    },
  },
  resolve: {
    alias: {
      '/@': resolve(__dirname, './src'),
    },
  },
})
```

#### Step 3: 创建测试setup文件

```typescript
// web/tests/setup.ts
import { config } from '@vue/test-utils'
import { vi } from 'vitest'

// Mock Element Plus组件
config.global.stubs = {
  ElButton: true,
  ElInput: true,
  ElForm: true,
  ElFormItem: true,
  ElDialog: true,
  ElTable: true,
}

// Mock window对象
global.window = Object.create(window)
global.window.matchMedia = vi.fn().mockImplementation(query => ({
  matches: false,
  media: query,
  onchange: null,
  addListener: vi.fn(),
  removeListener: vi.fn(),
  addEventListener: vi.fn(),
  removeEventListener: vi.fn(),
  dispatchEvent: vi.fn(),
}))
```

#### Step 4: 编写组件单元测试

```typescript
// web/tests/unit/components/UserForm.spec.ts
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import UserForm from '/@/components/UserForm.vue'

describe('UserForm.vue', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(UserForm, {
      props: {
        modelValue: {
          username: '',
          email: '',
          status: 1,
        },
      },
    })
  })

  it('renders properly', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('form').exists()).toBe(true)
  })

  it('validates required username', async () => {
    const usernameInput = wrapper.find('input[name="username"]')

    // 不填写用户名直接提交
    await wrapper.find('button[type="submit"]').trigger('click')

    // 应该显示错误信息
    expect(wrapper.find('.error-message').text()).toContain('用户名不能为空')
  })

  it('validates email format', async () => {
    const emailInput = wrapper.find('input[name="email"]')

    // 输入无效邮箱
    await emailInput.setValue('invalid-email')
    await wrapper.find('button[type="submit"]').trigger('click')

    expect(wrapper.find('.error-message').text()).toContain('邮箱格式不正确')
  })

  it('emits submit event with valid data', async () => {
    // 填写有效数据
    await wrapper.find('input[name="username"]').setValue('testuser')
    await wrapper.find('input[name="email"]').setValue('test@example.com')

    await wrapper.find('button[type="submit"]').trigger('click')

    // 验证emit事件
    expect(wrapper.emitted('submit')).toBeTruthy()
    expect(wrapper.emitted('submit')[0][0]).toEqual({
      username: 'testuser',
      email: 'test@example.com',
      status: 1,
    })
  })

  it('updates modelValue when input changes', async () => {
    await wrapper.find('input[name="username"]').setValue('newuser')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0][0].username).toBe('newuser')
  })
})
```

#### Step 5: 编写Composables测试

```typescript
// web/tests/unit/composables/useUser.spec.ts
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useUser } from '/@/composables/useUser'
import { setActivePinia, createPinia } from 'pinia'

// Mock axios
vi.mock('axios', () => ({
  default: {
    create: vi.fn(() => ({
      get: vi.fn(),
      post: vi.fn(),
      put: vi.fn(),
      delete: vi.fn(),
    })),
  },
}))

describe('useUser', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('fetches user list successfully', async () => {
    const { userList, loading, fetchUserList } = useUser()

    // Mock API响应
    const mockUsers = [
      { id: 1, username: 'user1', email: 'user1@example.com' },
      { id: 2, username: 'user2', email: 'user2@example.com' },
    ]

    // 调用获取用户列表
    await fetchUserList()

    expect(loading.value).toBe(false)
    expect(userList.value).toEqual(mockUsers)
  })

  it('handles fetch error', async () => {
    const { error, fetchUserList } = useUser()

    // Mock API错误
    // ...

    await fetchUserList()

    expect(error.value).toBeTruthy()
    expect(error.value.message).toContain('Failed to fetch users')
  })
})
```

#### Step 6: 编写E2E测试

```typescript
// web/tests/e2e/login.spec.ts
import { test, expect } from '@playwright/test'

test.describe('Login Flow', () => {
  test('successful login with valid credentials', async ({ page }) => {
    // 访问登录页面
    await page.goto('http://localhost:5173/admin/login')

    // 填写登录表单
    await page.fill('input[name="username"]', 'admin')
    await page.fill('input[name="password"]', 'admin123')

    // 点击登录按钮
    await page.click('button[type="submit"]')

    // 等待跳转到仪表盘
    await page.waitForURL('**/admin/dashboard')

    // 验证登录成功
    expect(page.url()).toContain('/admin/dashboard')

    // 验证页面显示用户名
    const username = await page.textContent('.user-info .username')
    expect(username).toBe('admin')
  })

  test('failed login with invalid credentials', async ({ page }) => {
    await page.goto('http://localhost:5173/admin/login')

    await page.fill('input[name="username"]', 'admin')
    await page.fill('input[name="password"]', 'wrongpassword')
    await page.click('button[type="submit"]')

    // 应该显示错误提示
    const errorMessage = await page.textContent('.error-notification')
    expect(errorMessage).toContain('用户名或密码错误')

    // 不应该跳转
    expect(page.url()).toContain('/login')
  })

  test('logout flow', async ({ page }) => {
    // 先登录
    await page.goto('http://localhost:5173/admin/login')
    await page.fill('input[name="username"]', 'admin')
    await page.fill('input[name="password"]', 'admin123')
    await page.click('button[type="submit"]')
    await page.waitForURL('**/admin/dashboard')

    // 点击登出
    await page.click('.user-menu')
    await page.click('text=退出登录')

    // 应该跳转回登录页
    await page.waitForURL('**/admin/login')
    expect(page.url()).toContain('/login')
  })
})
```

#### Step 7: 配置Playwright

```typescript
// web/playwright.config.ts
import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:5173',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
  ],

  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:5173',
    reuseExistingServer: !process.env.CI,
  },
})
```

#### Step 8: 更新package.json脚本

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "test": "vitest",
    "test:unit": "vitest run",
    "test:coverage": "vitest run --coverage",
    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui",
    "test:all": "npm run test:unit && npm run test:e2e"
  }
}
```

---

### 1.3 测试覆盖率目标

| 模块 | 当前覆盖率 | 目标覆盖率 | 优先级 |
|------|-----------|-----------|--------|
| Service层 | ~5% | **90%** | 🔴 P0 |
| Controller层 | ~0% | **70%** | 🔴 P0 |
| Middleware层 | ~0% | **80%** | 🔴 P0 |
| 前端组件 | ~0% | **70%** | 🟡 P1 |
| Composables | ~0% | **80%** | 🟡 P1 |
| 工具函数 | ~0% | **95%** | 🟢 P2 |

#### 测试用例清单（必须完成）

**后端（至少150+用例）**：
- ✅ BaseService: 20个用例
- ✅ UserService: 15个用例
- ✅ AuthService: 12个用例
- ✅ CacheService: 10个用例
- ✅ Controller: 50个用例
- ✅ Middleware: 30个用例
- ✅ API集成测试: 13个用例

**前端（至少100+用例）**：
- ✅ 核心组件: 40个用例
- ✅ Composables: 25个用例
- ✅ Utils: 20个用例
- ✅ E2E: 15个场景

---

## 二、CI/CD流程搭建（Week 3）

**目标**: 实现全自动化测试、构建和部署

### 2.1 GitHub Actions配置

创建完整的CI/CD工作流：

```yaml
# .github/workflows/ci-cd.yml
name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

env:
  PHP_VERSION: '8.1'
  NODE_VERSION: '18'
  MYSQL_DATABASE: superadmin_test
  MYSQL_ROOT_PASSWORD: root

jobs:
  # ==================== 代码质量检查 ====================
  code-quality:
    name: Code Quality Check
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: mbstring, pdo, pdo_mysql, redis
          coverage: xdebug

      - name: Install PHP Dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse app tests --level=8

      - name: Run PHP_CodeSniffer
        run: ./vendor/bin/phpcs --standard=PSR12 app

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: 'npm'
          cache-dependency-path: web/package-lock.json

      - name: Install Frontend Dependencies
        working-directory: web
        run: npm ci

      - name: Run ESLint
        working-directory: web
        run: npm run lint

      - name: Run TypeScript Check
        working-directory: web
        run: npm run typecheck

  # ==================== 后端测试 ====================
  backend-test:
    name: Backend Tests
    needs: code-quality
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: ${{ env.MYSQL_ROOT_PASSWORD }}
          MYSQL_DATABASE: ${{ env.MYSQL_DATABASE }}
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

      redis:
        image: redis:6.2-alpine
        ports:
          - 6379:6379
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: mbstring, pdo, pdo_mysql, redis
          coverage: xdebug

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Create .env.testing
        run: |
          cp .env.example .env.testing
          sed -i 's/DB_DATABASE=.*/DB_DATABASE=${{ env.MYSQL_DATABASE }}/' .env.testing
          sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/' .env.testing
          sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=${{ env.MYSQL_ROOT_PASSWORD }}/' .env.testing

      - name: Run Database Migrations
        run: php think migrate:run --seed

      - name: Run PHPUnit Tests
        run: ./vendor/bin/phpunit --coverage-clover=coverage.xml --coverage-html=coverage/html

      - name: Upload Coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
          flags: backend
          name: backend-coverage

      - name: Archive Coverage Report
        uses: actions/upload-artifact@v3
        with:
          name: backend-coverage
          path: coverage/html

  # ==================== 前端测试 ====================
  frontend-test:
    name: Frontend Tests
    needs: code-quality
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: 'npm'
          cache-dependency-path: web/package-lock.json

      - name: Install Dependencies
        working-directory: web
        run: npm ci

      - name: Run Unit Tests
        working-directory: web
        run: npm run test:coverage

      - name: Upload Coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./web/coverage/coverage-final.json
          flags: frontend
          name: frontend-coverage

      - name: Install Playwright Browsers
        working-directory: web
        run: npx playwright install --with-deps

      - name: Run E2E Tests
        working-directory: web
        run: npm run test:e2e

      - name: Upload E2E Test Results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: web/playwright-report

  # ==================== 安全扫描 ====================
  security-scan:
    name: Security Scan
    needs: [backend-test, frontend-test]
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Run Trivy vulnerability scanner
        uses: aquasecurity/trivy-action@master
        with:
          scan-type: 'fs'
          scan-ref: '.'
          format: 'sarif'
          output: 'trivy-results.sarif'

      - name: Upload Trivy results to GitHub Security
        uses: github/codeql-action/upload-sarif@v2
        with:
          sarif_file: 'trivy-results.sarif'

      - name: Check PHP Dependencies
        run: |
          composer install
          composer audit

      - name: Check NPM Dependencies
        working-directory: web
        run: npm audit --audit-level=high

  # ==================== 构建Docker镜像 ====================
  build-docker:
    name: Build Docker Images
    needs: [backend-test, frontend-test, security-scan]
    runs-on: ubuntu-latest
    if: github.event_name == 'push' && (github.ref == 'refs/heads/main' || github.ref == 'refs/heads/develop')

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v2

      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}

      - name: Extract metadata for Backend
        id: meta-backend
        uses: docker/metadata-action@v4
        with:
          images: superadmin/backend
          tags: |
            type=ref,event=branch
            type=sha,prefix={{branch}}-

      - name: Build and Push Backend Image
        uses: docker/build-push-action@v4
        with:
          context: .
          file: ./docker/Dockerfile.backend
          push: true
          tags: ${{ steps.meta-backend.outputs.tags }}
          labels: ${{ steps.meta-backend.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

      - name: Extract metadata for Frontend
        id: meta-frontend
        uses: docker/metadata-action@v4
        with:
          images: superadmin/frontend
          tags: |
            type=ref,event=branch
            type=sha,prefix={{branch}}-

      - name: Build and Push Frontend Image
        uses: docker/build-push-action@v4
        with:
          context: ./web
          file: ./docker/Dockerfile.frontend
          push: true
          tags: ${{ steps.meta-frontend.outputs.tags }}
          labels: ${{ steps.meta-frontend.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

  # ==================== 部署到测试环境 ====================
  deploy-staging:
    name: Deploy to Staging
    needs: build-docker
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/develop'
    environment:
      name: staging
      url: https://staging.superadmin.com

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Deploy to Staging Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          script: |
            cd /opt/superadmin
            docker-compose pull
            docker-compose up -d --no-deps --build
            docker-compose exec -T backend php think migrate:run
            docker-compose exec -T backend php think cache:clear

      - name: Run Smoke Tests
        run: |
          sleep 30
          curl -f https://staging.superadmin.com/health || exit 1

  # ==================== 部署到生产环境 ====================
  deploy-production:
    name: Deploy to Production
    needs: build-docker
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    environment:
      name: production
      url: https://superadmin.com

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Create Deployment
        id: deployment
        uses: actions/github-script@v6
        with:
          script: |
            const deployment = await github.rest.repos.createDeployment({
              owner: context.repo.owner,
              repo: context.repo.repo,
              ref: context.sha,
              environment: 'production',
              required_contexts: [],
            })
            return deployment.data.id

      - name: Deploy to Production Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd /opt/superadmin

            # 金丝雀部署 (10%流量)
            docker-compose -f docker-compose.canary.yml pull
            docker-compose -f docker-compose.canary.yml up -d

            # 等待健康检查
            sleep 60

            # 检查错误率
            ERROR_RATE=$(curl -s http://localhost:9090/api/v1/query?query=rate(http_requests_total{status=~"5.."}[5m]) | jq '.data.result[0].value[1]')

            if [ $(echo "$ERROR_RATE < 0.01" | bc) -eq 1 ]; then
              echo "Canary deployment healthy, proceeding with full rollout"
              docker-compose pull
              docker-compose up -d
              docker-compose exec -T backend php think migrate:run
              docker-compose exec -T backend php think cache:clear
            else
              echo "Canary deployment failed, rolling back"
              docker-compose -f docker-compose.canary.yml down
              exit 1
            fi

      - name: Update Deployment Status
        if: always()
        uses: actions/github-script@v6
        with:
          script: |
            await github.rest.repos.createDeploymentStatus({
              owner: context.repo.owner,
              repo: context.repo.repo,
              deployment_id: ${{ steps.deployment.outputs.result }},
              state: '${{ job.status }}' === 'success' ? 'success' : 'failure',
              environment_url: 'https://superadmin.com',
            })

      - name: Notify Deployment
        if: always()
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: 'Production deployment ${{ job.status }}'
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

### 2.2 Docker配置完善

创建优化的Dockerfile：

```dockerfile
# docker/Dockerfile.backend
FROM php:8.1-fpm-alpine AS base

# 安装系统依赖
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    redis \
    mysql-client \
    && rm -rf /var/cache/apk/*

# 安装PHP扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        gd \
        bcmath \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

# 配置OPcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ===== 开发构建 =====
FROM base AS development

COPY . .

RUN composer install --prefer-dist --no-progress \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/public

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

# ===== 生产构建 =====
FROM base AS production

COPY . .

RUN composer install --no-dev --optimize-autoloader --classmap-authoritative \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/public \
    && rm -rf tests/ .git/ .github/

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD curl -f http://localhost/health || exit 1

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

```dockerfile
# docker/Dockerfile.frontend
FROM node:18-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --prefer-offline --no-audit

COPY . .
RUN npm run build

# ===== 生产镜像 =====
FROM nginx:alpine AS production

COPY --from=builder /app/dist /usr/share/nginx/html
COPY docker/nginx-frontend.conf /etc/nginx/conf.d/default.conf

# Gzip压缩配置
RUN echo "gzip on;" >> /etc/nginx/nginx.conf \
    && echo "gzip_vary on;" >> /etc/nginx/nginx.conf \
    && echo "gzip_min_length 1024;" >> /etc/nginx/nginx.conf \
    && echo "gzip_types text/plain text/css application/json application/javascript text/xml application/xml;" >> /etc/nginx/nginx.conf

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s CMD wget --no-verbose --tries=1 --spider http://localhost/ || exit 1

CMD ["nginx", "-g", "daemon off;"]
```

---

### 2.3 环境配置管理

创建多环境配置：

```bash
# scripts/deploy.sh
#!/bin/bash

set -e

ENVIRONMENT=$1
VERSION=$2

if [ -z "$ENVIRONMENT" ] || [ -z "$VERSION" ]; then
    echo "Usage: ./deploy.sh <environment> <version>"
    echo "Example: ./deploy.sh production v2.4.0"
    exit 1
fi

echo "Deploying SuperAdmin $VERSION to $ENVIRONMENT..."

# 加载环境变量
source .env.$ENVIRONMENT

# 备份当前版本
echo "Creating backup..."
docker-compose exec -T mysql mysqldump -u root -p$DB_ROOT_PASSWORD $DB_DATABASE > backup_$(date +%Y%m%d_%H%M%S).sql

# 拉取新镜像
echo "Pulling new images..."
docker-compose pull

# 停止旧容器
echo "Stopping old containers..."
docker-compose down

# 启动新容器
echo "Starting new containers..."
docker-compose up -d

# 运行数据库迁移
echo "Running migrations..."
docker-compose exec -T backend php think migrate:run

# 清除缓存
echo "Clearing cache..."
docker-compose exec -T backend php think cache:clear

# 健康检查
echo "Health check..."
sleep 10
curl -f http://localhost/health || (echo "Health check failed" && exit 1)

echo "Deployment completed successfully!"
```

---

## 三、监控告警系统搭建（Week 4）

**目标**: 建立完整的监控告警体系

### 3.1 Sentry集成（应用性能监控）

#### Step 1: 后端集成Sentry

```bash
composer require sentry/sentry-laravel
```

```php
<?php
// config/sentry.php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    // 采样率
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 1.0),
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 1.0),

    // 环境
    'environment' => env('APP_ENV', 'production'),

    // 忽略的异常
    'ignore_exceptions' => [
        \think\exception\ValidateException::class,
        \think\exception\HttpException::class,
    ],

    // 性能监控
    'traces_sampler' => function (array $context): float {
        // 对API请求采样100%
        if (str_contains($context['url'] ?? '', '/api/')) {
            return 1.0;
        }
        // 其他请求采样10%
        return 0.1;
    },

    // 面包屑
    'breadcrumbs' => [
        'sql_queries' => true,
        'sql_bindings' => true,
        'cache' => true,
    ],

    // 发送之前的处理
    'before_send' => function (\Sentry\Event $event): ?\Sentry\Event {
        // 过滤敏感信息
        $event->setExtra('sanitized', true);
        return $event;
    },
];
```

```php
<?php
// app/middleware/SentryContext.php

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use Sentry\State\Scope;

class SentryContext
{
    public function handle(Request $request, Closure $next): Response
    {
        \Sentry\configureScope(function (Scope $scope) use ($request): void {
            // 设置用户信息
            if ($user = $request->user()) {
                $scope->setUser([
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ]);
            }

            // 设置请求信息
            $scope->setContext('request', [
                'url' => $request->url(true),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('user-agent'),
            ]);

            // 设置标签
            $scope->setTags([
                'environment' => env('APP_ENV'),
                'server' => gethostname(),
                'php_version' => PHP_VERSION,
            ]);
        });

        return $next($request);
    }
}
```

#### Step 2: 前端集成Sentry

```typescript
// web/src/plugins/sentry.ts
import * as Sentry from "@sentry/vue"
import type { App } from 'vue'
import router from '/@/router'

export function setupSentry(app: App) {
  if (import.meta.env.PROD && import.meta.env.VITE_SENTRY_DSN) {
    Sentry.init({
      app,
      dsn: import.meta.env.VITE_SENTRY_DSN,
      environment: import.meta.env.VITE_APP_ENV,

      // 性能监控
      integrations: [
        new Sentry.BrowserTracing({
          routingInstrumentation: Sentry.vueRouterInstrumentation(router),
          tracePropagationTargets: ["localhost", /^https:\/\/api\.superadmin\.com/],
        }),
        new Sentry.Replay({
          maskAllText: false,
          blockAllMedia: false,
        }),
      ],

      // 采样率
      tracesSampleRate: 1.0,
      replaysSessionSampleRate: 0.1,
      replaysOnErrorSampleRate: 1.0,

      // 忽略的错误
      ignoreErrors: [
        'Network request failed',
        'ResizeObserver loop limit exceeded',
      ],

      // 发送前处理
      beforeSend(event, hint) {
        // 过滤敏感信息
        if (event.request?.headers) {
          delete event.request.headers['Authorization']
          delete event.request.headers['Cookie']
        }
        return event
      },
    })
  }
}
```

```typescript
// web/src/main.ts
import { createApp } from 'vue'
import App from './App.vue'
import { setupSentry } from '/@/plugins/sentry'

const app = createApp(App)

// 集成Sentry
setupSentry(app)

app.mount('#app')
```

---

### 3.2 Prometheus + Grafana（系统监控）

#### Step 1: 安装Prometheus Exporter

```bash
composer require promphp/prometheus_client_php
```

```php
<?php
// app/admin/controller/Metrics.php

namespace app\admin\controller;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;
use think\Response;

class Metrics
{
    /**
     * Prometheus指标端点
     */
    public function index(): Response
    {
        $adapter = new Redis([
            'host' => env('redis.host', '127.0.0.1'),
            'port' => env('redis.port', 6379),
            'password' => env('redis.password', null),
            'database' => 1,
        ]);

        $registry = new CollectorRegistry($adapter);

        // 收集系统指标
        $this->collectSystemMetrics($registry);

        // 收集应用指标
        $this->collectAppMetrics($registry);

        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        return response($result)
            ->contentType('text/plain; version=0.0.4');
    }

    /**
     * 收集系统指标
     */
    private function collectSystemMetrics(CollectorRegistry $registry): void
    {
        // CPU使用率
        $cpuGauge = $registry->getOrRegisterGauge(
            'superadmin',
            'cpu_usage_percent',
            'CPU usage percentage'
        );
        $cpuGauge->set(sys_getloadavg()[0]);

        // 内存使用
        $memoryGauge = $registry->getOrRegisterGauge(
            'superadmin',
            'memory_usage_bytes',
            'Memory usage in bytes'
        );
        $memoryGauge->set(memory_get_usage(true));

        // 数据库连接数
        $dbCounter = $registry->getOrRegisterGauge(
            'superadmin',
            'database_connections',
            'Active database connections'
        );
        // $dbCounter->set($this->getActiveConnections());
    }

    /**
     * 收集应用指标
     */
    private function collectAppMetrics(CollectorRegistry $registry): void
    {
        // API请求总数
        $requestCounter = $registry->getOrRegisterCounter(
            'superadmin',
            'http_requests_total',
            'Total HTTP requests',
            ['method', 'endpoint', 'status']
        );

        // API响应时间
        $durationHistogram = $registry->getOrRegisterHistogram(
            'superadmin',
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['method', 'endpoint'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]
        );

        // 在线用户数
        $activeUsersGauge = $registry->getOrRegisterGauge(
            'superadmin',
            'active_users',
            'Number of active users'
        );
        // $activeUsersGauge->set($this->getActiveUsers());
    }
}
```

#### Step 2: 请求追踪中间件

```php
<?php
// app/common/middleware/MetricsCollector.php

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class MetricsCollector
{
    private CollectorRegistry $registry;

    public function __construct()
    {
        $adapter = new Redis([
            'host' => env('redis.host', '127.0.0.1'),
            'port' => env('redis.port', 6379),
            'database' => 1,
        ]);

        $this->registry = new CollectorRegistry($adapter);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;

        // 记录请求总数
        $counter = $this->registry->getOrRegisterCounter(
            'superadmin',
            'http_requests_total',
            'Total HTTP requests',
            ['method', 'endpoint', 'status']
        );
        $counter->inc([
            $request->method(),
            $this->normalizePath($request->pathinfo()),
            (string) $response->getCode(),
        ]);

        // 记录响应时间
        $histogram = $this->registry->getOrRegisterHistogram(
            'superadmin',
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['method', 'endpoint'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]
        );
        $histogram->observe($duration, [
            $request->method(),
            $this->normalizePath($request->pathinfo()),
        ]);

        return $response;
    }

    /**
     * 规范化路径（移除ID等动态参数）
     */
    private function normalizePath(string $path): string
    {
        // 将 /admin/user/123 转换为 /admin/user/:id
        return preg_replace('/\/\d+/', '/:id', $path);
    }
}
```

#### Step 3: Prometheus配置

```yaml
# docker/prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  # SuperAdmin后端
  - job_name: 'superadmin-backend'
    static_configs:
      - targets: ['backend:80']
    metrics_path: '/admin/metrics'

  # MySQL
  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql-exporter:9104']

  # Redis
  - job_name: 'redis'
    static_configs:
      - targets: ['redis-exporter:9121']

  # Node Exporter（系统指标）
  - job_name: 'node'
    static_configs:
      - targets: ['node-exporter:9100']
```

#### Step 4: 告警规则

```yaml
# docker/prometheus-rules.yml
groups:
  - name: superadmin_alerts
    rules:
      # API错误率告警
      - alert: HighErrorRate
        expr: rate(superadmin_http_requests_total{status=~"5.."}[5m]) > 0.05
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "API错误率过高"
          description: "最近5分钟API错误率超过5%"

      # API响应时间告警
      - alert: SlowResponse
        expr: histogram_quantile(0.95, rate(superadmin_http_request_duration_seconds_bucket[5m])) > 1
        for: 10m
        labels:
          severity: warning
        annotations:
          summary: "API响应时间过长"
          description: "95%请求响应时间超过1秒"

      # 数据库连接告警
      - alert: DatabaseConnectionFailure
        expr: mysql_up == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "数据库连接失败"

      # Redis连接告警
      - alert: RedisConnectionFailure
        expr: redis_up == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Redis连接失败"

      # CPU使用率告警
      - alert: HighCPUUsage
        expr: superadmin_cpu_usage_percent > 80
        for: 15m
        labels:
          severity: warning
        annotations:
          summary: "CPU使用率过高"
          description: "CPU使用率超过80%已持续15分钟"

      # 内存使用告警
      - alert: HighMemoryUsage
        expr: superadmin_memory_usage_bytes / 1024 / 1024 / 1024 > 2
        for: 15m
        labels:
          severity: warning
        annotations:
          summary: "内存使用过高"
          description: "内存使用超过2GB"

      # 活跃用户数突增
      - alert: UnusualActiveUsers
        expr: abs(superadmin_active_users - avg_over_time(superadmin_active_users[1h])) > 100
        for: 5m
        labels:
          severity: info
        annotations:
          summary: "活跃用户数异常"
          description: "活跃用户数与1小时平均值相差超过100"
```

#### Step 5: Grafana仪表板

创建JSON仪表板配置（保存为 `docker/grafana-dashboard.json`）：

```json
{
  "dashboard": {
    "title": "SuperAdmin Monitoring",
    "panels": [
      {
        "title": "API请求总数",
        "targets": [
          {
            "expr": "rate(superadmin_http_requests_total[5m])"
          }
        ]
      },
      {
        "title": "API响应时间(P95)",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(superadmin_http_request_duration_seconds_bucket[5m]))"
          }
        ]
      },
      {
        "title": "错误率",
        "targets": [
          {
            "expr": "rate(superadmin_http_requests_total{status=~\"5..\"}[5m])"
          }
        ]
      }
    ]
  }
}
```

---

### 3.3 日志聚合（ELK Stack）

#### Step 1: 结构化日志输出

```php
<?php
// config/log.php

return [
    'default' => env('log.channel', 'stack'),

    'channels' => [
        'stack' => [
            'type' => 'stack',
            'channels' => ['daily', 'elasticsearch'],
        ],

        'daily' => [
            'type' => 'daily',
            'path' => runtime_path() . 'log/',
            'level' => 'debug',
            'max_files' => 30,
            'json' => true,  // JSON格式输出
        ],

        'elasticsearch' => [
            'type' => 'elasticsearch',
            'host' => env('ELASTICSEARCH_HOST', 'elasticsearch:9200'),
            'index' => 'superadmin-logs',
            'level' => 'info',
        ],
    ],
];
```

```php
<?php
// app/common/library/Log.php

namespace app\common\library;

use think\facade\Log as ThinkLog;

class Log
{
    /**
     * 记录结构化日志
     */
    public static function structured(
        string $level,
        string $message,
        array $context = []
    ): void {
        $data = array_merge($context, [
            'timestamp' => date('Y-m-d H:i:s'),
            'hostname' => gethostname(),
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'trace_id' => request()->header('X-Request-ID', uniqid()),
        ]);

        ThinkLog::record($message, $level, $data);
    }

    /**
     * 记录API请求
     */
    public static function apiRequest(
        string $method,
        string $url,
        array $params,
        float $duration
    ): void {
        self::structured('info', 'API Request', [
            'type' => 'api_request',
            'method' => $method,
            'url' => $url,
            'params' => $params,
            'duration' => $duration,
            'user_id' => auth()->id ?? null,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * 记录数据库查询
     */
    public static function dbQuery(string $sql, array $bindings, float $time): void
    {
        self::structured('debug', 'Database Query', [
            'type' => 'database_query',
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
        ]);
    }
}
```

#### Step 2: Filebeat配置

```yaml
# docker/filebeat.yml
filebeat.inputs:
  - type: log
    enabled: true
    paths:
      - /var/www/runtime/log/*.log
    json.keys_under_root: true
    json.add_error_key: true
    fields:
      app: superadmin
      env: ${ENV:production}

processors:
  - add_host_metadata: ~
  - add_docker_metadata: ~

output.elasticsearch:
  hosts: ["elasticsearch:9200"]
  index: "superadmin-logs-%{+yyyy.MM.dd}"

setup.kibana:
  host: "kibana:5601"

setup.dashboards.enabled: true
```

---

### 3.4 告警通知配置

#### Step 1: AlertManager配置

```yaml
# docker/alertmanager.yml
global:
  resolve_timeout: 5m

route:
  group_by: ['alertname', 'severity']
  group_wait: 10s
  group_interval: 10s
  repeat_interval: 12h
  receiver: 'default'
  routes:
    - match:
        severity: critical
      receiver: 'critical'
    - match:
        severity: warning
      receiver: 'warning'

receivers:
  - name: 'default'
    webhook_configs:
      - url: 'http://webhook:8080/alert'

  - name: 'critical'
    webhook_configs:
      - url: 'http://webhook:8080/alert/critical'
    email_configs:
      - to: 'ops@superadmin.com'
        from: 'alert@superadmin.com'
        smarthost: 'smtp.example.com:587'
        auth_username: 'alert@superadmin.com'
        auth_password: '${SMTP_PASSWORD}'
        headers:
          Subject: '[CRITICAL] SuperAdmin Alert'

  - name: 'warning'
    webhook_configs:
      - url: 'http://webhook:8080/alert/warning'
```

#### Step 2: 钉钉/企业微信通知

```php
<?php
// app/common/library/AlertNotifier.php

namespace app\common\library;

use GuzzleHttp\Client;

class AlertNotifier
{
    /**
     * 发送钉钉通知
     */
    public static function sendDingTalk(string $message, string $level = 'info'): void
    {
        $webhook = env('DINGTALK_WEBHOOK');
        if (!$webhook) return;

        $client = new Client();

        $data = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => "SuperAdmin Alert - {$level}",
                'text' => "## {$level}\n\n{$message}\n\n" .
                         "时间: " . date('Y-m-d H:i:s') . "\n\n" .
                         "环境: " . env('APP_ENV')
            ],
        ];

        $client->post($webhook, [
            'json' => $data,
        ]);
    }

    /**
     * 发送企业微信通知
     */
    public static function sendWeWork(string $message, string $level = 'info'): void
    {
        $webhook = env('WEWORK_WEBHOOK');
        if (!$webhook) return;

        $client = new Client();

        $data = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => "## SuperAdmin Alert - {$level}\n\n" .
                            "{$message}\n\n" .
                            "**时间**: " . date('Y-m-d H:i:s') . "\n\n" .
                            "**环境**: " . env('APP_ENV')
            ],
        ];

        $client->post($webhook, [
            'json' => $data,
        ]);
    }
}
```

---

## 四、API文档自动化（Week 5）

**目标**: 自动生成和维护API文档

### 4.1 Swagger注解

```php
<?php
// app/admin/controller/User.php

namespace app\admin\controller;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="用户管理",
 *     description="用户相关操作"
 * )
 */
class User extends Backend
{
    /**
     * @OA\Get(
     *     path="/admin/user/index",
     *     summary="获取用户列表",
     *     tags={"用户管理"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="每页数量",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="搜索关键词",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=1),
     *             @OA\Property(property="msg", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="list",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="limit", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index(): void
    {
        // ...
    }

    /**
     * @OA\Post(
     *     path="/admin/user/add",
     *     summary="创建用户",
     *     tags={"用户管理"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password", "email"},
     *             @OA\Property(property="username", type="string", example="testuser"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="email", type="string", example="test@example.com"),
     *             @OA\Property(property="status", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="创建成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=1),
     *             @OA\Property(property="msg", type="string", example="创建成功"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function add(): void
    {
        // ...
    }
}

/**
 * @OA\Schema(
 *     schema="User",
 *     title="用户",
 *     description="用户模型",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="username", type="string", example="testuser"),
 *     @OA\Property(property="email", type="string", example="test@example.com"),
 *     @OA\Property(property="status", type="integer", example=1),
 *     @OA\Property(property="create_time", type="string", format="date-time"),
 *     @OA\Property(property="update_time", type="string", format="date-time")
 * )
 */
```

### 4.2 自动生成文档命令

```php
<?php
// app/command/ApiDoc.php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use OpenApi\Generator;

class ApiDoc extends Command
{
    protected function configure()
    {
        $this->setName('api:doc')
            ->setDescription('Generate API documentation');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('Generating API documentation...');

        $openapi = Generator::scan([
            app_path('admin/controller'),
            app_path('api/controller'),
        ]);

        // 保存为JSON
        file_put_contents(
            public_path() . 'api-docs/openapi.json',
            $openapi->toJson()
        );

        // 保存为YAML
        file_put_contents(
            public_path() . 'api-docs/openapi.yaml',
            $openapi->toYaml()
        );

        $output->writeln('API documentation generated successfully!');
        $output->writeln('View at: http://localhost:8000/api-docs');

        return 0;
    }
}
```

### 4.3 Swagger UI集成

```html
<!-- public/api-docs/index.html -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>SuperAdmin API文档</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "./openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
```

---

## 五、性能优化（Week 6-7）

### 5.1 数据库优化

```sql
-- 添加索引
ALTER TABLE `ba_admin` ADD INDEX idx_status_create_time (`status`, `create_time`);
ALTER TABLE `ba_user` ADD INDEX idx_group_status (`group_id`, `status`);
ALTER TABLE `ba_admin_log` ADD INDEX idx_admin_create (`admin_id`, `create_time`);

-- 优化配置
SET GLOBAL innodb_buffer_pool_size = 2G;
SET GLOBAL query_cache_size = 256M;
SET GLOBAL max_connections = 500;
```

### 5.2 Redis优化

```php
// config/cache.php修改
'redis' => [
    'type'       => 'redis',
    'host'       => env('redis.host', '127.0.0.1'),
    'port'       => env('redis.port', 6379),
    'password'   => env('redis.password', ''),
    'select'     => env('redis.select', 0),
    'timeout'    => 0,
    'persistent' => true,  // 持久连接
    'prefix'     => env('cache.prefix', 'superadmin:'),
    'expire'     => 0,
    'serialize'  => ['serialize', 'unserialize'],
],
```

### 5.3 前端优化

参考评估报告中的前端优化章节实施。

---

## 六、验证与上线（Week 8-12）

### 6.1 压力测试

使用Apache Bench或JMeter进行压力测试。

### 6.2 安全审计

运行安全扫描工具。

### 6.3 文档完善

完善所有文档。

### 6.4 团队培训

培训团队使用新的工具和流程。

---

## 📅 详细时间表

| 周次 | 任务 | 交付物 | 负责人 |
|------|------|--------|--------|
| Week 1 | 后端测试框架搭建 | PHPUnit配置+BaseService测试 | 后端团队 |
| Week 2 | 前端测试框架搭建 | Vitest配置+组件测试 | 前端团队 |
| Week 3 | CI/CD配置 | GitHub Actions配置 | DevOps |
| Week 4 | 监控系统搭建 | Sentry+Prometheus配置 | 运维团队 |
| Week 5 | 测试用例编写 | 150+后端用例+100+前端用例 | 全体 |
| Week 6 | API文档生成 | Swagger文档 | 后端团队 |
| Week 7 | 性能优化 | 数据库优化+缓存优化 | 全体 |
| Week 8 | 压力测试 | 测试报告 | 测试团队 |
| Week 9 | 安全审计 | 安全报告 | 安全团队 |
| Week 10 | 文档完善 | 完整文档 | 全体 |
| Week 11 | 团队培训 | 培训材料 | 技术负责人 |
| Week 12 | 上线验收 | 验收报告 | PM |

---

## 🎯 成功标准

完成后项目应达到：

- ✅ 测试覆盖率≥80%
- ✅ CI/CD全自动化
- ✅ 监控告警完善
- ✅ API文档完整
- ✅ 性能指标优秀
- ✅ 安全审计通过
- ✅ **综合评分: 5/5（卓越）**

---

**文档版本**: v1.0
**创建日期**: 2025-10-26
**维护者**: 技术团队
