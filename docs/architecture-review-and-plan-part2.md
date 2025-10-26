# BuildAdmin 项目技术架构评估与规划方案 (续)

## 6. 性能优化策略 (续)

### 6.2 缓存优化 (续)

#### 6.2.2 缓存预热

```php
<?php
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\service\MenuService;
use app\admin\service\ConfigService;

class CacheWarmup extends Command
{
    protected function configure()
    {
        $this->setName('cache:warmup')
            ->setDescription('预热系统缓存');
    }
    
    protected function execute(Input $input, Output $output)
    {
        $output->writeln('开始预热缓存...');
        
        // 1. 预热系统配置
        $configService = new ConfigService();
        $configService->getAllConfig();
        $output->writeln('✓ 系统配置已预热');
        
        // 2. 预热菜单树
        $menuService = new MenuService();
        $adminIds = Admin::column('id');
        foreach ($adminIds as $adminId) {
            $menuService->getUserMenus($adminId);
        }
        $output->writeln('✓ 菜单数据已预热');
        
        // 3. 预热权限规则
        // ...
        
        $output->writeln('缓存预热完成!');
    }
}
```

#### 6.2.3 缓存穿透/击穿/雪崩防护

**缓存穿透防护 (布隆过滤器)**:
```php
use think\facade\Cache;

class UserService
{
    /**
     * 防止缓存穿透
     */
    public function getUser(int $id): ?array
    {
        // 1. 布隆过滤器检查
        if (!$this->bloomFilter->mightContain($id)) {
            return null; // 一定不存在
        }
        
        // 2. 查询缓存
        $cacheKey = "user:{$id}";
        $user = Cache::get($cacheKey);
        
        if ($user !== false) {
            return $user;
        }
        
        // 3. 查询数据库
        $user = User::find($id);
        
        // 4. 缓存空对象防止穿透
        Cache::set($cacheKey, $user ?: [], $user ? 3600 : 60);
        
        return $user;
    }
}
```

**缓存击穿防护 (分布式锁)**:
```php
use think\facade\Cache;

public function getHotData(string $key): mixed
{
    $data = Cache::get($key);
    
    if ($data === false) {
        // 获取分布式锁
        $lock = Cache::lock($key . ':lock', 10);
        
        if ($lock->get()) {
            try {
                // 双重检查
                $data = Cache::get($key);
                if ($data === false) {
                    $data = $this->loadFromDatabase();
                    Cache::set($key, $data, 3600);
                }
            } finally {
                $lock->release();
            }
        } else {
            // 等待其他进程加载完成
            sleep(1);
            return $this->getHotData($key);
        }
    }
    
    return $data;
}
```

**缓存雪崩防护 (过期时间加随机值)**:
```php
// 设置缓存时添加随机过期时间
$expireTime = 3600 + rand(0, 600); // 3600-4200秒
Cache::set($key, $data, $expireTime);
```

### 6.3 前端性能优化

#### 6.3.1 代码分割与懒加载

```typescript
// router/index.ts
const routes = [
    {
        path: '/admin',
        component: () => import('/@/layouts/backend/index.vue'),
        children: [
            {
                path: 'dashboard',
                // 路由级代码分割
                component: () => import('/@/views/backend/dashboard/index.vue')
            }
        ]
    }
]
```

**Vite 分包配置**:
```typescript
// vite.config.ts
export default {
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // 框架核心
                    'vue-vendor': ['vue', 'vue-router', 'pinia'],
                    // UI 组件库
                    'element-plus': ['element-plus'],
                    // 工具库
                    'utils': ['lodash-es', 'axios', 'dayjs'],
                    // 图表库
                    'charts': ['echarts']
                }
            }
        }
    }
}
```

#### 6.3.2 资源优化

**图片优化**:
```typescript
// 使用 WebP 格式
<img :src="`${image}.webp`" :alt="title" loading="lazy" />

// 响应式图片
<picture>
    <source srcset="image-large.webp" media="(min-width: 1200px)">
    <source srcset="image-medium.webp" media="(min-width: 768px)">
    <img src="image-small.webp" alt="image">
</picture>
```

**虚拟滚动 (大列表优化)**:
```vue
<template>
    <el-table-v2
        :columns="columns"
        :data="data"
        :width="700"
        :height="400"
    />
</template>
```

