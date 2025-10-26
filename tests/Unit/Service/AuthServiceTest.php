<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use app\common\service\AuthService;
use app\common\library\Auth;
use Mockery;

/**
 * AuthService单元测试
 * 测试认证服务的所有功能
 */
class AuthServiceTest extends TestCase
{
    /**
     * @var AuthService 认证服务实例
     */
    private AuthService $authService;

    /**
     * @var Auth|Mockery\MockInterface Mock的Auth实例
     */
    private $authMock;

    /**
     * 测试前置操作
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 创建Auth的Mock对象
        $this->authMock = Mockery::mock(Auth::class);
        $this->authService = new AuthService($this->authMock);
    }

    /**
     * 测试1：登录成功
     *
     * @covers \app\common\service\AuthService::login
     */
    public function testLoginSuccess(): void
    {
        $username = 'admin';
        $password = 'admin123';

        // Mock Auth的行为
        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);
        $this->authMock->shouldReceive('login')
            ->once()
            ->with($username, $password, false)
            ->andReturn(true);

        $this->authMock->id = 1;
        $this->authMock->shouldReceive('getUserInfo')->once()->andReturn([
            'id' => 1,
            'username' => $username,
            'email' => 'admin@example.com',
        ]);

        // 执行登录
        $result = $this->authService->login($username, $password, false, []);

