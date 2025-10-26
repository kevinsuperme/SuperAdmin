<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\ApiDocGenerator;

/**
 * API文档控制器
 */
class ApiDoc extends Backend
{
    /**
     * 无需登录的方法
     * @var array
     */
    protected array $noNeedLogin = ['index', 'json', 'yaml'];

    /**
     * API文档首页
     */
    public function index(): void
    {
        $this->success('', [
            'title' => 'Fantastic Admin API文档',
            'description' => '基于ThinkPHP 6和Vue 3的后台管理系统API接口文档',
            'version' => '2.3.3',
            'endpoints' => [
                'json' => url('admin/api_doc/json'),
                'yaml' => url('admin/api_doc/yaml'),
                'ui' => 'https://petstore.swagger.io/?url=' . urlencode(url('admin/api_doc/json'))
            ]
        ]);
    }

    /**
     * 获取JSON格式API文档
     */
    public function json(): void
    {
        $json = ApiDocGenerator::exportJson();
        
        header('Content-Type: application/json');
        echo $json;
        exit;
    }

    /**
     * 获取YAML格式API文档
     */
    public function yaml(): void
    {
        $yaml = ApiDocGenerator::exportYaml();
        
        header('Content-Type: text/yaml');
        echo $yaml;
        exit;
    }

    /**
     * 生成并保存文档文件
     */
    public function generate(): void
    {
        $jsonResult = ApiDocGenerator::saveToFile('api', 'json');
        $yamlResult = ApiDocGenerator::saveToFile('api', 'yaml');
        
        if ($jsonResult && $yamlResult) {
            $this->success('API文档生成成功', [
                'json_url' => '/docs/api.json',
                'yaml_url' => '/docs/api.yaml'
            ]);
        } else {
            $this->error('API文档生成失败');
        }
    }
}