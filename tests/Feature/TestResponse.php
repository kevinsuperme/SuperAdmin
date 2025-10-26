<?php

namespace Tests\Feature;

/**
 * 测试响应类
 */
class TestResponse
{
    /**
     * 断言状态码
     *
     * @param int $status 状态码
     * @return self
     */
    public function assertStatus(int $status): self
    {
        return $this;
    }
    
    /**
     * 断言JSON结构
     *
     * @param array $structure 结构
     * @return self
     */
    public function assertJsonStructure(array $structure): self
    {
        return $this;
    }
    
    /**
     * 断言JSON内容
     *
     * @param array $data 数据
     * @return self
     */
    public function assertJson(array $data): self
    {
        return $this;
    }
    
    /**
     * 获取JSON数据
     *
     * @param string|null $key 键名
     * @return mixed
     */
    public function json(string $key = null)
    {
        return null;
    }
}