#### 6.3.3 请求优化

**请求防抖/节流**:
```typescript
import { debounce } from 'lodash-es'

// 搜索防抖
const handleSearch = debounce((keyword: string) => {
    searchApi(keyword)
}, 300)
```

**请求取消**:
```typescript
let controller: AbortController | null = null

async function fetchData() {
    // 取消之前的请求
    if (controller) {
        controller.abort()
    }
    
    controller = new AbortController()
    
    try {
        const data = await createAxios({
            url: '/api/data',
            signal: controller.signal
        })
        return data
    } catch (error) {
        if (error.name === 'AbortError') {
            console.log('请求已取消')
        }
    }
}
```

### 6.4 性能监控

#### 6.4.1 后端性能监控

```php
<?php
namespace app\common\middleware;

use think\facade\Log;

class PerformanceMonitor
{
    public function handle($request, \Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $response = $next($request);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsage = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2);
        
        // 记录慢请求
        if ($executionTime > 1000) {
            Log::warning('慢请求', [
                'url' => $request->url(),
                'method' => $request->method(),
                'time' => $executionTime . 'ms',
                'memory' => $memoryUsage . 'MB'
            ]);
        }
        
        // 添加响应头
        $response->header([
            'X-Response-Time' => $executionTime . 'ms',
            'X-Memory-Usage' => $memoryUsage . 'MB'
        ]);
        
        return $response;
    }
}
```

#### 6.4.2 前端性能监控

```typescript
// utils/performance.ts
export function reportPerformance() {
    if (!window.performance) return
    
    const timing = window.performance.timing
    const metrics = {
        // DNS 查询时间
        dns: timing.domainLookupEnd - timing.domainLookupStart,
        // TCP 连接时间
        tcp: timing.connectEnd - timing.connectStart,
        // 请求响应时间
        request: timing.responseEnd - timing.requestStart,
        // DOM 解析时间
        domParse: timing.domInteractive - timing.domLoading,
        // 页面加载完成时间
        load: timing.loadEventEnd - timing.navigationStart
    }
    
    // 上报到监控平台
    reportToMonitor(metrics)
}
```

---

## 7. 安全架构方案

### 7.1 认证与授权安全

#### 7.1.1 密码安全

**密码策略**:
```php
class PasswordPolicy
{
    // 密码复杂度要求
    const MIN_LENGTH = 8;
    const REQUIRE_UPPERCASE = true;
    const REQUIRE_LOWERCASE = true;
    const REQUIRE_NUMBER = true;
    const REQUIRE_SPECIAL = true;
    
    /**
     * 验证密码强度
     */
    public static function validate(string $password): bool
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new \Exception('密码长度不能少于8位');
        }
        
        if (self::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            throw new \Exception('密码必须包含大写字母');
        }
        
        if (self::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            throw new \Exception('密码必须包含小写字母');
        }
        
        if (self::REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
            throw new \Exception('密码必须包含数字');
        }
        
        if (self::REQUIRE_SPECIAL && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new \Exception('密码必须包含特殊字符');
        }
        
        return true;
    }
    
    /**
     * 密码加密 (使用 Bcrypt)
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * 验证密码
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
```

#### 7.1.2 Token 安全

**Token 生成策略**:
```php
class TokenGenerator
{
    /**
     * 生成安全的 Token
     */
    public static function generate(): string
    {
        return bin2hex(random_bytes(32)); // 64字符
    }
    
    /**
     * 生成 JWT Token
     */
    public static function generateJWT(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", config('app.key'), true);
        $signature = base64_encode($signature);
        
        return "$header.$payload.$signature";
    }
}
```

**Token 刷新机制**:
```php
public function refreshToken(string $refreshToken): array
{
    // 1. 验证 RefreshToken
    $tokenData = Token::get($refreshToken, false);
    if (!$tokenData || $tokenData['type'] !== 'refresh') {
        throw new TokenException('Invalid refresh token');
    }
    
    // 2. 检查是否过期
    if ($tokenData['expiretime'] <= time()) {
        throw new TokenException('Refresh token expired');
    }
    
    // 3. 生成新的 Token
    $newToken = TokenGenerator::generate();
    $newRefreshToken = TokenGenerator::generate();
    
    // 4. 保存新 Token
    Token::set($newToken, 'admin', $tokenData['user_id'], 86400);
    Token::set($newRefreshToken, 'refresh', $tokenData['user_id'], 2592000);
    
    // 5. 删除旧 Token
    Token::delete($refreshToken);
    
    return [
        'token' => $newToken,
        'refresh_token' => $newRefreshToken
    ];
}
```

