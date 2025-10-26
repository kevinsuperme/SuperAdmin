<?php

namespace app\admin\controller\user;

use Throwable;
use app\common\controller\Backend;
use app\admin\model\User as UserModel;
use app\common\service\UserService;

class User extends Backend
{
    /**
     * @var object
     * @phpstan-var UserModel
     */
    protected object $model;

    /**
     * @var UserService
     */
    protected UserService $userService;

    protected array $withJoinTable = ['userGroup'];

    // 排除字段
    protected string|array $preExcludeFields = ['last_login_time', 'login_failure', 'password', 'salt'];

    protected string|array $quickSearchField = ['username', 'nickname', 'id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserModel();
        $this->userService = new UserService();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withoutField('password,salt')
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 添加
     * @throws Throwable
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            // 排除不需要的字段
            $data = $this->excludeFields($data);

            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }

                // 调用服务层创建用户
                $user = $this->userService->createUser($data);

                if ($user) {
                    $this->success(__('Added successfully'));
                } else {
                    $this->error(__('No rows were added'));
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        
        // 获取用户信息
        $row = $this->userService->getUserInfo($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data = $this->excludeFields($data);

            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }

                // 调用服务层更新用户
                $result = $this->userService->updateUser($id, $data);

                if ($result) {
                    $this->success(__('Update successful'));
                } else {
                    $this->error(__('No rows updated'));
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $row->password = '';
        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     * @throws Throwable
     */
    public function del(): void
    {
        if ($this->request->isPost()) {
            $pk  = $this->model->getPk();
            $ids = $this->request->post($pk . '/a', []);

            if (empty($ids)) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            try {
                $result = $this->userService->deleteUser($ids);

                if ($result) {
                    $this->success(__('Deleted successfully'));
                } else {
                    $this->error(__('No rows were deleted'));
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 修改状态
     * @throws Throwable
     */
    public function changeStatus(): void
    {
        if ($this->request->isPost()) {
            $id     = $this->request->post('id');
            $status = $this->request->post('status');

            if (empty($id)) {
                $this->error(__('Parameter %s can not be empty', ['id']));
            }

            try {
                $result = $this->userService->changeUserStatus($id, $status);

                if ($result) {
                    $this->success(__('Update successful'));
                } else {
                    $this->error(__('No rows updated'));
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 重置密码
     * @throws Throwable
     */
    public function resetPassword(): void
    {
        if ($this->request->isPost()) {
            $id          = $this->request->post('id');
            $newPassword = $this->request->post('password');

            if (empty($id) || empty($newPassword)) {
                $this->error(__('Parameter error'));
            }

            try {
                $result = $this->userService->resetUserPassword($id, $newPassword);

                if ($result) {
                    $this->success(__('Password reset successful'));
                } else {
                    $this->error(__('Password reset failed'));
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 重写select
     * @throws Throwable
     */
    public function select(): void
    {
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withoutField('password,salt')
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        foreach ($res as $re) {
            $re->nickname_text = $re->username . '(ID:' . $re->id . ')';
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 获取用户统计信息
     */
    public function statistics(): void
    {
        try {
            $stats = $this->userService->getUserStatistics();
            $this->success('', $stats);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}