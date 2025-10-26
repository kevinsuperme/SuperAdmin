<?php

namespace app\common\service;

use app\common\model\User;
use think\facade\Db;

/**
 * 用户服务类
 * 处理用户相关的业务逻辑
 */
class UserService extends BaseService
{
    /**
     * 缓存服务实例
     * @var CacheService
     */
    protected $cache;

    public function __construct()
    {
        $this->model = new User();
        $this->cache = new CacheService();
    }

    /**
     * 获取用户列表(带分页)
     * @param array $params 查询参数
     * @return array
     */
    public function getUserList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';
        $groupId = $params['group_id'] ?? 0;

        $where = [];
        
        // 关键词搜索
        if (!empty($keyword)) {
            $where[] = ['username|nickname|email|mobile', 'like', "%{$keyword}%"];
        }
        
        // 状态筛选
        if (!empty($status)) {
            $where[] = ['status', '=', $status];
        }
        
        // 用户组筛选
        if ($groupId > 0) {
            $where[] = ['group_id', '=', $groupId];
        }

        return $this->paginate($where, ['id' => 'desc'], $page, $limit, ['userGroup']);
    }

    /**
     * 创建用户
     * @param array $data 用户数据
     * @return User|false
     */
    public function createUser(array $data): User|false
    {
        try {
            $this->model->startTrans();

            // 处理密码
            if (isset($data['password'])) {
                $password = $data['password'];
                unset($data['password']);
            }

            // 创建用户
            $user = $this->create($data);
            
            if (!$user) {
                $this->model->rollback();
                return false;
            }

            // 如果有密码,则重置密码
            if (isset($password) && !empty($password)) {
                $this->model->resetPassword($user->id, $password);
            }

            $this->model->commit();
            return $user;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 更新用户信息
     * @param int $userId 用户ID
     * @param array $data 用户数据
     * @return bool
     */
    public function updateUser(int $userId, array $data): bool
    {
        try {
            $this->model->startTrans();

            // 处理密码
            if (isset($data['password']) && !empty($data['password'])) {
                $password = $data['password'];
                unset($data['password']);
                
                // 更新密码
                $this->model->resetPassword($userId, $password);
            }

            // 更新用户信息
            $result = $this->update($userId, $data);
            
            $this->model->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 删除用户
     * @param int|array $userIds 用户ID或ID数组
     * @return bool
     */
    public function deleteUser(int|array $userIds): bool
    {
        $result = $this->delete($userIds);
        
        // 清除缓存
        if ($result) {
            $ids = is_array($userIds) ? $userIds : [$userIds];
            foreach ($ids as $userId) {
                $this->clearUserCache($userId);
            }
        }
        
        return $result;
    }

    /**
     * 修改用户状态
     * @param int $userId 用户ID
     * @param string $status 状态值
     * @return bool
     */
    public function changeUserStatus(int $userId, string $status): bool
    {
        return $this->update($userId, ['status' => $status]);
    }

    /**
     * 批量修改用户状态
     * @param array $userIds 用户ID数组
     * @param string $status 状态值
     * @return bool
     */
    public function batchChangeUserStatus(array $userIds, string $status): bool
    {
        try {
            $this->model->startTrans();
            $result = $this->model->whereIn('id', $userIds)->update(['status' => $status]);
            $this->model->commit();
            return $result > 0;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 重置用户密码
     * @param int $userId 用户ID
     * @param string $newPassword 新密码
     * @return bool
     */
    public function resetUserPassword(int $userId, string $newPassword): bool
    {
        try {
            $result = $this->model->resetPassword($userId, $newPassword);
            if ($result !== false) {
                $this->clearUserCache($userId);
            }
            return $result !== false;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 检查用户名是否存在
     * @param string $username 用户名
     * @param int $excludeId 排除的用户ID
     * @return bool
     */
    public function usernameExists(string $username, int $excludeId = 0): bool
    {
        $where = [['username', '=', $username]];
        if ($excludeId > 0) {
            $where[] = ['id', '<>', $excludeId];
        }
        return $this->count($where) > 0;
    }

    /**
     * 检查邮箱是否存在
     * @param string $email 邮箱
     * @param int $excludeId 排除的用户ID
     * @return bool
     */
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $where = [['email', '=', $email]];
        if ($excludeId > 0) {
            $where[] = ['id', '<>', $excludeId];
        }
        return $this->count($where) > 0;
    }

    /**
     * 检查手机号是否存在
     * @param string $mobile 手机号
     * @param int $excludeId 排除的用户ID
     * @return bool
     */
    public function mobileExists(string $mobile, int $excludeId = 0): bool
    {
        $where = [['mobile', '=', $mobile]];
        if ($excludeId > 0) {
            $where[] = ['id', '<>', $excludeId];
        }
        return $this->count($where) > 0;
    }

    /**
     * 获取用户详情
     * @param int $userId 用户ID
     * @param bool $withPassword 是否包含密码字段
     * @return User|null
     */
    public function getUserInfo(int $userId, bool $withPassword = false): ?User
    {
        $user = $this->find($userId, ['userGroup']);
        
        if ($user && !$withPassword) {
            unset($user->password);
            unset($user->salt);
        }
        
        return $user;
    }

    /**
     * 更新用户余额
     * @param int $userId 用户ID
     * @param float $amount 金额(正数为增加,负数为减少)
     * @param string $remark 备注
     * @return bool
     */
    public function updateUserMoney(int $userId, float $amount, string $remark = ''): bool
    {
        try {
            $this->model->startTrans();

            $user = $this->find($userId);
            if (!$user) {
                $this->model->rollback();
                return false;
            }

            // 计算新余额
            $oldMoney = $user->money;
            $newMoney = bcadd($oldMoney, $amount, 2);

            if ($newMoney < 0) {
                $this->model->rollback();
                throw new \Exception('余额不足');
            }

            // 更新余额
            $user->money = $newMoney;
            $user->save();

            // 记录余额变动日志
            Db::name('user_money_log')->insert([
                'user_id'     => $userId,
                'money'       => $amount,
                'before'      => $oldMoney,
                'after'       => $newMoney,
                'remark'      => $remark,
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            // 清除用户缓存
            $this->clearUserCache($userId);

            $this->model->commit();
            return true;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 更新最后登录信息
     * @param int $userId 用户ID
     * @param string $ip 登录IP
     * @return bool
     */
    public function updateLastLogin(int $userId, string $ip): bool
    {
        $result = $this->update($userId, [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip'   => $ip,
            'login_failure'   => 0,
        ]);
        
        if ($result) {
            $this->clearUserCache($userId);
        }
        
        return $result;
    }

    /**
     * 增加登录失败次数
     * @param int $userId 用户ID
     * @return bool
     */
    public function increaseLoginFailure(int $userId): bool
    {
        try {
            $result = $this->model->where('id', $userId)->inc('login_failure')->update();
            return $result > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 获取用户统计信息
     * @return array
     */
    public function getUserStatistics(): array
    {
        try {
            $total = $this->count();
            $enableCount = $this->count([['status', '=', 'enable']]);
            $disableCount = $this->count([['status', '=', 'disable']]);
            $todayCount = $this->count([
                ['create_time', '>=', date('Y-m-d 00:00:00')],
                ['create_time', '<=', date('Y-m-d 23:59:59')],
            ]);

            return [
                'total'         => $total,
                'enable_count'  => $enableCount,
                'disable_count' => $disableCount,
                'today_count'   => $todayCount,
            ];
        } catch (\Throwable $e) {
            return [
                'total'         => 0,
                'enable_count'  => 0,
                'disable_count' => 0,
                'today_count'   => 0,
            ];
        }
    }
}