#### 7.1.3 防止暴力破解

**登录失败限制**:
```php
class LoginAttemptLimiter
{
    /**
     * 检查登录尝试次数
     */
    public function check(string $username, string $ip): void
    {
        $key = "login_attempts:{$username}:{$ip}";
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= 5) {
            $ttl = Cache::ttl($key);
            throw new \Exception("登录失败次数过多,请{$ttl}秒后重试");
        }
    }
    
    /**
     * 记录失败尝试
     */
    public function recordFailure(string $username, string $ip): void
    {
        $key = "login_attempts:{$username}:{$ip}";
        $attempts = Cache::get($key, 0);
        Cache::set($key, $attempts + 1, 1800); // 30分钟
    }
    
    /**
     * 清除记录
     */
    public function clear(string $username, string $ip): void
    {
        $key = "login_attempts:{$username}:{$ip}";
        Cache::delete($key);
    }
}
```

### 7.2 数据安全

#### 7.2.1 SQL 注入防护

```php
// ✅ 使用参数绑定
$admin = Admin::where('username', $username)->find();

// ✅ 使用查询构建器
$list = Admin::whereIn('id', $ids)->select();

// ❌ 避免直接拼接 SQL
$sql = "SELECT * FROM admin WHERE username = '{$username}'";
```

#### 7.2.2 XSS 防护

**后端过滤**:
```php
use think\facade\Request;

class XssFilter
{
    /**
     * 过滤 XSS
     */
    public static function clean(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * 清理数组
     */
    public static function cleanArray(array $data): array
    {
        return array_map(function($value) {
            return is_string($value) ? self::clean($value) : $value;
        }, $data);
    }
}

// 中间件中使用
public function handle($request, \Closure $next)
{
    $params = $request->param();
    $cleaned = XssFilter::cleanArray($params);
    $request->withInput($cleaned);
    
    return $next($request);
}
```

**前端防护**:
```typescript
// 使用 DOMPurify 清理 HTML
import DOMPurify from 'dompurify'

function sanitizeHtml(html: string): string {
    return DOMPurify.sanitize(html)
}
```

#### 7.2.3 CSRF 防护

```php
// 中间件
class VerifyCsrfToken
{
    public function handle($request, \Closure $next)
    {
        // GET 请求跳过
        if ($request->isGet()) {
            return $next($request);
        }
        
        // 验证 CSRF Token
        $token = $request->header('X-CSRF-TOKEN') 
              ?? $request->param('_token');
        
        if (!$token || !$this->verifyToken($token)) {
            throw new \Exception('CSRF token mismatch', 419);
        }
        
        return $next($request);
    }
    
    private function verifyToken(string $token): bool
    {
        $sessionToken = session('_token');
        return $token === $sessionToken;
    }
}
```

### 7.3 API 安全

#### 7.3.1 签名验证

```php
class ApiSignature
{
    /**
     * 生成签名
     */
    public static function generate(array $params, string $secret): string
    {
        ksort($params);
        $str = http_build_query($params) . $secret;
        return md5($str);
    }
    
    /**
     * 验证签名
     */
    public static function verify(array $params, string $sign, string $secret): bool
    {
        $timestamp = $params['timestamp'] ?? 0;
        
        // 时间戳验证 (5分钟内有效)
        if (abs(time() - $timestamp) > 300) {
            return false;
        }
        
        // 签名验证
        $expectedSign = self::generate($params, $secret);
        return $sign === $expectedSign;
    }
}

// 中间件使用
class ApiSignatureMiddleware
{
    public function handle($request, \Closure $next)
    {
        $params = $request->param();
        $sign = $request->header('X-Signature');
        $secret = config('api.secret');
        
        if (!ApiSignature::verify($params, $sign, $secret)) {
            throw new \Exception('签名验证失败', 401);
        }
        
        return $next($request);
    }
}
```

#### 7.3.2 频率限制

