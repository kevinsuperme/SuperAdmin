<?php

namespace app\common\library;

use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;

/**
 * API文档生成器
 */
class ApiDocGenerator
{
    /**
     * 生成API文档
     *
     * @param array $paths 扫描路径
     * @return OpenApi
     */
    public static function generate(array $paths = []): OpenApi
    {
        if (empty($paths)) {
            $paths = [
                app_path() . 'api/controller',
                app_path() . 'admin/controller',
                app_path() . 'common/model',
                app_path() . 'common/service',
            ];
        }

        $openapi = Generator::scan($paths);

        // 添加基本信息
        $openapi->info->title = 'Fantastic Admin API文档';
        $openapi->info->description = '基于ThinkPHP 6和Vue 3的后台管理系统API接口文档';
        $openapi->info->version = '2.3.3';
        $openapi->info->contact = new \OpenApi\Annotations\Contact([
            'name' => 'Fantastic Admin Team',
            'email' => 'support@fantastic-admin.com'
        ]);

        // 添加服务器信息
        $openapi->servers = [
            new \OpenApi\Annotations\Server([
                'url' => request()->domain() . '/api',
                'description' => 'API服务器'
            ])
        ];

        // 添加安全定义
        $openapi->components->securitySchemes = [
            'bearerAuth' => new \OpenApi\Annotations\SecurityScheme([
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
                'description' => 'JWT认证令牌'
            ])
        ];

        // 添加全局安全要求
        $openapi->security = [['bearerAuth' => []]];

        return $openapi;
    }

    /**
     * 导出JSON格式文档
     *
     * @param array $paths 扫描路径
     * @return string
     */
    public static function exportJson(array $paths = []): string
    {
        $openapi = self::generate($paths);
        return $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * 导出YAML格式文档
     *
     * @param array $paths 扫描路径
     * @return string
     */
    public static function exportYaml(array $paths = []): string
    {
        $openapi = self::generate($paths);
        return $openapi->toYaml();
    }

    /**
     * 保存文档到文件
     *
     * @param string $filename 文件名
     * @param string $format 格式(json|yaml)
     * @param array $paths 扫描路径
     * @return bool
     */
    public static function saveToFile(string $filename, string $format = 'json', array $paths = []): bool
    {
        $dir = public_path() . 'docs' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filepath = $dir . $filename . '.' . $format;

        if ($format === 'json') {
            $content = self::exportJson($paths);
        } else {
            $content = self::exportYaml($paths);
        }

        return file_put_contents($filepath, $content) !== false;
    }

    /**
     * 获取控制器方法列表
     *
     * @param string $controllerPath 控制器路径
     * @return array
     */
    public static function getControllerMethods(string $controllerPath): array
    {
        $methods = [];
        
        try {
            $reflection = new ReflectionClass($controllerPath);
            $classMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            foreach ($classMethods as $method) {
                if ($method->class === $controllerPath && !$method->isStatic()) {
                    $methods[] = $method->getName();
                }
            }
        } catch (ReflectionException $e) {
            // 忽略反射异常
        }
        
        return $methods;
    }
}