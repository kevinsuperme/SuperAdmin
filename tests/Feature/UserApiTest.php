<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\TestResponse;
use think\facade\Db;
use think\response\Response;

/**
 * 用户API功能测试
 * @mixin \think\testing\TestCase
 */
class UserApiTest extends TestCase
{
    /**
     * 发送GET请求
     *
     * @param string $uri 请求URI
     * @param array $headers 请求头
     * @return TestResponse
     */
    protected function get($uri, array $headers = []): TestResponse
    {
        return parent::get($uri, $headers);
    }
    
    /**
     * 发送POST请求
     *
     * @param string $uri 请求URI
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return TestResponse
     */
    protected function post($uri, array $data = [], array $headers = []): TestResponse
    {
        return parent::post($uri, $data, $headers);
    }
    
    /**
     * 断言不为空
     *
     * @param mixed $value 值
     * @param string $message 消息
     */
    protected function assertNotNull($value, string $message = ''): void
    {
        parent::assertNotNull($value, $message);
    }
    
    /**
     * 断言相等
     *
     * @param mixed $expected 期望值
     * @param mixed $actual 实际值
     * @param string $message 消息
     */
    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        parent::assertEquals($expected, $actual, $message);
    }
    
    /**
     * 创建测试用户
     *
     * @param array $data 用户数据
     * @return int 用户ID
     */
    protected function createTestUser(array $data = []): int
    {
        return parent::createTestUser($data);
    }
    
    /**
     * 测试用户登录接口
     *
     * @return void
     */
    public function testUserLogin(): void
    {
        // 创建测试用户
        $userId = $this->createTestUser([
            'username' => 'api_test_user',
            'password' => password_hash('api123', PASSWORD_DEFAULT),
        ]);
        
        // 测试GET请求 - 获取登录配置
        $response = $this->get('/api/user/checkIn');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'msg',
            'data' => [
                'loginCaptcha',
                'registerCaptcha',
                'loginRestriction',
                'registerRestriction',
                'oauth',
                'agreement',
            ],
        ]);
        
        // 测试POST请求 - 用户登录
        $loginData = [
            'type' => 'username',
            'username' => 'api_test_user',
            'password' => 'api123',
        ];
        
        $response = $this->post('/api/user/checkIn', $loginData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'msg',
            'data' => [
                'token',
                'refreshToken',
                'expiresIn',
                'userInfo' => [
                    'id',
                    'username',
                    'nickname',
                    'avatar',
                    'email',
                    'mobile',
                ],
            ],
        ]);
        
        $response->assertJson([
            'code' => 1,
            'msg' => '登录成功',
        ]);
        
        // 测试错误的用户名或密码
        $loginData['password'] = 'wrong_password';
        
        $response = $this->post('/api/user/checkIn', $loginData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 0,
            'msg' => '用户名或密码错误',
        ]);
    }
    
    /**
     * 测试用户注册接口
     *
     * @return void
     */
    public function testUserRegister(): void
    {
        $registerData = [
            'type' => 'username',
            'username' => 'api_register_user',
            'password' => 'register123',
            'nickname' => 'API注册用户',
            'email' => 'api_register@example.com',
            'mobile' => '13912345678',
        ];
        
        $response = $this->post('/api/user/checkIn', $registerData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'msg',
            'data' => [
                'token',
                'refreshToken',
                'expiresIn',
                'userInfo' => [
                    'id',
                    'username',
                    'nickname',
                    'avatar',
                    'email',
                    'mobile',
                ],
            ],
        ]);
        
        $response->assertJson([
            'code' => 1,
            'msg' => '注册成功',
        ]);
        
        // 验证用户已创建
        $user = Db::name('user')->where('username', 'api_register_user')->find();
        $this->assertNotNull($user);
        $this->assertEquals('api_register_user', $user['username']);
        $this->assertEquals('API注册用户', $user['nickname']);
        
        // 测试重复用户名注册
        $response = $this->post('/api/user/checkIn', $registerData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 0,
            'msg' => '用户名已存在',
        ]);
    }
    
    /**
     * 测试用户登出接口
     *
     * @return void
     */
    public function testUserLogout(): void
    {
        // 创建测试用户
        $userId = $this->createTestUser([
            'username' => 'api_logout_user',
            'password' => password_hash('logout123', PASSWORD_DEFAULT),
        ]);
        
        // 先登录
        $loginData = [
            'type' => 'username',
            'username' => 'api_logout_user',
            'password' => 'logout123',
        ];
        
        $response = $this->post('/api/user/checkIn', $loginData);
        $response->assertStatus(200);
        $response->assertJson(['code' => 1]);
        
        $token = $response->json('data.token');
        $refreshToken = $response->json('data.refreshToken');
        
        // 测试登出
        $logoutData = [
            'refreshToken' => $refreshToken,
        ];
        
        $response = $this->post('/api/user/logout', $logoutData, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 1,
            'msg' => '退出成功',
        ]);
        
        // 测试未登录状态登出
        $response = $this->post('/api/user/logout', $logoutData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 0,
            'msg' => '请先登录',
        ]);
    }
    
    /**
     * 测试获取用户信息接口
     *
     * @return void
     */
    public function testGetUserInfo(): void
    {
        // 创建测试用户
        $userId = $this->createTestUser([
            'username' => 'api_info_user',
            'password' => password_hash('info123', PASSWORD_DEFAULT),
        ]);
        
        // 先登录
        $loginData = [
            'type' => 'username',
            'username' => 'api_info_user',
            'password' => 'info123',
        ];
        
        $response = $this->post('/api/user/checkIn', $loginData);
        $response->assertStatus(200);
        $response->assertJson(['code' => 1]);
        
        $token = $response->json('data.token');
        
        // 测试获取用户信息
        $response = $this->get('/api/user/info', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'msg',
            'data' => [
                'id',
                'username',
                'nickname',
                'avatar',
                'email',
                'mobile',
                'gender',
                'birthday',
                'motto',
                'create_time',
                'update_time',
            ],
        ]);
        
        $response->assertJson([
            'code' => 1,
            'msg' => '获取成功',
            'data' => [
                'username' => 'api_info_user',
                'id' => $userId,
            ],
        ]);
        
        // 测试未登录状态获取用户信息
        $response = $this->get('/api/user/info');
        
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 0,
            'msg' => '请先登录',
        ]);
    }
}