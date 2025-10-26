<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use app\common\service\BaseService;
use app\common\model\Admin;
use think\Model;

/**
 * BaseService单元测试
 * 测试基础服务类的CRUD操作
 */
class BaseServiceTest extends TestCase
{
    /**
     * @var TestableBaseService 可测试的Service实例
     */
    private TestableBaseService $service;

    /**
     * 测试前置操作
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestableBaseService();
    }

    /**
     * 测试获取模型实例
     *
     * @covers \app\common\service\BaseService::getModel
     */
    public function testGetModel(): void
    {
        $model = $this->service->getModel();

        $this->assertInstanceOf(Admin::class, $model);
    }

    /**
     * 测试查询单条记录 - 成功场景
     *
     * @covers \app\common\service\BaseService::find
     */
    public function testFindSuccess(): void
    {
        // 创建测试数据
        $adminId = $this->createTestAdmin([
            'username' => 'test_find_user',
            'email' => 'find@example.com',
        ]);

        // 执行查询
        $result = $this->service->find($adminId);

        // 断言
        $this->assertNotNull($result);
        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('test_find_user', $result->username);
        $this->assertEquals('find@example.com', $result->email);
    }

    /**
     * 测试查询单条记录 - 不存在的记录
     *
     * @covers \app\common\service\BaseService::find
     */
    public function testFindNotFound(): void
    {
        $result = $this->service->find(999999);

        $this->assertNull($result);
    }

