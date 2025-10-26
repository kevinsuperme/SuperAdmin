<?php

namespace app\common\service;

use think\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 基础服务类
 * 提供通用的业务逻辑处理方法
 */
abstract class BaseService
{
    /**
     * @var Model 模型实例
     */
    protected Model $model;

    /**
     * 获取模型实例
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * 查询单条记录
     * @param int|string $id 主键值
     * @param array $with 关联模型
     * @return Model|null
     */
    public function find(int|string $id, array $with = []): ?Model
    {
        try {
            $query = $this->model;
            if (!empty($with)) {
                $query = $query->with($with);
            }
            return $query->find($id);
        } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
            return null;
        }
    }

    /**
     * 查询列表
     * @param array $where 查询条件
     * @param array $order 排序条件
     * @param int $limit 每页数量
     * @param array $with 关联模型
     * @return array
     */
    public function select(array $where = [], array $order = [], int $limit = 0, array $with = []): array
    {
        try {
            $query = $this->model->where($where);
            
            if (!empty($with)) {
                $query = $query->with($with);
            }
            
            if (!empty($order)) {
                $query = $query->order($order);
            }
            
            if ($limit > 0) {
                $query = $query->limit($limit);
            }
            
            return $query->select()->toArray();
        } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
            return [];
        }
    }

    /**
     * 分页查询
     * @param array $where 查询条件
     * @param array $order 排序条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param array $with 关联模型
     * @return array
     */
    public function paginate(array $where = [], array $order = [], int $page = 1, int $limit = 15, array $with = []): array
    {
        try {
            $query = $this->model->where($where);
            
            if (!empty($with)) {
                $query = $query->with($with);
            }
            
            if (!empty($order)) {
                $query = $query->order($order);
            }
            
            $result = $query->paginate([
                'list_rows' => $limit,
                'page'      => $page,
            ]);
            
            return [
                'list'  => $result->items(),
                'total' => $result->total(),
                'page'  => $result->currentPage(),
                'limit' => $result->listRows(),
            ];
        } catch (DbException $e) {
            return [
                'list'  => [],
                'total' => 0,
                'page'  => 1,
                'limit' => $limit,
            ];
        }
    }

    /**
     * 创建记录
     * @param array $data 数据
     * @return Model|false
     */
    public function create(array $data): Model|false
    {
        try {
            $this->model->startTrans();
            $result = $this->model->save($data);
            $this->model->commit();
            return $result ? $this->model : false;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 更新记录
     * @param int|string $id 主键值
     * @param array $data 数据
     * @return bool
     */
    public function update(int|string $id, array $data): bool
    {
        try {
            $this->model->startTrans();
            $model = $this->model->find($id);
            if (!$model) {
                $this->model->rollback();
                return false;
            }
            $result = $model->save($data);
            $this->model->commit();
            return $result !== false;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 删除记录
     * @param int|string|array $ids 主键值或主键数组
     * @param bool $force 是否强制删除
     * @return bool
     */
    public function delete(int|string|array $ids, bool $force = false): bool
    {
        try {
            $this->model->startTrans();
            if ($force) {
                $result = $this->model->destroy($ids, true);
            } else {
                $result = $this->model->destroy($ids);
            }
            $this->model->commit();
            return $result > 0;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 批量创建
     * @param array $dataList 数据列表
     * @return bool
     */
    public function batchCreate(array $dataList): bool
    {
        try {
            $this->model->startTrans();
            $result = $this->model->saveAll($dataList);
            $this->model->commit();
            return $result !== false;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 统计数量
     * @param array $where 查询条件
     * @return int
     */
    public function count(array $where = []): int
    {
        try {
            return $this->model->where($where)->count();
        } catch (DbException $e) {
            return 0;
        }
    }
}