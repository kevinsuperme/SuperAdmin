<?php

namespace Tests;

use think\facade\Config;
use think\facade\Db;
use think\testing\TestCase;

/**
 * 测试基类
 */
abstract class TestCase extends \think\testing\TestCase
{
    /**
     * 应用初始化
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 设置测试环境配置
        $this->setupTestEnvironment();
    }
    
    /**
     * 设置测试环境
     *
     * @return void
     */
    protected function setupTestEnvironment(): void
    {
        // 设置测试数据库配置
        $databaseConfig = [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'type' => 'mysql',
                    'hostname' => env('DB_HOST', '127.0.0.1'),
                    'database' => env('DB_DATABASE', 'fantastic_admin_test'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'hostport' => env('DB_PORT', '3306'),
                    'charset' => 'utf8mb4',
                    'prefix' => '',
                    'debug' => true,
                ],
            ],
        ];
        
        Config::set($databaseConfig, 'database');
        
        // 设置缓存配置为文件缓存
        $cacheConfig = [
            'default' => 'file',
            'stores' => [
                'file' => [
                    'type' => 'File',
                    'path' => runtime_path() . 'cache' . DIRECTORY_SEPARATOR . 'tests',
                    'prefix' => '',
                    'expire' => 0,
                ],
            ],
        ];
        
        Config::set($cacheConfig, 'cache');
    }
    
    /**
     * 创建测试用户
     *
     * @param array $data 用户数据
     * @return int 用户ID
     */
    protected function createTestUser(array $data = []): int
    {
        $defaultData = [
            'username' => 'test_user_' . time(),
            'nickname' => '测试用户',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'email' => 'test_' . time() . '@example.com',
            'mobile' => '138' . substr(time(), -8),
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        
        $userData = array_merge($defaultData, $data);
        
        return Db::name('user')->insertGetId($userData);
    }
    
    /**
     * 创建测试管理员
     *
     * @param array $data 管理员数据
     * @return int 管理员ID
     */
    protected function createTestAdmin(array $data = []): int
    {
        $defaultData = [
            'username' => 'test_admin_' . time(),
            'nickname' => '测试管理员',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'email' => 'admin_' . time() . '@example.com',
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        
        $adminData = array_merge($defaultData, $data);
        
        return Db::name('admin')->insertGetId($adminData);
    }
    
    /**
     * 清理测试数据
     *
     * @return void
     */
    protected function cleanupTestData(): void
    {
        // 清理测试用户
        Db::name('user')->whereLike('username', 'test_%')->delete();
        
        // 清理测试管理员
        Db::name('admin')->whereLike('username', 'test_%')->delete();
    }
    
    /**
     * 测试结束
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // 清理测试数据
        $this->cleanupTestData();
        
        parent::tearDown();
    }
}