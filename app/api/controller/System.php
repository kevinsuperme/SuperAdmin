<?php

namespace app\api\controller;

use ba\Random;
use think\facade\Cache;
use think\facade\Db;
use app\common\controller\Api;

class System extends Api
{
    protected array $noNeedLogin = ['healthCheck', 'info'];

    /**
     * 健康检查接口
     * 用于 CI/CD 部署后的健康检查
     */
    public function healthCheck()
    {
        try {
            $health = [
                'status' => 'healthy',
                'timestamp' => time(),
                'checks' => []
            ];

            // 检查数据库连接
            try {
                Db::execute('SELECT 1');
                $health['checks']['database'] = [
                    'status' => 'up',
                    'message' => 'Database connection successful'
                ];
            } catch (\Exception $e) {
                $health['checks']['database'] = [
                    'status' => 'down',
                    'message' => $e->getMessage()
                ];
                $health['status'] = 'unhealthy';
            }

            // 检查 Redis 缓存
            try {
                $testKey = 'health_check_' . Random::uuid();
                $testValue = time();
                
                Cache::set($testKey, $testValue, 10);
                $retrieved = Cache::get($testKey);
                Cache::delete($testKey);
                
                if ($retrieved == $testValue) {
                    $health['checks']['cache'] = [
                        'status' => 'up',
                        'driver' => config('cache.default'),
                        'message' => 'Cache working properly'
                    ];
                } else {
                    $health['checks']['cache'] = [
                        'status' => 'degraded',
                        'driver' => config('cache.default'),
                        'message' => 'Cache not returning correct values'
                    ];
                }
            } catch (\Exception $e) {
                $health['checks']['cache'] = [
                    'status' => 'down',
                    'driver' => config('cache.default'),
                    'message' => $e->getMessage()
                ];
            }

            // 检查文件系统写入权限
            try {
                $testFile = runtime_path() . 'health_check_' . Random::uuid() . '.txt';
                file_put_contents($testFile, 'test');
                if (file_exists($testFile)) {
                    unlink($testFile);
                    $health['checks']['filesystem'] = [
                        'status' => 'up',
                        'message' => 'Filesystem writable'
                    ];
                } else {
                    throw new \Exception('Cannot write to filesystem');
                }
            } catch (\Exception $e) {
                $health['checks']['filesystem'] = [
                    'status' => 'down',
                    'message' => $e->getMessage()
                ];
                $health['status'] = 'unhealthy';
            }

            $this->success('Health check completed', $health);
            
        } catch (\Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());
        }
    }

    /**
     * 系统信息接口
     */
    public function info()
    {
        $info = [
            'app_name' => config('app.app_name'),
            'version' => config('SuperAdmin.version'),
            'php_version' => PHP_VERSION,
            'thinkphp_version' => app()->version(),
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'environment' => app()->isDebug() ? 'development' : 'production',
            'cache_driver' => config('cache.default'),
            'database_type' => config('database.default'),
        ];

        $this->success('System information retrieved', $info);
    }
}