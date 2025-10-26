# Service 层架构说明

## 概述

Service层是介于Controller和Model之间的业务逻辑层,负责处理复杂的业务逻辑、事务管理和数据处理。通过引入Service层,可以有效地将业务逻辑从Controller中分离,提高代码的可维护性和可测试性。

## 目录结构

```
app/common/service/
├── BaseService.php          # 基础服务类,提供通用CRUD方法
├── UserService.php          # 用户服务
├── AuthService.php          # 认证服务
└── ...                      # 其他业务服务
```

## 架构设计

### 1. BaseService 基础服务类

BaseService 提供了通用的数据操作方法,所有具体的Service类都应继承此类。

**主要功能:**
- 单条查询 (find)
- 列表查询 (select)
- 分页查询 (paginate)
- 创建记录 (create)
- 更新记录 (update)
- 删除记录 (delete)
- 批量创建 (batchCreate)
- 统计数量 (count)

**使用示例:**
```php
<?php
namespace app\common\service;

use app\common\model\Article;

class ArticleService extends BaseService
{
    public function __construct()
    {
        $this->model = new Article();
    }
    
    // 可以直接使用父类方法
    public function getArticleList(array $where = [])
    {
        return $this->select($where, ['id' => 'desc'], 10);
    }
}
```

### 2. UserService 用户服务

处理用户相关的所有业务逻辑。

**主要方法:**
- `getUserList()` - 获取用户列表(带分页)
- `createUser()` - 创建用户
- `updateUser()` - 更新用户信息
- `deleteUser()` - 删除用户
- `changeUserStatus()` - 修改用户状态
- `resetUserPassword()` - 重置用户密码
- `usernameExists()` - 检查用户名是否存在
- `emailExists()` - 检查邮箱是否存在
- `mobileExists()` - 检查手机号是否存在
- `getUserInfo()` - 获取用户详情
- `updateUserMoney()` - 更新用户余额
- `updateLastLogin()` - 更新最后登录信息
- `getUserStatistics()` - 获取用户统计信息

**使用示例:**
```php
// 在Controller中使用
public function initialize(): void
{
    parent::initialize();
    $this->userService = new UserService();
}

public function add(): void
{
    $data = $this->request->post();
    
    try {
        $user = $this->userService->createUser($data);
        if ($user) {
            $this->success('添加成功');
        }
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
    }
}
```

### 3. AuthService 认证服务

处理用户登录、注册、登出等认证相关的业务逻辑。

**主要方法:**
- `login()` - 用户登录
- `register()` - 用户注册
- `logout()` - 用户登出
- `getLoginConfig()` - 获取登录配置信息
- `isLogin()` - 检查是否已登录
- `getUserInfo()` - 获取当前用户信息
- `getUserId()` - 获取当前用户ID

**使用示例:**
```php
// 在Controller中使用
public function initialize(): void
{
    parent::initialize();
    $this->authService = new AuthService($this->auth);
}

public function checkIn(): void
{
    $params = $this->request->post();
    
    try {
        $result = $this->authService->login(
            $params['username'],
            $params['password'],
            !empty($params['keep'])
        );
        
        if ($result['success']) {
            $this->success($result['msg'], $result['data']);
        } else {
            $this->error($result['msg']);
        }
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
    }
}
```

## 开发规范

### 1. Service类命名规范

- 类名使用大驼峰命名法,以 `Service` 结尾
- 文件名与类名保持一致
- 放置在 `app/common/service/` 目录下

示例:
- UserService.php
- AuthService.php
- OrderService.php

### 2. 方法命名规范

- 使用小驼峰命名法
- 方法名应清晰表达业务意图
- 增删改查使用统一前缀:
  - `get` - 查询单条或列表
  - `create` - 创建
  - `update` - 更新
  - `delete` - 删除
  - `check` - 检查/验证

示例:
```php
getUserInfo()      // 获取用户信息
createUser()       // 创建用户
updateUserStatus() // 更新用户状态
deleteUser()       // 删除用户
checkUsername()    // 检查用户名
```

### 3. 事务处理规范

涉及多表操作或需要保证数据一致性的操作,必须使用事务:

```php
public function createOrder(array $data): bool
{
    try {
        $this->model->startTrans();
        
        // 业务逻辑处理
        $order = $this->create($data);
        
        // 更新库存
        $this->updateStock($data['goods_id'], $data['num']);
        
        // 更新用户余额
        $this->updateUserMoney($data['user_id'], -$data['amount']);
        
        $this->model->commit();
        return true;
    } catch (\Throwable $e) {
        $this->model->rollback();
        throw $e;
    }
}
```

### 4. 异常处理规范

Service层应该抛出异常,由Controller层捕获并处理:

```php
// Service层
public function createUser(array $data): User
{
    if ($this->usernameExists($data['username'])) {
        throw new \Exception('用户名已存在');
    }
    
    // 继续处理...
}

// Controller层
public function add(): void
{
    try {
        $user = $this->userService->createUser($data);
        $this->success('添加成功');
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
    }
}
```