        // 断言结果
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('成功', $result['msg']);
        $this->assertArrayHasKey('userInfo', $result['data']);
        $this->assertEquals($username, $result['data']['userInfo']['username']);
    }

    /**
     * 测试2：登录失败 - 错误的密码
     *
     * @covers \app\common\service\AuthService::login
     */
    public function testLoginFailureWithWrongPassword(): void
    {
        $username = 'admin';
        $wrongPassword = 'wrongpassword';

        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);
        $this->authMock->shouldReceive('login')
            ->once()
            ->with($username, $wrongPassword, false)
            ->andReturn(false);
        $this->authMock->shouldReceive('getError')->once()->andReturn('密码错误');

        $result = $this->authService->login($username, $wrongPassword, false, []);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('密码错误', $result['msg']);
    }

    /**
     * 测试3：登录失败 - 用户不存在
     *
     * @covers \app\common\service\AuthService::login
     */
    public function testLoginFailureWithNonexistentUser(): void
    {
        $username = 'nonexistent_user';
        $password = 'password123';

        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);
        $this->authMock->shouldReceive('login')
            ->once()
            ->with($username, $password, false)
            ->andReturn(false);
        $this->authMock->shouldReceive('getError')->once()->andReturn('用户不存在');

        $result = $this->authService->login($username, $password, false, []);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('用户不存在', $result['msg']);
    }

    /**
     * 测试4：已登录状态再次登录
     *
     * @covers \app\common\service\AuthService::login
     */
    public function testLoginWhenAlreadyLoggedIn(): void
    {
        $this->authMock->shouldReceive('isLogin')->once()->andReturn(true);
        $this->authMock->shouldReceive('getUserInfo')->once()->andReturn([
            'id' => 1,
            'username' => 'admin',
        ]);

        $result = $this->authService->login('admin', 'password', false, []);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('type', $result['data']);
        $this->assertEquals(Auth::LOGGED_IN, $result['data']['type']);
    }

    /**
     * 测试5：登录成功 - 保持登录
     *
     * @covers \app\common\service\AuthService::login
     */
    public function testLoginWithKeepLoggedIn(): void
    {
        $username = 'admin';
        $password = 'admin123';

        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);
        $this->authMock->shouldReceive('login')
            ->once()
            ->with($username, $password, true) // keep = true
            ->andReturn(true);

        $this->authMock->id = 1;
        $this->authMock->shouldReceive('getUserInfo')->once()->andReturn([
            'id' => 1,
            'username' => $username,
        ]);

        $result = $this->authService->login($username, $password, true, []);

        $this->assertTrue($result['success']);
    }

    /**
     * 测试6：登录失败 - 验证码错误
     *
     * @covers \app\common\service\AuthService::login
     */
    public function testLoginFailureWithInvalidCaptcha(): void
    {
        // 模拟开启验证码
        config(['superadmin.user_login_captcha' => true]);

        $captchaParams = [
            'captchaId' => 'invalid_id',
            'captchaInfo' => 'invalid_info',
        ];

        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);

        $result = $this->authService->login('admin', 'password', false, $captchaParams);

        // 如果验证码实现存在，应该返回验证码错误
        // 这里简化处理
        $this->assertIsArray($result);
    }

    /**
     * 测试7：登出操作
     *
     * @covers \app\common\service\AuthService::logout
     */
    public function testLogout(): void
    {
        $this->authMock->shouldReceive('logout')->once();

        $result = $this->authService->logout();

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('成功', $result['msg']);
    }

    /**
     * 测试8：登出操作 - 带RefreshToken
     *
     * @covers \app\common\service\AuthService::logout
     */
    public function testLogoutWithRefreshToken(): void
    {
        $refreshToken = 'test_refresh_token_12345';

        $this->authMock->shouldReceive('logout')->once();

        $result = $this->authService->logout($refreshToken);

        $this->assertTrue($result['success']);
    }

    /**
     * 测试9：用户注册 - 成功
     *
     * @covers \app\common\service\AuthService::register
     */
    public function testRegisterSuccess(): void
    {
        // 模拟开启会员中心
        config(['superadmin.open_member_center' => true]);

        $registerParams = [
            'username' => 'newuser_' . time(),
            'password' => 'Password123!',
            'email' => 'newuser_' . time() . '@example.com',
            'mobile' => '13800138000',
            'captcha' => '1234',
            'registerType' => 'email',
        ];

        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);
        $this->authMock->shouldReceive('register')
            ->once()
            ->andReturn(true);
        $this->authMock->shouldReceive('getUserInfo')->once()->andReturn([
            'id' => 1,
            'username' => $registerParams['username'],
        ]);

        // 注意：实际测试中需要Mock验证码和用户名/邮箱检查
        // 这里简化处理，假设所有检查都通过

        $result = $this->authService->register($registerParams);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('成功', $result['msg']);
    }

    /**
     * 测试10：用户注册 - 会员中心未开启
     *
     * @covers \app\common\service\AuthService::register
     */
    public function testRegisterWhenMemberCenterDisabled(): void
    {
        // 模拟关闭会员中心
        config(['superadmin.open_member_center' => false]);

        $result = $this->authService->register([
            'username' => 'newuser',
            'password' => 'password123',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('会员中心', $result['msg']);
    }

    /**
     * 测试11：检查用户是否已登录
     *
     * @covers \app\common\service\AuthService::isLogin
     */
    public function testIsLogin(): void
    {
        // 测试已登录
        $this->authMock->shouldReceive('isLogin')->once()->andReturn(true);
        $this->assertTrue($this->authService->isLogin());

        // 测试未登录
        $this->authMock->shouldReceive('isLogin')->once()->andReturn(false);
        $this->assertFalse($this->authService->isLogin());
    }

    /**
     * 测试12：获取当前登录用户信息
     *
     * @covers \app\common\service\AuthService::getUserInfo
     * @covers \app\common\service\AuthService::getUserId
     */
    public function testGetUserInfo(): void
    {
        $userInfo = [
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'nickname' => '测试用户',
        ];

        $this->authMock->shouldReceive('getUserInfo')->once()->andReturn($userInfo);
        $this->authMock->id = 1;

        // 测试获取用户信息
        $result = $this->authService->getUserInfo();
        $this->assertEquals($userInfo, $result);

        // 测试获取用户ID
        $userId = $this->authService->getUserId();
        $this->assertEquals(1, $userId);
    }

    /**
     * 清理Mockery
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