```php
class RateLimiter
{
    /**
     * 检查频率限制
     */
    public function check(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= $maxAttempts) {
            throw new \Exception('请求过于频繁', 429);
        }
        
        Cache::set($cacheKey, $attempts + 1, $decayMinutes * 60);
        
        return true;
    }
    
    /**
     * 获取剩余次数
     */
    public function remaining(string $key, int $maxAttempts = 60): int
    {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0);
        
        return max(0, $maxAttempts - $attempts);
    }
}

// 中间件使用
class ThrottleRequests
{
    public function handle($request, \Closure $next)
    {
        $key = $request->ip() . ':' . $request->path();
        $limiter = new RateLimiter();
        
        $limiter->check($key, 60, 1);
        
        $response = $next($request);
        
        // 添加响应头
        $response->header([
            'X-RateLimit-Limit' => 60,
            'X-RateLimit-Remaining' => $limiter->remaining($key, 60)
        ]);
        
        return $response;
    }
}
```

### 7.4 文件上传安全

```php
class SecureUpload
{
    // 允许的 MIME 类型
    const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];
    
    // 允许的扩展名
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    
    // 最大文件大小 (10MB)
    const MAX_SIZE = 10 * 1024 * 1024;
    
    /**
     * 验证上传文件
     */
    public function validate(\think\File $file): void
    {
        // 1. 验证文件大小
        if ($file->getSize() > self::MAX_SIZE) {
            throw new \Exception('文件大小不能超过10MB');
        }
        
        // 2. 验证 MIME 类型
        if (!in_array($file->getMime(), self::ALLOWED_MIMES)) {
            throw new \Exception('不允许的文件类型');
        }
        
        // 3. 验证扩展名
        $extension = strtolower($file->extension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception('不允许的文件扩展名');
        }
        
        // 4. 验证文件内容 (防止伪造扩展名)
        if (!$this->verifyFileContent($file, $extension)) {
            throw new \Exception('文件内容与扩展名不匹配');
        }
    }
    
    /**
     * 验证文件内容
     */
    private function verifyFileContent(\think\File $file, string $extension): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 8);
        fclose($handle);
        
        // 文件头魔数验证
        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47"],
            'gif' => ["GIF87a", "GIF89a"],
            'pdf' => ["%PDF"],
        ];
        
        if (!isset($signatures[$extension])) {
            return true;
        }
        
        foreach ($signatures[$extension] as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 生成安全的文件名
     */
    public function generateFilename(\think\File $file): string
    {
        return date('Ymd') . '/' . md5(uniqid() . $file->getBasename()) 
            . '.' . $file->extension();
    }
}
```

---

## 8. 扩展性与可维护性规划

### 8.1 代码规范

#### 8.1.1 PHP 代码规范 (PSR-12)

```php
<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Admin\Model\Admin;
use Think\Facade\Db;

/**
 * 管理员服务类
 */
class AdminService
{
    /**
     * 创建管理员
     *
     * @param array $data 管理员数据
     * @return Admin
     * @throws \Exception
     */
    public function create(array $data): Admin
    {
        return Db::transaction(function () use ($data) {
            $admin = Admin::create($data);
            
            return $admin;
        });
    }
    
    /**
     * 更新管理员
     *
     * @param int $id 管理员ID
     * @param array $data 更新数据
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $admin = Admin::find($id);
        if (!$admin) {
            throw new \Exception('管理员不存在');
        }
        
        return $admin->save($data);
    }
}
```

#### 8.1.2 TypeScript 代码规范

```typescript
/**
 * 用户信息接口
 */
interface UserInfo {
    id: number
    username: string
    nickname: string
    email: string
    avatar: string
    status: 'enabled' | 'disabled'
}

/**
 * 用户服务类
 */
class UserService {
    /**
     * 获取用户信息
     * @param id 用户ID
     * @returns 用户信息
     */
    async getUserInfo(id: number): Promise<UserInfo> {
        const response = await createAxios({
            url: `/api/v1/users/${id}`,
            method: 'get',
        })
        
        return response.data
    }
    
    /**
     * 更新用户信息
     * @param id 用户ID
     * @param data 更新数据
     * @returns 是否成功
     */
    async updateUserInfo(id: number, data: Partial<UserInfo>): Promise<boolean> {
        const response = await createAxios({
            url: `/api/v1/users/${id}`,
            method: 'put',
            data,
        })
        
        return response.success
    }
}

export type { UserInfo }
export { UserService }
```

