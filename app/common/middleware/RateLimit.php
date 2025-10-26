<?php
declare (strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Cache;
use think\facade\Config;

/**
 * API限流中间件
 */
class RateLimit
{
    /**
     * 默认限流配置
     * @var array
     */
    protected array $defaultConfig = [
        'max_requests' => 60,     // 最大请求数
        'window'       => 60,     // 时间窗口（秒）
        'key_prefix'   => 'rate_limit:', // 缓存键前缀
    ];

    /**
     * 处理请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param int $maxRequests 最大请求数
     * @param int $window 时间窗口（秒）
     * @return Response
     */
    public function handle(Request $request, Closure $next, int $maxRequests = 0, int $window = 0): Response
    {
        // 获取配置
        $config = $this->getConfig($maxRequests, $window);
        
        // 获取客户端标识
        $identifier = $this->getClientIdentifier($request);
        
        // 生成缓存键
        $cacheKey = $config['key_prefix'] . $identifier;
        
        // 获取当前计数
        $current = Cache::get($cacheKey, 0);
        
        // 检查是否超过限制
        if ($current >= $config['max_requests']) {
            // 计算剩余时间
            $ttl = Cache::ttl($cacheKey);
            
            // 返回限流响应
            return response()->json([
                'code' => 429,
                'msg'  => '请求过于频繁，请稍后再试',
                'data' => [
                    'retry_after' => $ttl ?: $config['window'],
                ]
            ])->header([
                'X-RateLimit-Limit'     => $config['max_requests'],
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset'     => time() + ($ttl ?: $config['window']),
                'Retry-After'           => $ttl ?: $config['window'],
            ]);
        }
        
        // 增加计数
        if ($current == 0) {
            // 首次请求，设置过期时间
            Cache::set($cacheKey, 1, $config['window']);
        } else {
            // 增加计数
            Cache::inc($cacheKey);
        }
        
        // 获取剩余请求数
        $remaining = $config['max_requests'] - Cache::get($cacheKey, 0);
        $ttl = Cache::ttl($cacheKey);
        
        // 继续处理请求
        $response = $next($request);
        
        // 添加响应头
        $response->header([
            'X-RateLimit-Limit'     => $config['max_requests'],
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset'     => time() + ($ttl ?: $config['window']),
        ]);
        
        return $response;
    }
    
    /**
     * 获取配置
     * @param int $maxRequests
     * @param int $window
     * @return array
     */
    protected function getConfig(int $maxRequests, int $window): array
    {
        // 从安全配置文件获取默认值
        $defaultConfig = Config::get('security.rate_limit.default', $this->defaultConfig);
        $pathConfig = $this->getPathConfig(request());
        
        // 合并配置，路径配置优先级最高
        $maxRequests = $maxRequests ?: ($pathConfig['requests'] ?? $defaultConfig['requests']);
        $window = $window ?: ($pathConfig['duration'] ?? $defaultConfig['duration']);
        
        return [
            'max_requests' => $maxRequests,
            'window'       => $window,
            'key_prefix'   => Config::get('security.rate_limit.cache.prefix', $this->defaultConfig['key_prefix']),
        ];
    }
    
    /**
     * 获取特定路径的配置
     * @param Request $request
     * @return array
     */
    protected function getPathConfig(Request $request): array
    {
        $path = $request->pathinfo();
        $pathConfigs = Config::get('security.rate_limit.paths', []);
        
        // 检查路径匹配
        foreach ($pathConfigs as $pattern => $config) {
            if ($this->pathMatchesPattern($path, $pattern)) {
                return $config;
            }
        }
        
        return [];
    }
    
    /**
     * 检查路径是否匹配模式
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
     * 获取客户端标识
     * @param Request $request
     * @return string
     */
    protected function getClientIdentifier(Request $request): string
    {
        // 优先使用用户ID（如果已登录）
        $auth = $request->adminAuth ?? null;
        if ($auth && $auth->isLogin()) {
            return 'user:' . $auth->id;
        }
        
        // 使用IP地址
        $ip = $request->ip();
        
        // 使用用户代理（可选）
        $userAgent = $request->header('User-Agent', '');
        
        // 组合生成唯一标识
        return md5($ip . $userAgent);
    }
}