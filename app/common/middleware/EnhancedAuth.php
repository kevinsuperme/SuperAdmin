<?php
declare (strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Config;
use think\facade\Cache;
use app\admin\library\Auth;

/**
 * 增强认证中间件
 */
class EnhancedAuth
{
    /**
     * 默认配置
     * @var array
     */
    protected array $defaultConfig = [
        'token_blacklist_prefix' => 'token_blacklist:', // 令牌黑名单前缀
        'session_check'           => true,               // 是否检查会话
        'ip_check'                => true,               // 是否检查IP
        'user_agent_check'        => true,               // 是否检查用户代理
        'concurrent_sessions'     => 1,                  // 允许的并发会话数
    ];

    /**
     * 处理请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取配置
        $config = $this->getConfig();
        
        // 获取令牌
        $token = $this->getTokenFromRequest($request);
        
        // 如果没有令牌，直接继续处理（由其他中间件处理未认证）
        if (!$token) {
            return $next($request);
        }
        
        // 检查令牌是否在黑名单中
        if ($this->isTokenBlacklisted($token, $config)) {
            return response()->json([
                'code' => 401,
                'msg'  => '令牌已失效，请重新登录',
                'data' => []
            ], 401);
        }
        
        // 初始化认证
        $auth = Auth::instance();
        $authResult = $auth->init($token);
        
        // 如果认证失败，直接返回
        if (!$authResult) {
            return $next($request);
        }
        
        // 检查会话
        if ($config['session_check']) {
            $sessionResult = $this->checkSession($auth, $config);
            if ($sessionResult !== true) {
                return $sessionResult;
            }
        }
        
        // 检查IP
        if ($config['ip_check']) {
            $ipResult = $this->checkIP($auth, $request, $config);
            if ($ipResult !== true) {
                return $ipResult;
            }
        }
        
        // 检查用户代理
        if ($config['user_agent_check']) {
            $userAgentResult = $this->checkUserAgent($auth, $request, $config);
            if ($userAgentResult !== true) {
                return $userAgentResult;
            }
        }
        
        // 检查并发会话
        if ($config['concurrent_sessions'] > 0) {
            $concurrentResult = $this->checkConcurrentSessions($auth, $config);
            if ($concurrentResult !== true) {
                return $concurrentResult;
            }
        }
        
        // 继续处理请求
        return $next($request);
    }
    
    /**
     * 从请求中获取令牌
     * @param Request $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // 从Header中获取
        $token = $request->header('Authorization');
        if ($token && strpos($token, 'Bearer ') === 0) {
            return substr($token, 7);
        }
        
        // 从参数中获取
        return $request->param('token', null);
    }
    
    /**
     * 检查令牌是否在黑名单中
     * @param string $token
     * @param array $config
     * @return bool
     */
    protected function isTokenBlacklisted(string $token, array $config): bool
    {
        $blacklistKey = $config['token_blacklist_prefix'] . md5($token);
        return Cache::has($blacklistKey);
    }
    
    /**
     * 检查会话
     * @param Auth $auth
     * @param array $config
     * @return bool|Response
     */
    protected function checkSession(Auth $auth, array $config)
    {
        // 检查会话是否过期
        $lastLoginTime = $auth->last_login_time;
        $maxSessionTime = Config::get('superadmin.max_session_time', 86400 * 7); // 默认7天
        
        if ($lastLoginTime && (time() - $lastLoginTime) > $maxSessionTime) {
            // 将令牌加入黑名单
            $this->blacklistToken($auth->getToken(), $config);
            
            // 强制退出
            $auth->logout();
            
            return response()->json([
                'code' => 401,
                'msg'  => '会话已过期，请重新登录',
                'data' => []
            ], 401);
        }
        
        return true;
    }
    
    /**
     * 检查IP
     * @param Auth $auth
     * @param Request $request
     * @param array $config
     * @return bool|Response
     */
    protected function checkIP(Auth $auth, Request $request, array $config)
    {
        $currentIP = $request->ip();
        $lastLoginIP = $auth->last_login_ip;
        
        // 如果IP发生变化，记录日志但不阻止（可根据需要调整）
        if ($lastLoginIP && $currentIP !== $lastLoginIP) {
            // 记录IP变化日志
            \think\facade\Log::warning('用户IP发生变化', [
                'user_id' => $auth->id,
                'username' => $auth->username,
                'old_ip' => $lastLoginIP,
                'new_ip' => $currentIP,
                'time' => date('Y-m-d H:i:s'),
            ]);
            
            // 如果需要更严格的检查，可以在这里返回错误响应
            // return response()->json(['code' => 401, 'msg' => 'IP地址发生变化，请重新登录'], 401);
        }
        
        return true;
    }
    
    /**
     * 检查用户代理
     * @param Auth $auth
     * @param Request $request
     * @param array $config
     * @return bool|Response
     */
    protected function checkUserAgent(Auth $auth, Request $request, array $config)
    {
        $currentUserAgent = $request->header('User-Agent', '');
        
        // 获取存储的用户代理（如果有的话）
        $cacheKey = 'user_agent:' . $auth->id;
        $storedUserAgent = Cache::get($cacheKey);
        
        // 如果没有存储的用户代理，存储当前用户代理
        if (!$storedUserAgent) {
            Cache::set($cacheKey, $currentUserAgent, 86400 * 30); // 存储30天
            return true;
        }
        
        // 如果用户代理发生变化，记录日志
        if ($currentUserAgent !== $storedUserAgent) {
            // 记录用户代理变化日志
            \think\facade\Log::warning('用户代理发生变化', [
                'user_id' => $auth->id,
                'username' => $auth->username,
                'old_user_agent' => $storedUserAgent,
                'new_user_agent' => $currentUserAgent,
                'time' => date('Y-m-d H:i:s'),
            ]);
            
            // 更新存储的用户代理
            Cache::set($cacheKey, $currentUserAgent, 86400 * 30);
            
            // 如果需要更严格的检查，可以在这里返回错误响应
            // return response()->json(['code' => 401, 'msg' => '浏览器环境发生变化，请重新登录'], 401);
        }
        
        return true;
    }
    
    /**
     * 检查并发会话
     * @param Auth $auth
     * @param array $config
     * @return bool|Response
     */
    protected function checkConcurrentSessions(Auth $auth, array $config)
    {
        // 获取当前用户的活跃令牌列表
        $cacheKey = 'user_tokens:' . $auth->id;
        $tokens = Cache::get($cacheKey, []);
        
        // 如果当前令牌不在列表中，添加它
        $currentToken = $auth->getToken();
        if (!in_array($currentToken, $tokens)) {
            $tokens[] = $currentToken;
        }
        
        // 如果超过限制，移除最早的令牌
        if (count($tokens) > $config['concurrent_sessions']) {
            $tokensToRemove = array_slice($tokens, 0, count($tokens) - $config['concurrent_sessions']);
            $tokens = array_slice($tokens, count($tokens) - $config['concurrent_sessions']);
            
            // 将移除的令牌加入黑名单
            foreach ($tokensToRemove as $token) {
                $this->blacklistToken($token, $config);
            }
        }
        
        // 更新令牌列表
        Cache::set($cacheKey, $tokens, 86400 * 7); // 存储7天
        
        return true;
    }
    
    /**
     * 将令牌加入黑名单
     * @param string $token
     * @param array $config
     */
    protected function blacklistToken(string $token, array $config): void
    {
        $blacklistKey = $config['token_blacklist_prefix'] . md5($token);
        Cache::set($blacklistKey, time(), 86400 * 7); // 黑名单保留7天
    }
    
    /**
     * 获取配置
     * @return array
     */
    protected function getConfig(): array
    {
        return [
            'token_blacklist_prefix' => Config::get('superadmin.enhanced_auth.token_blacklist_prefix', $this->defaultConfig['token_blacklist_prefix']),
            'session_check'           => Config::get('superadmin.enhanced_auth.session_check', $this->defaultConfig['session_check']),
            'ip_check'                => Config::get('superadmin.enhanced_auth.ip_check', $this->defaultConfig['ip_check']),
            'user_agent_check'        => Config::get('superadmin.enhanced_auth.user_agent_check', $this->defaultConfig['user_agent_check']),
            'concurrent_sessions'     => Config::get('superadmin.enhanced_auth.concurrent_sessions', $this->defaultConfig['concurrent_sessions']),
        ];
    }
}