<?php

namespace app\common\service;

use ba\Captcha;
use ba\ClickCaptcha;
use think\facade\Config;
use app\common\facade\Token;
use app\common\library\Auth;

/**
 * 认证服务类
 * 处理用户登录、注册、登出等认证相关的业务逻辑
 */
class AuthService
{
    /**
     * @var Auth 认证实例
     */
    protected Auth $auth;

    /**
     * @var UserService 用户服务
     */
    protected UserService $userService;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->userService = new UserService();
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $keep 是否保持登录
     * @param array $captchaParams 验证码参数
     * @return array
     */
    public function login(string $username, string $password, bool $keep = false, array $captchaParams = []): array
    {
        // 检查是否已登录
        if ($this->auth->isLogin()) {
            return [
                'code'    => Auth::LOGIN_RESPONSE_CODE,
                'success' => true,
                'msg'     => __('You have already logged in. There is no need to log in again~'),
                'data'    => [
                    'type'     => Auth::LOGGED_IN,
                    'userInfo' => $this->auth->getUserInfo(),
                ]
            ];
        }

        // 验证码检查
        if (Config::get('superadmin.user_login_captcha')) {
            if (empty($captchaParams['captchaId']) || empty($captchaParams['captchaInfo'])) {
                return [
                    'success' => false,
                    'msg'     => __('Captcha parameter error'),
                ];
            }

            $captchaObj = new ClickCaptcha();
            if (!$captchaObj->check($captchaParams['captchaId'], $captchaParams['captchaInfo'])) {
                return [
                    'success' => false,
                    'msg'     => __('Captcha error'),
                ];
            }
        }

        // 执行登录
        $result = $this->auth->login($username, $password, $keep);

        if ($result === true) {
            // 更新最后登录信息
            $userId = $this->auth->id;
            $loginIp = request()->ip();
            $this->userService->updateLastLogin($userId, $loginIp);

            return [
                'success' => true,
                'msg'     => __('Login succeeded!'),
                'data'    => [
                    'userInfo'  => $this->auth->getUserInfo(),
                    'routePath' => '/user',
                ]
            ];
        } else {
            // 登录失败,增加失败次数
            $user = $this->userService->getModel()->where('username', $username)->find();
            if ($user) {
                $this->userService->increaseLoginFailure($user->id);
            }

            $msg = $this->auth->getError();
            $msg = $msg ?: __('Login failed, please try again~');
            
            return [
                'success' => false,
                'msg'     => $msg,
            ];
        }
    }

    /**
     * 用户注册
     * @param array $params 注册参数
     * @return array
     */
    public function register(array $params): array
    {
        // 检查是否开启会员中心
        if (!Config::get('superadmin.open_member_center')) {
            return [
                'success' => false,
                'msg'     => __('Member center disabled'),
            ];
        }

        // 检查是否已登录
        if ($this->auth->isLogin()) {
            return [
                'code'    => Auth::LOGIN_RESPONSE_CODE,
                'success' => true,
                'msg'     => __('You have already logged in. There is no need to register again~'),
                'data'    => [
                    'type'     => Auth::LOGGED_IN,
                    'userInfo' => $this->auth->getUserInfo(),
                ]
            ];
        }

        // 验证码检查
        $registerType = $params['registerType'] ?? 'email';
        if (empty($params['captcha']) || empty($params[$registerType])) {
            return [
                'success' => false,
                'msg'     => __('Parameter error'),
            ];
        }

        $captchaObj = new Captcha();
        if (!$captchaObj->check($params['captcha'], $params[$registerType] . 'user_register')) {
            return [
                'success' => false,
                'msg'     => __('Please enter the correct verification code'),
            ];
        }

        // 检查用户名是否已存在
        if ($this->userService->usernameExists($params['username'])) {
            return [
                'success' => false,
                'msg'     => __('Username already exists'),
            ];
        }

        // 检查邮箱是否已存在
        if (!empty($params['email']) && $this->userService->emailExists($params['email'])) {
            return [
                'success' => false,
                'msg'     => __('Email already exists'),
            ];
        }

        // 检查手机号是否已存在
        if (!empty($params['mobile']) && $this->userService->mobileExists($params['mobile'])) {
            return [
                'success' => false,
                'msg'     => __('Mobile already exists'),
            ];
        }

        // 执行注册
        $result = $this->auth->register(
            $params['username'],
            $params['password'],
            $params['mobile'] ?? '',
            $params['email'] ?? ''
        );

        if ($result === true) {
            return [
                'success' => true,
                'msg'     => __('Registration succeeded!'),
                'data'    => [
                    'userInfo'  => $this->auth->getUserInfo(),
                    'routePath' => '/user',
                ]
            ];
        } else {
            $msg = $this->auth->getError();
            $msg = $msg ?: __('Registration failed, please try again~');
            
            return [
                'success' => false,
                'msg'     => $msg,
            ];
        }
    }

    /**
     * 用户登出
     * @param string $refreshToken 刷新令牌
     * @return array
     */
    public function logout(string $refreshToken = ''): array
    {
        if ($refreshToken) {
            Token::delete($refreshToken);
        }
        
        $this->auth->logout();
        
        return [
            'success' => true,
            'msg'     => __('Logout succeeded!'),
        ];
    }

    /**
     * 获取登录配置信息
     * @return array
     */
    public function getLoginConfig(): array
    {
        return [
            'userLoginCaptchaSwitch'  => Config::get('superadmin.user_login_captcha'),
            'accountVerificationType' => get_account_verification_type(),
        ];
    }

    /**
     * 检查用户是否已登录
     * @return bool
     */
    public function isLogin(): bool
    {
        return $this->auth->isLogin();
    }

    /**
     * 获取当前登录用户信息
     * @return array
     */
    public function getUserInfo(): array
    {
        return $this->auth->getUserInfo();
    }

    /**
     * 获取当前登录用户ID
     * @return int
     */
    public function getUserId(): int
    {
        return $this->auth->id;
    }
}