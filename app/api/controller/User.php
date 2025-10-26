<?php

namespace app\api\controller;

use Throwable;
use think\facade\Config;
use app\common\controller\Frontend;
use app\api\validate\User as UserValidate;
use app\common\service\AuthService;

/**
 * 用户API控制器
 * 
 * @OA\Tag(
 *   name="User",
 *   description="用户相关接口"
 * )
 */
class User extends Frontend
{
    /**
     * @var AuthService
     */
    protected AuthService $authService;

    protected array $noNeedLogin = ['checkIn', 'logout'];

    public function initialize(): void
    {
        parent::initialize();
        $this->authService = new AuthService($this->auth);
    }

    /**
     * 会员签入(登录和注册)
     * 
     * @OA\Get(
     *   path="/api/user/checkIn",
     *   tags={"User"},
     *   summary="获取登录配置信息",
     *   description="获取登录和注册相关的配置信息",
     *   @OA\Response(
     *     response=200,
     *     description="成功返回配置信息",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=1),
     *       @OA\Property(property="msg", type="string", example="success"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="openLogin", type="boolean", example=true),
     *         @OA\Property(property="openRegister", type="boolean", example=true),
     *         @OA\Property(property="registerType", type="array", @OA\Items(type="string"), example={"username", "email", "mobile"}),
     *         @OA\Property(property="loginCaptcha", type="boolean", example=false),
     *         @OA\Property(property="registerCaptcha", type="boolean", example=false)
     *       )
     *     )
     *   )
     * )
     * 
     * @OA\Post(
     *   path="/api/user/checkIn",
     *   tags={"User"},
     *   summary="用户登录或注册",
     *   description="处理用户登录和注册请求",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="tab", type="string", description="操作类型", example="login"),
     *       @OA\Property(property="username", type="string", description="用户名", example="admin"),
     *       @OA\Property(property="password", type="string", description="密码", example="123456"),
     *       @OA\Property(property="keep", type="boolean", description="是否保持登录", example=false),
     *       @OA\Property(property="captcha", type="string", description="验证码", example="1234"),
     *       @OA\Property(property="captchaId", type="string", description="验证码ID", example="abc123"),
     *       @OA\Property(property="captchaInfo", type="string", description="验证码信息", example=""),
     *       @OA\Property(property="registerType", type="string", description="注册类型", example="username"),
     *       @OA\Property(property="email", type="string", description="邮箱", example="admin@example.com"),
     *       @OA\Property(property="mobile", type="string", description="手机号", example="13800138000")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="成功返回登录或注册结果",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=1),
     *       @OA\Property(property="msg", type="string", example="登录成功"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *         @OA\Property(property="refreshToken", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *         @OA\Property(property="expiresIn", type="integer", example=7200),
     *         @OA\Property(property="userInfo", type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="username", type="string", example="admin"),
     *           @OA\Property(property="nickname", type="string", example="管理员"),
     *           @OA\Property(property="avatar", type="string", example="/uploads/avatar/1.jpg"),
     *           @OA\Property(property="email", type="string", example="admin@example.com")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="未授权",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=0),
     *       @OA\Property(property="msg", type="string", example="用户名或密码错误")
     *     )
     *   )
     * )
     * 
     * @throws Throwable
     */
    public function checkIn(): void
    {
        // 检查是否开启会员中心
        $openMemberCenter = Config::get('superadmin.open_member_center');
        if (!$openMemberCenter) {
            $this->error(__('Member center disabled'));
        }

        // GET请求返回配置信息
        if ($this->request->isGet()) {
            $config = $this->authService->getLoginConfig();
            $this->success('', $config);
            return;
        }

        // POST请求处理登录/注册
        if ($this->request->isPost()) {
            $params = $this->request->post([
                'tab', 
                'email', 
                'mobile', 
                'username', 
                'password', 
                'keep', 
                'captcha', 
                'captchaId', 
                'captchaInfo', 
                'registerType'
            ]);

            // 验证tab参数
            if (!in_array($params['tab'] ?? '', ['login', 'register'])) {
                $this->error(__('Unknown operation'));
            }

            // 数据验证
            $validate = new UserValidate();
            try {
                $validate->scene($params['tab'])->check($params);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }

            try {
                // 根据tab执行不同操作
                if ($params['tab'] == 'login') {
                    // 登录
                    $result = $this->authService->login(
                        $params['username'],
                        $params['password'],
                        !empty($params['keep']),
                        [
                            'captchaId'   => $params['captchaId'] ?? '',
                            'captchaInfo' => $params['captchaInfo'] ?? '',
                        ]
                    );
                } else {
                    // 注册
                    $result = $this->authService->register($params);
                }

                // 处理结果
                if ($result['success']) {
                    $code = $result['code'] ?? 1;
                    $this->success($result['msg'], $result['data'] ?? [], $code);
                } else {
                    $this->error($result['msg']);
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 用户登出
     * 
     * @OA\Post(
     *   path="/api/user/logout",
     *   tags={"User"},
     *   summary="用户登出",
     *   description="用户退出登录，清除登录状态",
     *   security={{"bearerAuth": {}}},
     *   @OA\RequestBody(
     *     required=false,
     *     @OA\JsonContent(
     *       @OA\Property(property="refreshToken", type="string", description="刷新令牌", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="成功返回登出结果",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=1),
     *       @OA\Property(property="msg", type="string", example="退出成功")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="未授权",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=0),
     *       @OA\Property(property="msg", type="string", example="请先登录")
     *     )
     *   )
     * )
     */
    public function logout(): void
    {
        if ($this->request->isPost()) {
            try {
                $refreshToken = $this->request->post('refreshToken', '');
                $result = $this->authService->logout($refreshToken);
                
                if ($result['success']) {
                    $this->success($result['msg']);
                } else {
                    $this->error($result['msg'] ?? __('Logout failed'));
                }
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 获取当前用户信息
     * 
     * @OA\Get(
     *   path="/api/user/info",
     *   tags={"User"},
     *   summary="获取当前用户信息",
     *   description="获取当前登录用户的详细信息",
     *   security={{"bearerAuth": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="成功返回用户信息",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=1),
     *       @OA\Property(property="msg", type="string", example="success"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="userInfo", type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="username", type="string", example="admin"),
     *           @OA\Property(property="nickname", type="string", example="管理员"),
     *           @OA\Property(property="avatar", type="string", example="/uploads/avatar/1.jpg"),
     *           @OA\Property(property="email", type="string", example="admin@example.com"),
     *           @OA\Property(property="mobile", type="string", example="13800138000"),
     *           @OA\Property(property="status", type="integer", example=1),
     *           @OA\Property(property="createTime", type="string", example="2023-01-01 00:00:00"),
     *           @OA\Property(property="updateTime", type="string", example="2023-01-01 00:00:00")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="未授权",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=0),
     *       @OA\Property(property="msg", type="string", example="请先登录")
     *     )
     *   )
     * )
     */
    public function info(): void
    {
        try {
            if (!$this->authService->isLogin()) {
                $this->error(__('Please login first'), [], 401);
            }

            $userInfo = $this->authService->getUserInfo();
            $this->success('', ['userInfo' => $userInfo]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}