### 5. 返回值规范

- 查询单条:返回 `Model|null`
- 查询列表:返回 `array`
- 创建:返回 `Model|false`
- 更新/删除:返回 `bool`
- 复杂操作:返回 `array` (包含success、msg、data等字段)

```php
// 示例1: 简单操作
public function deleteUser(int $userId): bool
{
    return $this->delete($userId);
}

// 示例2: 复杂操作
public function login(string $username, string $password): array
{
    return [
        'success' => true,
        'msg'     => '登录成功',
        'data'    => [
            'userInfo' => $userInfo,
            'token'    => $token,
        ]
    ];
}
```

## 最佳实践

### 1. 单一职责原则

每个Service类应该只负责一个业务领域:

```php
// ✅ 好的做法
UserService      - 负责用户管理
OrderService     - 负责订单管理
PaymentService   - 负责支付处理

// ❌ 不好的做法
CommonService    - 包含所有业务逻辑
```

### 2. 依赖注入

Service之间有依赖关系时,通过构造函数注入:

```php
class OrderService extends BaseService
{
    protected UserService $userService;
    protected GoodsService $goodsService;
    
    public function __construct()
    {
        $this->model = new Order();
        $this->userService = new UserService();
        $this->goodsService = new GoodsService();
    }
}
```

### 3. 避免循环依赖

如果A Service需要B Service,B Service又需要A Service,说明职责划分有问题,需要重新设计。

### 4. 合理使用缓存

对于频繁查询且变化不大的数据,可以在Service层添加缓存:

```php
use think\facade\Cache;

public function getUserInfo(int $userId): ?User
{
    $cacheKey = "user:info:{$userId}";
    
    $user = Cache::get($cacheKey);
    if ($user) {
        return $user;
    }
    
    $user = $this->find($userId);
    if ($user) {
        Cache::set($cacheKey, $user, 3600);
    }
    
    return $user;
}
```

### 5. 日志记录

重要的业务操作应该记录日志:

```php
use think\facade\Log;

public function updateUserMoney(int $userId, float $amount): bool
{
    try {
        // 执行业务逻辑...
        
        Log::info("用户余额变动", [
            'user_id' => $userId,
            'amount'  => $amount,
            'before'  => $oldMoney,
            'after'   => $newMoney,
        ]);
        
        return true;
    } catch (\Throwable $e) {
        Log::error("用户余额变动失败: " . $e->getMessage());
        throw $e;
    }
}
```

## Controller重构指南

### 重构前(控制器包含业务逻辑)

```php
class User extends Backend
{
    public function add(): void
    {
        $data = $this->request->post();
        
        // 检查用户名是否存在
        $exists = $this->model->where('username', $data['username'])->find();
        if ($exists) {
            $this->error('用户名已存在');
        }
        
        // 处理密码
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // 保存数据
        $this->model->save($data);
        
        $this->success('添加成功');
    }
}
```

### 重构后(使用Service层)

```php
class User extends Backend
{
    protected UserService $userService;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->userService = new UserService();
    }
    
    public function add(): void
    {
        $data = $this->request->post();
        
        try {
            $user = $this->userService->createUser($data);
            if ($user) {
                $this->success('添加成功');
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
```

## 测试建议

Service层应该编写单元测试,确保业务逻辑的正确性:

```php
<?php
namespace tests\service;

use PHPUnit\Framework\TestCase;
use app\common\service\UserService;

class UserServiceTest extends TestCase
{
    protected UserService $userService;
    
    protected function setUp(): void
    {
        $this->userService = new UserService();
    }
    
    public function testCreateUser()
    {
        $data = [
            'username' => 'testuser',
            'password' => '123456',
            'email'    => 'test@example.com',
        ];
        
        $user = $this->userService->createUser($data);
        
        $this->assertNotFalse($user);
        $this->assertEquals('testuser', $user->username);
    }
}
```

## 迁移计划

建议按照以下步骤逐步迁移到Service层架构:

1. **第一阶段**: 新功能直接使用Service层开发
2. **第二阶段**: 重构核心模块(用户、订单等)
3. **第三阶段**: 逐步重构其他模块
4. **第四阶段**: 补充单元测试

## 注意事项

1. **不要在Service层直接使用Request对象**,所有参数应通过方法参数传入
2. **不要在Service层直接输出响应**,应该返回数据由Controller处理
3. **复杂的查询逻辑应该放在Service层**,而不是Controller
4. **Service层方法应该是纯粹的业务逻辑**,不包含任何表现层的内容
5. **合理控制Service类的大小**,如果一个Service类超过500行,考虑拆分

## 相关文档

- [架构说明](01-项目技术架构评估与规划.md)
- [API接口规范](./API_SPECIFICATION.md)
- [数据库设计规范](./DATABASE_DESIGN.md)