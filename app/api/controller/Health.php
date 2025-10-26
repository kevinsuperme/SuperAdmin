<?php

namespace app\api\controller;

use app\BaseController;

/**
 * 健康检查控制器
 */
class Health extends BaseController
{
    /**
     * 健康检查接口
     *
     * @return \think\Response
     */
    public function index()
    {
        // 检查数据库连接
        $dbStatus = $this->checkDatabase();
        
        // 检查缓存服务
        $cacheStatus = $this->checkCache();
        
        // 检查文件系统
        $fsStatus = $this->checkFilesystem();
        
        // 检查系统负载
        $loadStatus = $this->checkSystemLoad();
        
        $status = 'ok';
        $details = [
            'database' => $dbStatus,
            'cache' => $cacheStatus,
            'filesystem' => $fsStatus,
            'system' => $loadStatus,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'version' => config('app.version', 'unknown'),
            'environment' => config('app.app_env', 'production'),
        ];
        
        // 如果任何检查失败，则整体状态为error
        foreach ($details as $key => $value) {
            if (is_array($value) && isset($value['status']) && $value['status'] !== 'ok') {
                $status = 'error';
                break;
            }
        }
        
        $data = [
            'status' => $status,
            'details' => $details,
        ];
        
        $httpCode = $status === 'ok' ? 200 : 503;
        
        return json($data, $httpCode);
    }
    
    /**
     * 检查数据库连接
     *
     * @return array
     */
    private function checkDatabase()
    {
        try {
            $connection = \think\facade\Db::connect();
            $connection->query('SELECT 1');
            
            return [
                'status' => 'ok',
                'message' => '数据库连接正常',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => '数据库连接失败: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 检查缓存服务
     *
     * @return array
     */
    private function checkCache()
    {
        try {
            $cache = \think\facade\Cache::store();
            $testKey = 'health_check_' . time();
            $testValue = 'ok';
            
            // 写入测试数据
            $cache->set($testKey, $testValue, 60);
            
            // 读取测试数据
            $result = $cache->get($testKey);
            
            // 清理测试数据
            $cache->delete($testKey);
            
            if ($result === $testValue) {
                return [
                    'status' => 'ok',
                    'message' => '缓存服务正常',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => '缓存读写测试失败',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => '缓存服务异常: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 检查文件系统
     *
     * @return array
     */
    private function checkFilesystem()
    {
        try {
            $runtimePath = runtime_path();
            $publicPath = public_path();
            
            // 检查运行时目录是否可写
            $runtimeWritable = is_writable($runtimePath);
            
            // 检查公共目录是否可读
            $publicReadable = is_readable($publicPath);
            
            // 检查磁盘空间
            $freeSpace = disk_free_space($runtimePath);
            $totalSpace = disk_total_space($runtimePath);
            $usedPercent = round((1 - $freeSpace / $totalSpace) * 100, 2);
            
            if ($runtimeWritable && $publicReadable && $usedPercent < 90) {
                return [
                    'status' => 'ok',
                    'message' => '文件系统正常',
                    'details' => [
                        'runtime_writable' => $runtimeWritable,
                        'public_readable' => $publicReadable,
                        'disk_usage_percent' => $usedPercent,
                    ],
                ];
            } else {
                $issues = [];
                if (!$runtimeWritable) $issues[] = '运行时目录不可写';
                if (!$publicReadable) $issues[] = '公共目录不可读';
                if ($usedPercent >= 90) $issues[] = '磁盘空间不足（使用率: ' . $usedPercent . '%）';
                
                return [
                    'status' => 'error',
                    'message' => '文件系统异常: ' . implode(', ', $issues),
                    'details' => [
                        'runtime_writable' => $runtimeWritable,
                        'public_readable' => $publicReadable,
                        'disk_usage_percent' => $usedPercent,
                    ],
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => '文件系统检查异常: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 检查系统负载
     *
     * @return array
     */
    private function checkSystemLoad()
    {
        try {
            // 获取系统负载（Linux）
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $load1 = $load[0];
                $load5 = $load[1];
                $load15 = $load[2];
                
                // 获取CPU核心数
                $cpuCores = $this->getCpuCores();
                
                // 如果1分钟负载超过CPU核心数的2倍，则认为负载过高
                if ($load1 > $cpuCores * 2) {
                    return [
                        'status' => 'warning',
                        'message' => '系统负载较高',
                        'details' => [
                            'load_1min' => $load1,
                            'load_5min' => $load5,
                            'load_15min' => $load15,
                            'cpu_cores' => $cpuCores,
                        ],
                    ];
                } else {
                    return [
                        'status' => 'ok',
                        'message' => '系统负载正常',
                        'details' => [
                            'load_1min' => $load1,
                            'load_5min' => $load5,
                            'load_15min' => $load15,
                            'cpu_cores' => $cpuCores,
                        ],
                    ];
                }
            } else {
                // Windows系统，使用其他方式检查
                return [
                    'status' => 'ok',
                    'message' => '系统负载检查（Windows）',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => '系统负载检查异常: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 获取CPU核心数
     *
     * @return int
     */
    private function getCpuCores()
    {
        if (function_exists('shell_exec')) {
            $cpuCount = shell_exec('nproc');
            if (is_numeric($cpuCount)) {
                return (int)$cpuCount;
            }
        }
        
        // 默认返回4核心
        return 4;
    }
}