    /**
     * 测试查询单条记录 - 带关联
     *
     * @covers \app\common\service\BaseService::find
     */
    public function testFindWithRelations(): void
    {
        $adminId = $this->createTestAdmin();

        $result = $this->service->find($adminId, ['group']);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Model::class, $result);
    }

    /**
     * 测试查询列表
     *
     * @covers \app\common\service\BaseService::select
     */
    public function testSelect(): void
    {
        // 创建多个测试数据
        $this->createTestAdmin(['username' => 'test_select_1', 'status' => 1]);
        $this->createTestAdmin(['username' => 'test_select_2', 'status' => 1]);
        $this->createTestAdmin(['username' => 'test_select_3', 'status' => 0]);

        // 查询状态为1的记录
        $result = $this->service->select(['status' => 1]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(2, count($result));
    }

    /**
     * 测试查询列表 - 带排序
     *
     * @covers \app\common\service\BaseService::select
     */
    public function testSelectWithOrder(): void
    {
        $admin1Id = $this->createTestAdmin(['username' => 'test_order_a']);
        sleep(1);
        $admin2Id = $this->createTestAdmin(['username' => 'test_order_b']);

        $result = $this->service->select([], ['id' => 'desc'], 10);

        $this->assertIsArray($result);
        $this->assertTrue(count($result) >= 2);

        // 验证排序
        $ids = array_column($result, 'id');
        $this->assertTrue($ids[0] > $ids[count($ids) - 1], '结果应该按ID降序排列');
    }

    /**
     * 测试查询列表 - 带限制
     *
     * @covers \app\common\service\BaseService::select
     */
    public function testSelectWithLimit(): void
    {
        // 创建5条测试数据
        for ($i = 1; $i <= 5; $i++) {
            $this->createTestAdmin(['username' => "test_limit_{$i}"]);
        }

        $result = $this->service->select([], [], 3);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(3, count($result));
    }

    /**
     * 测试分页查询
     *
     * @covers \app\common\service\BaseService::paginate
     */
    public function testPaginate(): void
    {
        // 创建25条测试数据
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestAdmin(['username' => "test_page_{$i}", 'status' => 1]);
        }

        // 分页查询
        $result = $this->service->paginate(['status' => 1], ['id' => 'desc'], 1, 10);

        // 断言返回结构
        $this->assertIsArray($result);
        $this->assertArrayHasKeys(['list', 'total', 'page', 'limit'], $result);

        // 断言数据
        $this->assertIsArray($result['list']);
        $this->assertCount(10, $result['list']);
        $this->assertGreaterThanOrEqual(25, $result['total']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(10, $result['limit']);
    }

    /**
     * 测试分页查询 - 第二页
     *
     * @covers \app\common\service\BaseService::paginate
     */
    public function testPaginateSecondPage(): void
    {
        // 创建15条数据
        for ($i = 1; $i <= 15; $i++) {
            $this->createTestAdmin(['username' => "test_page2_{$i}"]);
        }

        $result = $this->service->paginate([], [], 2, 10);

        $this->assertEquals(2, $result['page']);
        $this->assertLessThanOrEqual(10, count($result['list']));
    }

    /**
     * 测试创建记录
     *
     * @covers \app\common\service\BaseService::create
     */
    public function testCreate(): void
    {
        $data = [
            'username' => 'test_create_user',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'create@example.com',
            'status' => 1,
        ];

        $result = $this->service->create($data);

        // 断言返回结果
        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('test_create_user', $result->username);
        $this->assertEquals('create@example.com', $result->email);

        // 验证数据库中存在
        $this->assertDatabaseHas('admin', ['username' => 'test_create_user']);
    }

    /**
     * 测试创建记录 - 失败场景
     *
     * @covers \app\common\service\BaseService::create
     */
    public function testCreateFailure(): void
    {
        $this->expectException(\Exception::class);

        // 尝试创建无效数据（缺少必填字段）
        $data = [];

        $this->service->create($data);
    }

    /**
     * 测试更新记录
     *
     * @covers \app\common\service\BaseService::update
     */
    public function testUpdate(): void
    {
        // 创建测试数据
        $adminId = $this->createTestAdmin([
            'username' => 'test_old_name',
            'email' => 'old@example.com',
        ]);

        // 执行更新
        $result = $this->service->update($adminId, [
            'username' => 'test_new_name',
            'email' => 'new@example.com',
        ]);

        // 断言更新成功
        $this->assertTrue($result);

        // 验证数据库中的数据已更新
        $this->assertDatabaseHas('admin', [
            'id' => $adminId,
            'username' => 'test_new_name',
            'email' => 'new@example.com',
        ]);

        // 验证旧数据不存在
        $this->assertDatabaseMissing('admin', [
            'id' => $adminId,
            'username' => 'test_old_name',
        ]);
    }

    /**
     * 测试更新记录 - 不存在的记录
     *
     * @covers \app\common\service\BaseService::update
     */
    public function testUpdateNotFound(): void
    {
        $result = $this->service->update(999999, [
            'username' => 'test_update',
        ]);

        $this->assertFalse($result);
    }

    /**
     * 测试删除记录
     *
     * @covers \app\common\service\BaseService::delete
     */
    public function testDelete(): void
    {
        $adminId = $this->createTestAdmin(['username' => 'test_delete_user']);

        $result = $this->service->delete($adminId);

        $this->assertTrue($result);

        // 验证软删除（如果模型支持）或硬删除
        $deleted = $this->service->find($adminId);
        $this->assertNull($deleted);
    }

    /**
     * 测试批量删除
     *
     * @covers \app\common\service\BaseService::delete
     */
    public function testBatchDelete(): void
    {
        $id1 = $this->createTestAdmin(['username' => 'test_batch_del_1']);
        $id2 = $this->createTestAdmin(['username' => 'test_batch_del_2']);
        $id3 = $this->createTestAdmin(['username' => 'test_batch_del_3']);

        $result = $this->service->delete([$id1, $id2, $id3]);

        $this->assertTrue($result);

        // 验证所有记录都已删除
        $this->assertNull($this->service->find($id1));
        $this->assertNull($this->service->find($id2));
        $this->assertNull($this->service->find($id3));
    }

    /**
     * 测试强制删除
     *
     * @covers \app\common\service\BaseService::delete
     */
    public function testForceDelete(): void
    {
        $adminId = $this->createTestAdmin(['username' => 'test_force_delete']);

        $result = $this->service->delete($adminId, true);

        $this->assertTrue($result);

        // 验证记录完全删除（包括软删除）
        $this->assertNull($this->service->find($adminId));
    }

    /**
     * 测试批量创建
     *
     * @covers \app\common\service\BaseService::batchCreate
     */
    public function testBatchCreate(): void
    {
        $dataList = [
            [
                'username' => 'test_batch_1',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'email' => 'batch1@example.com',
                'status' => 1,
            ],
            [
                'username' => 'test_batch_2',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'email' => 'batch2@example.com',
                'status' => 1,
            ],
            [
                'username' => 'test_batch_3',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'email' => 'batch3@example.com',
                'status' => 1,
            ],
        ];

        $result = $this->service->batchCreate($dataList);

        $this->assertTrue($result);

        // 验证数据库中存在这些记录
        $this->assertDatabaseHas('admin', ['username' => 'test_batch_1']);
        $this->assertDatabaseHas('admin', ['username' => 'test_batch_2']);
        $this->assertDatabaseHas('admin', ['username' => 'test_batch_3']);
    }

    /**
     * 测试统计数量
     *
     * @covers \app\common\service\BaseService::count
     */
    public function testCount(): void
    {
        // 创建测试数据
        $this->createTestAdmin(['username' => 'test_count_1', 'status' => 1]);
        $this->createTestAdmin(['username' => 'test_count_2', 'status' => 1]);
        $this->createTestAdmin(['username' => 'test_count_3', 'status' => 0]);

        // 统计状态为1的数量
        $activeCount = $this->service->count(['status' => 1]);
        $this->assertGreaterThanOrEqual(2, $activeCount);

        // 统计状态为0的数量
        $inactiveCount = $this->service->count(['status' => 0]);
        $this->assertGreaterThanOrEqual(1, $inactiveCount);
    }

    /**
     * 测试统计数量 - 无记录
     *
     * @covers \app\common\service\BaseService::count
     */
    public function testCountEmpty(): void
    {
        $count = $this->service->count(['username' => 'nonexistent_user_12345']);

        $this->assertEquals(0, $count);
    }
}

/**
 * 可测试的BaseService实现
 * 用于测试BaseService的抽象方法
 */
class TestableBaseService extends BaseService
{
    public function __construct()
    {
        $this->model = new Admin();
    }
}