### 8.2 设计模式应用

#### 8.2.1 策略模式 (支付方式)

```php
<?php
namespace app\common\payment;

interface PaymentInterface
{
    public function pay(float $amount): array;
    public function refund(string $orderNo, float $amount): array;
}

class AlipayPayment implements PaymentInterface
{
    public function pay(float $amount): array
    {
        // 支付宝支付逻辑
        return ['status' => 'success'];
    }
    
    public function refund(string $orderNo, float $amount): array
    {
        // 支付宝退款逻辑
        return ['status' => 'success'];
    }
}

class WechatPayment implements PaymentInterface
{
    public function pay(float $amount): array
    {
        // 微信支付逻辑
        return ['status' => 'success'];
    }
    
    public function refund(string $orderNo, float $amount): array
    {
        // 微信退款逻辑
        return ['status' => 'success'];
    }
}

class PaymentService
{
    private PaymentInterface $payment;
    
    public function __construct(string $type)
    {
        $this->payment = match($type) {
            'alipay' => new AlipayPayment(),
            'wechat' => new WechatPayment(),
            default => throw new \Exception('不支持的支付方式')
        };
    }
    
    public function pay(float $amount): array
    {
        return $this->payment->pay($amount);
    }
}
```

#### 8.2.2 观察者模式 (事件系统)

```php
<?php
namespace app\common\event;

class UserRegistered
{
    public function __construct(public int $userId) {}
}

class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        // 发送欢迎邮件
        $user = User::find($event->userId);
        Mail::send($user->email, '欢迎注册');
    }
}

class CreateDefaultData
{
    public function handle(UserRegistered $event): void
    {
        // 创建默认数据
        // ...
    }
}

// 注册事件监听
// event.php
return [
    'bind' => [],
    'listen' => [
        UserRegistered::class => [
            SendWelcomeEmail::class,
            CreateDefaultData::class,
        ],
    ],
];

// 触发事件
event(new UserRegistered($userId));
```

#### 8.2.3 工厂模式 (通知发送)

```php
<?php
namespace app\common\notification;

interface NotificationInterface
{
    public function send(string $to, string $content): bool;
}

class EmailNotification implements NotificationInterface
{
    public function send(string $to, string $content): bool
    {
        // 发送邮件
        return true;
    }
}

class SmsNotification implements NotificationInterface
{
    public function send(string $to, string $content): bool
    {
        // 发送短信
        return true;
    }
}

class NotificationFactory
{
    public static function create(string $type): NotificationInterface
    {
        return match($type) {
            'email' => new EmailNotification(),
            'sms' => new SmsNotification(),
            default => throw new \Exception('不支持的通知类型')
        };
    }
}

// 使用
$notification = NotificationFactory::create('email');
$notification->send('user@example.com', '您有新消息');
```

### 8.3 模块化与插件化

#### 8.3.1 模块化设计

```
modules/
├── Blog/                    // 博客模块
│   ├── Controller/
│   ├── Model/
│   ├── Service/
│   ├── config/
│   └── routes.php
├── Shop/                    // 商城模块
│   ├── Controller/
│   ├── Model/
│   ├── Service/
│   └── routes.php
└── Forum/                   // 论坛模块
    ├── Controller/
    ├── Model/
    └── routes.php
```

**模块加载器**:
```php
class ModuleManager
{
    protected array $modules = [];
    
    /**
     * 加载所有模块
     */
    public function loadModules(): void
    {
        $modulesPath = app()->getBasePath() . 'modules';
        $modules = scandir($modulesPath);
        
        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') continue;
            
            $configFile = "$modulesPath/$module/config.php";
            if (file_exists($configFile)) {
                $this->modules[$module] = require $configFile;
            }
        }
    }
    
    /**
     * 启用模块
     */
    public function enable(string $module): bool
    {
        // 启用逻辑
        return true;
    }
    
    /**
     * 禁用模块
     */
    public function disable(string $module): bool
    {
        // 禁用逻辑
        return true;
    }
}
```

#### 8.3.2 插件系统

```php
interface PluginInterface
{
    public function install(): void;
    public function uninstall(): void;
    public function enable(): void;
    public function disable(): void;
}