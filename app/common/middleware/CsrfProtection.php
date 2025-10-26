<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Cache;
use think\facade\Config;
use think\exception\HttpException;

/**
 * CSRF保护中间件
 * 用于防止跨站请求伪造攻击
 */
class CsrfProtection
{
    /**
     * CSRF令牌名称
     */
    const TOKEN_NAME = '_token';
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->initializeConfig();
    }
    
    /**
     * 初始化配置
     */
    protected function initializeConfig(): void
    {
        $config = Config::get('security.csrf_protection', []);
        
        if (!($config['enabled'] ?? true)) {
            return;
        }
        
        $this->tokenName = $config['token_name'] ?? self::TOKEN_NAME;
        $this->tokenExpire = $config['token_expire'] ?? 7200;
        $this->cachePrefix = $config['cache_prefix'] ?? 'csrf_token:';
        $this->exceptPatterns = $config['except_patterns'] ?? [
            'api/admin/login',
            'api/admin/logout',
            'api/health',
            'api/webhook/*',
        ];
    }
    
    /**
     * CSRF令牌名称
     * @var string
     */
    protected $tokenName = '_token';
    
    /**
     * CSRF令牌缓存前缀
     * @var string
     */
    protected $cachePrefix = 'csrf_token:';
    
    /**
     * CSRF令牌有效期（秒）
     * @var int
     */
    protected $tokenExpire = 7200; // 2小时
    
    /**
     * 需要CSRF保护的HTTP方法
     * @var array
     */
    protected $protectedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
    
    /**
     * 不需要CSRF保护的路径模式
     * @var array
     */
    protected $exceptPatterns = [
        'api/admin/login', // 登录接口
        'api/admin/logout', // 登出接口
        'api/health', // 健康检查接口
        'api/webhook/*', // Webhook接口
    ];
    
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        // 检查是否需要CSRF保护
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }
        
        // 对于GET请求，生成CSRF令牌并添加到响应中
        if ($request->isGet()) {
            $response = $next($request);
            $this->addTokenToResponse($request, $response);
            return $response;
        }
        
        // 对于受保护的HTTP方法，验证CSRF令牌
        if (in_array($request->method(), $this->protectedMethods)) {
            if (!$this->tokensMatch($request)) {
                throw new HttpException(419, 'CSRF token mismatch');
            }
        }
        
        return $next($request);
    }
    
    /**
     * 判断请求是否应该跳过CSRF保护
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldPassThrough(Request $request): bool
    {
        $path = $request->pathinfo();
        
        // 检查是否在排除列表中
        foreach ($this->exceptPatterns as $pattern) {
            if ($this->pathMatchesPattern($path, $pattern)) {
                return true;
            }
        }
        
        // API请求可能使用令牌认证，可以配置跳过CSRF
        if ($request->header('Authorization') && $this->isApiRequest($request)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 检查路径是否匹配模式
     *
     * @param string $path
     * @param string $pattern
     * @return bool
     */
    protected function pathMatchesPattern(string $path, string $pattern): bool
    {
        // 简单的通配符匹配
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);
        return (bool) preg_match("#^{$pattern}$#", $path);
    }
    
    /**
     * 判断是否为API请求
     *
     * @param Request $request
     * @return bool
     */
    protected function isApiRequest(Request $request): bool
    {
        return strpos($request->pathinfo(), 'api/') === 0;
    }
    
    /**
     * 验证CSRF令牌是否匹配
     *
     * @param Request $request
     * @return bool
     */
    protected function tokensMatch(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);
        $sessionToken = $this->getSessionToken($request);
        
        if (!$token || !$sessionToken) {
            return false;
        }
        
        // 使用hash_equals防止时序攻击
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * 从请求中获取CSRF令牌
     *
     * @param Request $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // 优先从请求头获取
        $token = $request->header('X-CSRF-TOKEN');
        
        // 如果请求头中没有，从请求参数获取
        if (!$token) {
            $token = $request->param($this->tokenName);
        }
        
        return $token ?: null;
    }
    
    /**
     * 获取会话中的CSRF令牌
     *
     * @param Request $request
     * @return string|null
     */
    protected function getSessionToken(Request $request): ?string
    {
        $sessionId = $this->getSessionId($request);
        if (!$sessionId) {
            return null;
        }
        
        return Cache::get($this->cachePrefix . $sessionId);
    }
    
    /**
     * 获取会话ID
     *
     * @param Request $request
     * @return string|null
     */
    protected function getSessionId(Request $request): ?string
    {
        // 尝试从ThinkPHP会话获取
        if (session_id()) {
            return session_id();
        }
        
        // 尝试从Authorization令牌获取
        $authorization = $request->header('Authorization');
        if ($authorization && strpos($authorization, 'Bearer ') === 0) {
            return md5(substr($authorization, 7));
        }
        
        return null;
    }
    
    /**
     * 生成新的CSRF令牌
     *
     * @param Request $request
     * @return string
     */
    protected function generateToken(Request $request): string
    {
        $sessionId = $this->getSessionId($request);
        if (!$sessionId) {
            // 如果没有会话ID，生成一个临时的
            $sessionId = uniqid('csrf_', true);
        }
        
        $token = bin2hex(random_bytes(32));
        
        // 存储令牌到缓存
        Cache::set($this->cachePrefix . $sessionId, $token, $this->tokenExpire);
        
        return $token;
    }
    
    /**
     * 将CSRF令牌添加到响应中
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function addTokenToResponse(Request $request, Response $response): void
    {
        $token = $this->getTokenFromRequest($request);
        
        // 如果请求中没有令牌，生成一个新的
        if (!$token) {
            $token = $this->generateToken($request);
        }
        
        // 将令牌添加到响应头
        $response->header('X-CSRF-TOKEN', $token);
        
        // 如果是HTML响应，尝试将令牌添加到meta标签
        $contentType = $response->getHeader('Content-Type');
        if ($contentType && strpos($contentType, 'text/html') !== false) {
            $content = $response->getContent();
            if (strpos($content, '<head>') !== false) {
                $metaTag = '<meta name="csrf-token" content="' . $token . '">';
                $content = str_replace('<head>', '<head>' . $metaTag, $content);
                $response->content($content);
            }
        }
    }
    
    /**
     * 设置需要排除的路径模式
     *
     * @param array $patterns
     * @return self
     */
    public function setExceptPatterns(array $patterns): self
    {
        $this->exceptPatterns = $patterns;
        return $this;
    }
    
    /**
     * 获取当前排除的路径模式
     *
     * @return array
     */
    public function getExceptPatterns(): array
    {
        return $this->exceptPatterns;
    }
}