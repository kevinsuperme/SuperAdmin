<?php

namespace Tests\Unit;

use app\common\service\AuthService;
use Tests\TestCase;
use think\facade\Db;

/**
 * 认证服务单元测试
 */
class AuthServiceTest extends TestCase
{
    /**
     * 认证服务实例
     *
     * @var AuthService
     */
    protected $authService;
    
    /**
     * 测试用户ID
     *
     * @var int
     */
    protected $testUserId;
    
    /**
     * 设置测试环境
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建认证服务实例
        $this->authService = new AuthService();
        
        // 创建测试用户
        $this->testUserId = $this->createTestUser([
            'username' => 'auth_test_user',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
        ]);
    }
    
    /**
     * 测试用户登录
     *
     * @return void
     */
    public function testUserLogin(): void
    {
        // 测试正确的用户名和密码
        $result = $this->authService->login('auth_test_user', 'test123', false);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('token', $result['data']);
        $this->assertArrayHasKey('refreshToken', $result['data']);
        $this->assertArrayHasKey('expiresIn', $result['data']);
        $this->assertArrayHasKey('userInfo', $result['data']);
        $this->assertEquals('auth_test_user', $result['data']['userInfo']['username']);
        
        // 测试错误的密码
        $result = $this->authService->login('auth_test_user', 'wrong_password', false);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('用户名或密码错误', $result['msg']);
        
        // 测试不存在的用户名
        $result = $this->authService->login('non_existent_user', 'test123', false);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('用户名或密码错误', $result['msg']);
    }
    
    /**
     * 测试用户注册
     *
     * @return void
     */
    public function testUserRegister(): void
    {
        $userData = [
            'username' => 'new_test_user',
            'password' => 'new_test123',
            'nickname' => '新测试用户',
            'email' => 'new_test@example.com',
            'mobile' => '13912345678',
            'registerType' => 'username',
        ];
        
        $result = $this->authService->register($userData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('token', $result['data']);
        $this->assertArrayHasKey('userInfo', $result['data']);
        $this->assertEquals('new_test_user', $result['data']['userInfo']['username']);
        
        // 验证用户已创建
        $user = Db::name('user')->where('username', 'new_test_user')->find();
        $this->assertNotNull($user);
        $this->assertEquals('new_test_user', $user['username']);
        $this->assertEquals('新测试用户', $user['nickname']);
        
        // 测试重复用户名注册
        $result = $this->authService->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('用户名已存在', $result['msg']);
    }
    
    /**
     * 测试用户登出
     *
     * @return void
     */
    public function testUserLogout(): void
    {
        // 先登录
        $loginResult = $this->authService->login('auth_test_user', 'test123', false);
        $this->assertTrue($loginResult['success']);
        
        // 设置认证信息
        $this->authService->setAuth($loginResult['data']['userInfo'], $loginResult['data']['token']);
        
        // 测试登出
        $result = $this->authService->logout($loginResult['data']['refreshToken']);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('退出成功', $result['msg']);
        
        // 验证用户已登出
        $this->assertFalse($this->authService->isLogin());
    }
    
    /**
     * 测试获取用户信息
     *
     * @return void
     */
    public function testGetUserInfo(): void
    {
        // 先登录
        $loginResult = $this->authService->login('auth_test_user', 'test123', false);
        $this->assertTrue($loginResult['success']);
        
        // 设置认证信息
        $this->authService->setAuth($loginResult['data']['userInfo'], $loginResult['data']['token']);
        
        // 测试获取用户信息
        $userInfo = $this->authService->getUserInfo();
        
        $this->assertIsArray($userInfo);
        $this->assertEquals('auth_test_user', $userInfo['username']);
        $this->assertEquals($this->testUserId, $userInfo['id']);
        
        // 测试未登录状态获取用户信息
        $this->authService->logout('');
        $userInfo = $this->authService->getUserInfo();
        
        $this->assertEmpty($userInfo);
    }
    
    /**
     * 测试检查登录状态
     *
     * @return void
     */
    public function testIsLogin(): void
    {
        // 初始状态未登录
        $this->assertFalse($this->authService->isLogin());
        
        // 登录后
        $loginResult = $this->authService->login('auth_test_user', 'test123', false);
        $this->assertTrue($loginResult['success']);
        
        // 设置认证信息
        $this->authService->setAuth($loginResult['data']['userInfo'], $loginResult['data']['token']);
        
        $this->assertTrue($this->authService->isLogin());
        
        // 登出后
        $this->authService->logout('');
        $this->assertFalse($this->authService->isLogin());
    }
}