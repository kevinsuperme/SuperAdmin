<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use app\common\middleware\RateLimit;
use think\Request;
use think\Response;
use think\facade\Cache;
use think\facade\Config;
use Mockery;

/**
 * RateLimit中间件单元测试
 * 测试API限流中间件的所有功能
 */
class RateLimitTest extends TestCase
{
    /**
     * @var RateLimit 中间件实例
     */
    private RateLimit $middleware;

    /**
     * @var Request|Mockery\MockInterface Mock的请求对象
     */
    private $requestMock;

    /**
     * 测试前置操作
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new RateLimit();
        $this->requestMock = Mockery::mock(Request::class);

        // 清空缓存
        Cache::clear();

        // 设置默认配置
        Config::set([
            'security.rate_limit.default' => [
                'requests' => 60,
                'duration' => 60,
            ],
            'security.rate_limit.cache.prefix' => 'rate_limit:',
        ], null);
    }

    /**
     * 测试1：正常请求通过限流检查
     *
     * @covers \app\common\middleware\RateLimit::handle
     */
    public function testNormalRequestPassesThrough(): void
    {
        $this->setupBasicRequestMock();

        $executed = false;
        $next = function($request) use (&$executed) {
            $executed = true;
            return new Response('success', 200);
        };

        $response = $this->middleware->handle($this->requestMock, $next);

        $this->assertTrue($executed);
        $this->assertEquals(200, $response->getCode());

        // 验证响应头包含限流信息
        $headers = $response->getHeader();
        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    /**
     * 测试2：超过限流返回429错误
     *
     * @covers \app\common\middleware\RateLimit::handle
     */
    public function testExceedingRateLimitReturns429(): void
    {
        $this->setupBasicRequestMock();

        $identifier = $this->getClientIdentifier();
        $cacheKey = 'rate_limit:' . $identifier;

        // 模拟已经达到限流阈值
        Cache::set($cacheKey, 60, 60); // 已请求60次

        $next = function($request) {
            $this->fail('不应该执行到这里');
        };

        $response = $this->middleware->handle($this->requestMock, $next, 60, 60);

        $this->assertEquals(429, $response->getCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(429, $data['code']);
        $this->assertStringContainsString('频繁', $data['msg']);
        $this->assertArrayHasKey('retry_after', $data['data']);

        // 验证响应头
        $headers = $response->getHeader();
        $this->assertEquals(60, $headers['X-RateLimit-Limit']);
        $this->assertEquals(0, $headers['X-RateLimit-Remaining']);
        $this->assertArrayHasKey('Retry-After', $headers);
    }

    /**
     * 测试3：限流计数器递增
     *
     * @covers \app\common\middleware\RateLimit::handle
     */
    public function testRateLimitCounterIncreases(): void
    {
        $this->setupBasicRequestMock();

        $identifier = $this->getClientIdentifier();
        $cacheKey = 'rate_limit:' . $identifier;

        $next = function($request) {
            return new Response('success', 200);
        };

        // 第一次请求
        $this->middleware->handle($this->requestMock, $next, 10, 60);
        $this->assertEquals(1, Cache::get($cacheKey));

        // 第二次请求
        $this->middleware->handle($this->requestMock, $next, 10, 60);
        $this->assertEquals(2, Cache::get($cacheKey));

        // 第三次请求
        $this->middleware->handle($this->requestMock, $next, 10, 60);
        $this->assertEquals(3, Cache::get($cacheKey));
    }

    /**
     * 测试4：限流窗口过期后重置
     *
     * @covers \app\common\middleware\RateLimit::handle
     */
    public function testRateLimitResetsAfterWindow(): void
    {
        $this->setupBasicRequestMock();

        $identifier = $this->getClientIdentifier();
        $cacheKey = 'rate_limit:' . $identifier;

        $next = function($request) {
            return new Response('success', 200);
        };

        // 设置短过期时间
        $window = 2; // 2秒

        // 第一次请求
        $this->middleware->handle($this->requestMock, $next, 5, $window);
        $this->assertEquals(1, Cache::get($cacheKey));

        // 等待窗口过期
        sleep($window + 1);

        // 过期后应该重置计数
        $this->assertNull(Cache::get($cacheKey));

        // 新请求应该重新开始计数
        $this->middleware->handle($this->requestMock, $next, 5, $window);
        $this->assertEquals(1, Cache::get($cacheKey));
    }

    /**
     * 测试5：获取客户端标识 - 使用IP地址
     *
     * @covers \app\common\middleware\RateLimit::getClientIdentifier
     */
    public function testGetClientIdentifierWithIP(): void
    {
        $this->requestMock->adminAuth = null; // 未登录

        $this->requestMock->shouldReceive('ip')
            ->andReturn('192.168.1.100');
        $this->requestMock->shouldReceive('header')
            ->with('User-Agent', '')
            ->andReturn('Mozilla/5.0 Test Browser');

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('getClientIdentifier');
        $method->setAccessible(true);

        $identifier = $method->invoke($this->middleware, $this->requestMock);

        $this->assertIsString($identifier);
        $this->assertEquals(32, strlen($identifier)); // MD5哈希长度
    }

    /**
     * 测试6：获取客户端标识 - 使用用户ID（已登录）
     *
     * @covers \app\common\middleware\RateLimit::getClientIdentifier
     */
    public function testGetClientIdentifierWithUserId(): void
    {
        $authMock = Mockery::mock();
        $authMock->shouldReceive('isLogin')->andReturn(true);
        $authMock->id = 123;

        $this->requestMock->adminAuth = $authMock;

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('getClientIdentifier');
        $method->setAccessible(true);

        $identifier = $method->invoke($this->middleware, $this->requestMock);

        $this->assertEquals('user:123', $identifier);
    }

    /**
     * 测试7：不同路径使用不同的限流配置
     *
     * @covers \app\common\middleware\RateLimit::getPathConfig
     */
    public function testDifferentPathsHaveDifferentLimits(): void
    {
        // 配置特定路径的限流
        Config::set([
            'security.rate_limit.paths' => [
                'admin/user/*' => [
                    'requests' => 10,
                    'duration' => 60,
                ],
                'admin/login' => [
                    'requests' => 5,
                    'duration' => 60,
                ],
            ],
        ], null);

        // 测试用户管理路径
        $this->requestMock->shouldReceive('pathinfo')
            ->andReturn('admin/user/index');

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('getPathConfig');
        $method->setAccessible(true);

        $config = $method->invoke($this->middleware, $this->requestMock);

        $this->assertEquals(10, $config['requests']);
        $this->assertEquals(60, $config['duration']);
    }

    /**
     * 测试8：路径模式匹配 - 通配符支持
     *
     * @covers \app\common\middleware\RateLimit::pathMatchesPattern
     */
    public function testPathPatternMatching(): void
    {
        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('pathMatchesPattern');
        $method->setAccessible(true);

        // 测试精确匹配
        $this->assertTrue($method->invoke($this->middleware, 'admin/login', 'admin/login'));

        // 测试通配符匹配
        $this->assertTrue($method->invoke($this->middleware, 'admin/user/index', 'admin/user/*'));
        $this->assertTrue($method->invoke($this->middleware, 'admin/user/edit', 'admin/user/*'));

        // 测试不匹配
        $this->assertFalse($method->invoke($this->middleware, 'api/user/index', 'admin/user/*'));
    }

    /**
     * 测试9：自定义限流参数优先级最高
     *
     * @covers \app\common\middleware\RateLimit::getConfig
     */
    public function testCustomParametersHaveHighestPriority(): void
    {
        Config::set([
            'security.rate_limit.default' => [
                'requests' => 60,
                'duration' => 60,
            ],
            'security.rate_limit.paths' => [
                'test/path' => [
                    'requests' => 30,
                    'duration' => 60,
                ],
            ],
        ], null);

        $this->requestMock->shouldReceive('pathinfo')
            ->andReturn('test/path');

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);

        // 自定义参数应该覆盖路径配置
        $config = $method->invoke($this->middleware, 100, 120);

        $this->assertEquals(100, $config['max_requests']);
        $this->assertEquals(120, $config['window']);
    }

    /**
     * 测试10：剩余请求数正确显示
     *
     * @covers \app\common\middleware\RateLimit::handle
     */
    public function testRemainingRequestsDisplayedCorrectly(): void
    {
        $this->setupBasicRequestMock();

        $maxRequests = 10;

        $next = function($request) {
            return new Response('success', 200);
        };

        // 发送5次请求
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->middleware->handle($this->requestMock, $next, $maxRequests, 60);
            $headers = $response->getHeader();

            $this->assertEquals($maxRequests, $headers['X-RateLimit-Limit']);
            $this->assertEquals($maxRequests - $i, $headers['X-RateLimit-Remaining']);
        }

        // 第5次后，应该还剩5次
        $response = $this->middleware->handle($this->requestMock, $next, $maxRequests, 60);
        $headers = $response->getHeader();
        $this->assertEquals(5, $headers['X-RateLimit-Remaining']);
    }

    /**
     * 设置基本的请求Mock
     */
    private function setupBasicRequestMock(): void
    {
        $this->requestMock->adminAuth = null;
        $this->requestMock->shouldReceive('ip')->andReturn('127.0.0.1');
        $this->requestMock->shouldReceive('header')
            ->with('User-Agent', '')
            ->andReturn('Test User Agent');
        $this->requestMock->shouldReceive('pathinfo')->andReturn('test/path');
    }

    /**
     * 获取客户端标识（用于测试）
     */
    private function getClientIdentifier(): string
    {
        return md5('127.0.0.1' . 'Test User Agent');
    }

    /**
     * 清理Mockery
     */
    protected function tearDown(): void
    {
        Mockery::close();
        Cache::clear();
        parent::tearDown();
    }
}
