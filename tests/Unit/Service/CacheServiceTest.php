<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use app\common\service\CacheService;
use think\facade\Cache;

/**
 * CacheService单元测试
 * 测试缓存服务的所有功能
 */
class CacheServiceTest extends TestCase
{
    /**
     * @var CacheService 缓存服务实例
     */
    private CacheService $cacheService;

    /**
     * 测试前置操作
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService();

        // 清空所有缓存
        Cache::clear();
    }

    /**
     * 测试1：设置和获取缓存
     *
     * @covers \app\common\service\CacheService::set
     * @covers \app\common\service\CacheService::get
     */
    public function testSetAndGetCache(): void
    {
        $key = 'test_cache_key';
        $value = 'test_cache_value';

        // 设置缓存
        $setResult = $this->cacheService->set($key, $value);
        $this->assertTrue($setResult);

        // 获取缓存
        $getValue = $this->cacheService->get($key);
        $this->assertEquals($value, $getValue);
    }

    /**
     * 测试2：设置带过期时间的缓存
     *
     * @covers \app\common\service\CacheService::set
     * @covers \app\common\service\CacheService::get
     */
    public function testSetCacheWithExpire(): void
    {
        $key = 'expire_cache_key';
        $value = 'expire_cache_value';
        $expire = 2; // 2秒过期

        // 设置带过期时间的缓存
        $this->cacheService->set($key, $value, $expire);

        // 立即获取应该成功
        $this->assertEquals($value, $this->cacheService->get($key));

        // 等待过期
        sleep($expire + 1);

        // 过期后应该返回null
        $this->assertNull($this->cacheService->get($key));
    }

    /**
     * 测试3：删除缓存
     *
     * @covers \app\common\service\CacheService::delete
     */
    public function testDeleteCache(): void
    {
        $key = 'delete_test_key';
        $value = 'delete_test_value';

        // 先设置缓存
        $this->cacheService->set($key, $value);
        $this->assertEquals($value, $this->cacheService->get($key));

        // 删除缓存
        $deleteResult = $this->cacheService->delete($key);
        $this->assertTrue($deleteResult);

        // 验证已删除
        $this->assertNull($this->cacheService->get($key));
    }

    /**
     * 测试4：清空所有缓存
     *
     * @covers \app\common\service\CacheService::clear
     */
    public function testClearAllCache(): void
    {
        // 设置多个缓存
        $this->cacheService->set('key1', 'value1');
        $this->cacheService->set('key2', 'value2');
        $this->cacheService->set('key3', 'value3');

        // 验证缓存存在
        $this->assertEquals('value1', $this->cacheService->get('key1'));
        $this->assertEquals('value2', $this->cacheService->get('key2'));

        // 清空所有缓存
        $clearResult = $this->cacheService->clear();
        $this->assertTrue($clearResult);

        // 验证所有缓存已清空
        $this->assertNull($this->cacheService->get('key1'));
        $this->assertNull($this->cacheService->get('key2'));
        $this->assertNull($this->cacheService->get('key3'));
    }

    /**
     * 测试5：检查缓存是否存在
     *
     * @covers \app\common\service\CacheService::has
     */
    public function testHasCache(): void
    {
        $key = 'exists_test_key';
        $value = 'exists_test_value';

        // 检查不存在的缓存
        $this->assertFalse($this->cacheService->has($key));

        // 设置缓存
        $this->cacheService->set($key, $value);

        // 检查存在的缓存
        $this->assertTrue($this->cacheService->has($key));
    }

    /**
     * 测试6：Remember模式 - 缓存不存在时执行回调
     *
     * @covers \app\common\service\CacheService::remember
     */
    public function testRememberWhenCacheNotExists(): void
    {
        $key = 'remember_test_key';
        $expectedValue = 'computed_value';
        $callbackExecuted = false;

        // Remember模式
        $result = $this->cacheService->remember($key, function() use ($expectedValue, &$callbackExecuted) {
            $callbackExecuted = true;
            return $expectedValue;
        }, 3600);

        // 验证回调被执行
        $this->assertTrue($callbackExecuted);
        $this->assertEquals($expectedValue, $result);

        // 验证缓存已设置
        $this->assertEquals($expectedValue, $this->cacheService->get($key));
    }

    /**
     * 测试7：Remember模式 - 缓存存在时不执行回调
     *
     * @covers \app\common\service\CacheService::remember
     */
    public function testRememberWhenCacheExists(): void
    {
        $key = 'remember_existing_key';
        $cachedValue = 'cached_value';
        $callbackExecuted = false;

        // 先设置缓存
        $this->cacheService->set($key, $cachedValue);

        // Remember模式
        $result = $this->cacheService->remember($key, function() use (&$callbackExecuted) {
            $callbackExecuted = true;
            return 'new_value';
        }, 3600);

        // 验证回调未被执行
        $this->assertFalse($callbackExecuted);
        // 应该返回缓存的值
        $this->assertEquals($cachedValue, $result);
    }

    /**
     * 测试8：缓存复杂数据类型
     *
     * @covers \app\common\service\CacheService::set
     * @covers \app\common\service\CacheService::get
     */
    public function testCacheComplexDataTypes(): void
    {
        // 测试数组
        $arrayKey = 'array_cache';
        $arrayValue = [
            'name' => '张三',
            'age' => 25,
            'skills' => ['PHP', 'JavaScript', 'Python'],
        ];
        $this->cacheService->set($arrayKey, $arrayValue);
        $this->assertEquals($arrayValue, $this->cacheService->get($arrayKey));

        // 测试对象
        $objectKey = 'object_cache';
        $objectValue = (object) [
            'id' => 1,
            'title' => '测试对象',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->cacheService->set($objectKey, $objectValue);
        $this->assertEquals($objectValue, $this->cacheService->get($objectKey));

        // 测试null值
        $nullKey = 'null_cache';
        $this->cacheService->set($nullKey, null);
        $this->assertNull($this->cacheService->get($nullKey));

        // 测试布尔值
        $boolKey = 'bool_cache';
        $this->cacheService->set($boolKey, true);
        $this->assertTrue($this->cacheService->get($boolKey));

        // 测试数字
        $numberKey = 'number_cache';
        $this->cacheService->set($numberKey, 12345);
        $this->assertEquals(12345, $this->cacheService->get($numberKey));
    }

    /**
     * 测试后置操作
     */
    protected function tearDown(): void
    {
        // 清空测试缓存
        Cache::clear();
        parent::tearDown();
    }
}
