<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use app\common\service\UserService;
use app\common\model\User;
use think\facade\Db;

/**
 * UserService单元测试
 * 测试用户管理服务的所有功能
 */
class UserServiceTest extends TestCase
{
    /**
     * @var UserService 用户服务实例
     */
    private UserService $userService;

    /**
     * 测试前置操作
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    /**
     * 测试1：创建用户 - 成功场景
     *
     * @covers \app\common\service\UserService::create
     */
    public function testCreateUserSuccess(): void
    {
        $userData = [
            'username' => 'newuser_' . time(),
            'password' => 'Password123!',
            'email' => 'newuser_' . time() . '@example.com',
            'mobile' => '13800' . rand(100000, 999999),
            'nickname' => '新用户',
            'status' => 1,
        ];

        $user = $this->userService->create($userData);

        // 断言用户创建成功
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['username'], $user->username);
        $this->assertEquals($userData['email'], $user->email);

        // 验证密码已加密
        $this->assertNotEquals($userData['password'], $user->password);
        $this->assertTrue(password_verify($userData['password'], $user->password));

        // 验证数据库中存在
        $this->assertDatabaseHas('user', [
            'username' => $userData['username'],
            'email' => $userData['email'],
        ]);
    }

    /**
     * 测试2：创建用户 - 用户名已存在
     *
     * @covers \app\common\service\UserService::create
     */
    public function testCreateUserWithDuplicateUsername(): void
    {
        $username = 'duplicate_user_' . time();

        // 先创建一个用户
        $this->createTestUser(['username' => $username]);

        // 尝试创建同名用户，应该抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('用户名已存在');

        $this->userService->create([
            'username' => $username,
            'password' => 'password123',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * 测试3：创建用户 - 邮箱已存在
     *
     * @covers \app\common\service\UserService::create
     */
    public function testCreateUserWithDuplicateEmail(): void
    {
        $email = 'duplicate_' . time() . '@example.com';

        // 先创建一个用户
        $this->createTestUser(['email' => $email]);

        // 尝试创建同邮箱用户，应该抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('邮箱已存在');

        $this->userService->create([
            'username' => 'newuser_' . time(),
            'password' => 'password123',
            'email' => $email,
        ]);
    }

    /**
     * 测试4：更新用户信息
     *
     * @covers \app\common\service\UserService::update
     */
    public function testUpdateUser(): void
    {
        // 创建测试用户
        $userId = $this->createTestUser([
            'username' => 'olduser',
            'nickname' => '旧昵称',
            'email' => 'old@example.com',
        ]);

        // 更新用户信息
        $updateData = [
            'nickname' => '新昵称',
            'email' => 'new@example.com',
        ];

        $result = $this->userService->update($userId, $updateData);

        // 断言更新成功
        $this->assertTrue($result);

        // 验证数据库中的数据已更新
        $user = User::find($userId);
        $this->assertEquals('新昵称', $user->nickname);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('olduser', $user->username); // 用户名未改变
    }

    /**
     * 测试5：删除用户（软删除）
     *
     * @covers \app\common\service\UserService::delete
     */
    public function testSoftDeleteUser(): void
    {
        $userId = $this->createTestUser(['username' => 'user_to_delete']);

        // 执行软删除
        $result = $this->userService->delete($userId);

        $this->assertTrue($result);

        // 验证用户无法通过普通查询找到
        $user = User::find($userId);
        $this->assertNull($user);

        // 验证用户在软删除查询中存在
        $deletedUser = User::onlyTrashed()->find($userId);
        $this->assertNotNull($deletedUser);
    }

    /**
     * 测试6：查询用户列表 - 按状态筛选
     *
     * @covers \app\common\service\UserService::select
     */
    public function testGetUserListByStatus(): void
    {
        // 创建不同状态的用户
        $this->createTestUser(['username' => 'active_user_1', 'status' => 1]);
        $this->createTestUser(['username' => 'active_user_2', 'status' => 1]);
        $this->createTestUser(['username' => 'inactive_user', 'status' => 0]);

        // 查询激活状态的用户
        $activeUsers = $this->userService->select(['status' => 1]);

        $this->assertIsArray($activeUsers);
        $this->assertGreaterThanOrEqual(2, count($activeUsers));

        // 验证所有返回的用户状态都是1
        foreach ($activeUsers as $user) {
            $this->assertEquals(1, $user['status']);
        }
    }

    /**
     * 测试7：分页查询用户
     *
     * @covers \app\common\service\UserService::paginate
     */
    public function testPaginateUsers(): void
    {
        // 创建15个测试用户
        for ($i = 1; $i <= 15; $i++) {
            $this->createTestUser([
                'username' => "paginate_user_{$i}",
                'status' => 1,
            ]);
        }

        // 分页查询（每页10条）
        $result = $this->userService->paginate(['status' => 1], ['id' => 'desc'], 1, 10);

        // 断言返回结构正确
        $this->assertArrayHasKeys(['list', 'total', 'page', 'limit'], $result);
        $this->assertCount(10, $result['list']);
        $this->assertGreaterThanOrEqual(15, $result['total']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(10, $result['limit']);
    }

    /**
     * 测试8：检查用户名是否存在
     *
     * @covers \app\common\service\UserService::usernameExists
     */
    public function testUsernameExists(): void
    {
        $username = 'existing_user_' . time();
        $this->createTestUser(['username' => $username]);

        // 测试存在的用户名
        $exists = $this->userService->usernameExists($username);
        $this->assertTrue($exists);

        // 测试不存在的用户名
        $notExists = $this->userService->usernameExists('nonexistent_user_12345');
        $this->assertFalse($notExists);
    }

    /**
     * 测试9：检查邮箱是否存在
     *
     * @covers \app\common\service\UserService::emailExists
     */
    public function testEmailExists(): void
    {
        $email = 'existing_' . time() . '@example.com';
        $this->createTestUser(['email' => $email]);

        // 测试存在的邮箱
        $exists = $this->userService->emailExists($email);
        $this->assertTrue($exists);

        // 测试不存在的邮箱
        $notExists = $this->userService->emailExists('nonexistent@example.com');
        $this->assertFalse($notExists);
    }

    /**
     * 测试10：更新用户最后登录信息
     *
     * @covers \app\common\service\UserService::updateLastLogin
     */
    public function testUpdateLastLogin(): void
    {
        $userId = $this->createTestUser(['username' => 'login_test_user']);
        $loginIp = '192.168.1.100';
        $loginTime = time();

        // 更新最后登录信息
        $result = $this->userService->updateLastLogin($userId, $loginIp);

        $this->assertTrue($result);

        // 验证数据已更新
        $user = User::find($userId);
        $this->assertEquals($loginIp, $user->last_login_ip);
        $this->assertNotNull($user->last_login_time);
        $this->assertGreaterThanOrEqual($loginTime, strtotime($user->last_login_time));
    }

    /**
     * 测试11：增加登录失败次数
     *
     * @covers \app\common\service\UserService::increaseLoginFailure
     */
    public function testIncreaseLoginFailure(): void
    {
        $userId = $this->createTestUser([
            'username' => 'fail_test_user',
            'login_failure' => 0,
        ]);

        // 第一次失败
        $this->userService->increaseLoginFailure($userId);
        $user = User::find($userId);
        $this->assertEquals(1, $user->login_failure);

        // 第二次失败
        $this->userService->increaseLoginFailure($userId);
        $user = User::find($userId);
        $this->assertEquals(2, $user->login_failure);

        // 超过限制后应该锁定账户
        for ($i = 0; $i < 3; $i++) {
            $this->userService->increaseLoginFailure($userId);
        }
        $user = User::find($userId);
        $this->assertEquals(5, $user->login_failure);
        $this->assertEquals(0, $user->status); // 状态应该变为禁用
    }

    /**
     * 测试12：重置登录失败次数
     *
     * @covers \app\common\service\UserService::resetLoginFailure
     */
    public function testResetLoginFailure(): void
    {
        $userId = $this->createTestUser([
            'username' => 'reset_test_user',
            'login_failure' => 5,
        ]);

        // 重置失败次数
        $this->userService->resetLoginFailure($userId);

        // 验证已重置
        $user = User::find($userId);
        $this->assertEquals(0, $user->login_failure);
    }
}
