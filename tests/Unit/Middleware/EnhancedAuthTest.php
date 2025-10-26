<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use app\common\middleware\EnhancedAuth;
use think\Request;
use think\Response;
use think\facade\Cache;
use think\facade\Config;
use app\admin\library\Auth;
use Mockery;

/**
 * EnhancedAuth中间件单元测试
 * 测试增强认证中间件的所有安全功能
 */
class EnhancedAuthTest extends TestCase
{
    /**
     * @var EnhancedAuth 中间件实例
     */
    private EnhancedAuth $middleware;

    /**
     * @var Request|Mockery\MockInterface Mock的请求对象
     */
    private $requestMock;

    /**
     * @var Auth|Mockery\MockInterface Mock的Auth对象
     */
    private $authMock;

    /**
     * 测试前置操作
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new EnhancedAuth();
        $this->requestMock = Mockery::mock(Request::class);
        $this->authMock = Mockery::mock(Auth::class);

        // 清空缓存
        Cache::clear();
    }

    /**
     * 测试1：无Token时继续处理
     *
     * @covers \app\common\middleware\EnhancedAuth::handle
     */
    public function testHandleWithoutToken(): void
    {
        $this->requestMock->shouldReceive('header')
            ->with('Authorization')
            ->andReturn(null);
        $this->requestMock->shouldReceive('param')
            ->with('token', null)
            ->andReturn(null);

        $executed = false;
        $next = function($request) use (&$executed) {
            $executed = true;
            return new Response('success');
        };

        $response = $this->middleware->handle($this->requestMock, $next);

        $this->assertTrue($executed);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * 测试2：Token在黑名单中被拒绝
     *
     * @covers \app\common\middleware\EnhancedAuth::handle
     * @covers \app\common\middleware\EnhancedAuth::isTokenBlacklisted
     */
    public function testTokenInBlacklistIsRejected(): void
    {
        $token = 'blacklisted_token_12345';
        $blacklistKey = 'token_blacklist:' . md5($token);

        // 将Token加入黑名单
        Cache::set($blacklistKey, time(), 3600);

        $this->requestMock->shouldReceive('header')
            ->with('Authorization')
            ->andReturn('Bearer ' . $token);

        $next = function($request) {
            $this->fail('不应该执行到这里');
        };

        $response = $this->middleware->handle($this->requestMock, $next);

        $this->assertEquals(401, $response->getCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(401, $data['code']);
        $this->assertStringContainsString('失效', $data['msg']);
    }

    /**
     * 测试3：从Header获取Token
     *
     * @covers \app\common\middleware\EnhancedAuth::getTokenFromRequest
     */
    public function testGetTokenFromHeader(): void
    {
        $token = 'valid_token_from_header';

        $this->requestMock->shouldReceive('header')
            ->with('Authorization')
            ->andReturn('Bearer ' . $token);

        // 使用反射调用protected方法
        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('getTokenFromRequest');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->requestMock);

        $this->assertEquals($token, $result);
    }

    /**
     * 测试4：从参数获取Token
     *
     * @covers \app\common\middleware\EnhancedAuth::getTokenFromRequest
     */
    public function testGetTokenFromParam(): void
    {
        $token = 'valid_token_from_param';

        $this->requestMock->shouldReceive('header')
            ->with('Authorization')
            ->andReturn(null);
        $this->requestMock->shouldReceive('param')
            ->with('token', null)
            ->andReturn($token);

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('getTokenFromRequest');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->requestMock);

        $this->assertEquals($token, $result);
    }

    /**
     * 测试5：会话过期检查
     *
     * @covers \app\common\middleware\EnhancedAuth::checkSession
     */
    public function testSessionExpired(): void
    {
        Config::set([
            'superadmin.max_session_time' => 3600, // 1小时
        ], null);

        $this->authMock->last_login_time = time() - 7200; // 2小时前

        $config = [
            'token_blacklist_prefix' => 'token_blacklist:',
            'session_check' => true,
        ];

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('checkSession');
        $method->setAccessible(true);

        $this->authMock->shouldReceive('getToken')->andReturn('expired_token');
        $this->authMock->shouldReceive('logout')->once();

        $result = $method->invoke($this->middleware, $this->authMock, $config);

        // 应该返回Response对象（拒绝请求）
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getCode());
    }

    /**
     * 测试6：会话未过期通过检查
     *
     * @covers \app\common\middleware\EnhancedAuth::checkSession
     */
    public function testSessionNotExpired(): void
    {
        Config::set([
            'superadmin.max_session_time' => 3600, // 1小时
        ], null);

        $this->authMock->last_login_time = time() - 1800; // 30分钟前

        $config = [
            'session_check' => true,
        ];

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('checkSession');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->authMock, $config);

        // 应该返回true（通过检查）
        $this->assertTrue($result);
    }

    /**
     * 测试7：IP变化检测和日志记录
     *
     * @covers \app\common\middleware\EnhancedAuth::checkIP
     */
    public function testIPChangeDetection(): void
    {
        $this->authMock->id = 1;
        $this->authMock->username = 'testuser';
        $this->authMock->last_login_ip = '192.168.1.100';

        $this->requestMock->shouldReceive('ip')
            ->andReturn('192.168.1.200'); // 不同的IP

        $config = [
            'ip_check' => true,
        ];

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('checkIP');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->authMock, $this->requestMock, $config);

        // 默认情况下，IP变化只记录日志，仍然允许访问
        $this->assertTrue($result);
    }

    /**
     * 测试8：UserAgent变化检测
     *
     * @covers \app\common\middleware\EnhancedAuth::checkUserAgent
     */
    public function testUserAgentChangeDetection(): void
    {
        $this->authMock->id = 1;
        $this->authMock->username = 'testuser';

        $oldUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0';
        $newUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/89.0';

        $cacheKey = 'user_agent:1';
        Cache::set($cacheKey, $oldUserAgent, 86400);

        $this->requestMock->shouldReceive('header')
            ->with('User-Agent', '')
            ->andReturn($newUserAgent);

        $config = [
            'user_agent_check' => true,
        ];

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('checkUserAgent');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->authMock, $this->requestMock, $config);

        // 默认情况下，UserAgent变化只记录日志，仍然允许访问
        $this->assertTrue($result);

        // 验证缓存已更新为新的UserAgent
        $this->assertEquals($newUserAgent, Cache::get($cacheKey));
    }

    /**
     * 测试9：并发会话控制 - 超过限制
     *
     * @covers \app\common\middleware\EnhancedAuth::checkConcurrentSessions
     */
    public function testConcurrentSessionsExceedLimit(): void
    {
        $this->authMock->id = 1;
        $currentToken = 'current_token';
        $this->authMock->shouldReceive('getToken')->andReturn($currentToken);

        $config = [
            'concurrent_sessions' => 2, // 最多2个并发会话
            'token_blacklist_prefix' => 'token_blacklist:',
        ];

        // 模拟已有2个旧token
        $cacheKey = 'user_tokens:1';
        Cache::set($cacheKey, ['old_token_1', 'old_token_2'], 3600);

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('checkConcurrentSessions');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->authMock, $config);

        // 应该通过检查
        $this->assertTrue($result);

        // 验证旧token被移除，只保留最新的2个
        $tokens = Cache::get($cacheKey);
        $this->assertCount(2, $tokens);
        $this->assertContains($currentToken, $tokens);
        $this->assertNotContains('old_token_1', $tokens);

        // 验证最旧的token被加入黑名单
        $blacklistKey = 'token_blacklist:' . md5('old_token_1');
        $this->assertTrue(Cache::has($blacklistKey));
    }

    /**
     * 测试10：并发会话控制 - 未超过限制
     *
     * @covers \app\common\middleware\EnhancedAuth::checkConcurrentSessions
     */
    public function testConcurrentSessionsWithinLimit(): void
    {
        $this->authMock->id = 1;
        $currentToken = 'current_token';
        $this->authMock->shouldReceive('getToken')->andReturn($currentToken);

        $config = [
            'concurrent_sessions' => 3, // 最多3个并发会话
            'token_blacklist_prefix' => 'token_blacklist:',
        ];

        // 模拟已有1个旧token
        $cacheKey = 'user_tokens:1';
        Cache::set($cacheKey, ['old_token_1'], 3600);

        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('checkConcurrentSessions');
        $method->setAccessible(true);

        $result = $method->invoke($this->middleware, $this->authMock, $config);

        $this->assertTrue($result);

        // 验证两个token都保留
        $tokens = Cache::get($cacheKey);
        $this->assertCount(2, $tokens);
        $this->assertContains('old_token_1', $tokens);
        $this->assertContains($currentToken, $tokens);
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
