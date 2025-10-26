<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 安全头中间件
 * 用于添加各种安全相关的HTTP头，增强应用安全性
 */
class SecurityHeaders
{
    /**
     * 安全头配置
     * @var array
     */
    protected $headers = [];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->initializeHeaders();
    }
    
    /**
     * 初始化安全头配置
     */
    protected function initializeHeaders(): void
    {
        $config = Config::get('security.security_headers', []);
        
        if (empty($config) || !($config['enabled'] ?? true)) {
            return;
        }
        
        // X-Frame-Options 防止点击劫持
        $this->headers['X-Frame-Options'] = $config['x_frame_options'] ?? 'SAMEORIGIN';
        
        // X-Content-Type-Options 防止MIME类型嗅探攻击
        $this->headers['X-Content-Type-Options'] = $config['x_content_type_options'] ?? 'nosniff';
        
        // X-XSS-Protection 启用XSS保护
        $this->headers['X-XSS-Protection'] = $config['x_xss_protection'] ?? '1; mode=block';
        
        // Referrer-Policy 控制referrer信息
        $this->headers['Referrer-Policy'] = $config['referrer_policy'] ?? 'strict-origin-when-cross-origin';
        
        // Content-Security-Policy 内容安全策略
        $this->headers['Content-Security-Policy'] = $this->buildCsp($config);
        
        // Permissions-Policy 控制浏览器功能权限
        $this->headers['Permissions-Policy'] = $this->buildPermissionsPolicy($config);
        
        // Strict-Transport-Security 强制HTTPS（仅在HTTPS连接下设置）
        $this->headers['Strict-Transport-Security'] = $this->buildHsts($config);
    }
    
    /**
     * 构建内容安全策略
     *
     * @param array $config
     * @return string
     */
    protected function buildCsp(array $config): string
    {
        if ($this->isDevelopmentEnvironment() && isset($config['development_policies']['content_security_policy'])) {
            return $config['development_policies']['content_security_policy'];
        }
        
        $cspConfig = $config['content_security_policy'] ?? [];
        
        $directives = [];
        
        if (isset($cspConfig['default'])) {
            $directives[] = $cspConfig['default'];
        }
        
        if (isset($cspConfig['script'])) {
            $directives[] = "script-src {$cspConfig['script']}";
        }
        
        if (isset($cspConfig['style'])) {
            $directives[] = "style-src {$cspConfig['style']}";
        }
        
        if (isset($cspConfig['img'])) {
            $directives[] = "img-src {$cspConfig['img']}";
        }
        
        if (isset($cspConfig['font'])) {
            $directives[] = "font-src {$cspConfig['font']}";
        }
        
        if (isset($cspConfig['connect'])) {
            $directives[] = "connect-src {$cspConfig['connect']}";
        }
        
        if (isset($cspConfig['frame_ancestors'])) {
            $directives[] = "frame-ancestors {$cspConfig['frame_ancestors']}";
        }
        
        return implode('; ', $directives);
    }
    
    /**
     * 构建权限策略
     *
     * @param array $config
     * @return string
     */
    protected function buildPermissionsPolicy(array $config): string
    {
        $policyConfig = $config['permissions_policy'] ?? [];
        $policies = [];
        
        foreach ($policyConfig as $feature => $value) {
            $policies[] = "{$feature}={$value}";
        }
        
        return implode(', ', $policies);
    }
    
    /**
     * 构建HSTS策略
     *
     * @param array $config
     * @return string
     */
    protected function buildHsts(array $config): string
    {
        $hstsConfig = $config['strict_transport_security'] ?? [];
        
        $maxAge = $hstsConfig['max_age'] ?? 31536000;
        $includeSubdomains = $hstsConfig['include_subdomains'] ?? false;
        $preload = $hstsConfig['preload'] ?? false;
        
        $policy = "max-age={$maxAge}";
        
        if ($includeSubdomains) {
            $policy .= '; includeSubDomains';
        }
        
        if ($preload) {
            $policy .= '; preload';
        }
        
        return $policy;
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // 只在HTTPS连接下设置HSTS头
        if (!$request->isSsl()) {
            unset($this->headers['Strict-Transport-Security']);
        }
        
        // 根据环境调整CSP策略
        if ($this->isDevelopmentEnvironment()) {
            // 开发环境允许更多资源
            $this->headers['Content-Security-Policy'] = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' ws: wss:; frame-ancestors 'self';";
        }
        
        // 添加安全头
        foreach ($this->headers as $name => $value) {
            $response->header($name, $value);
        }
        
        // 移除可能泄露服务器信息的头
        $response->header([
            'Server' => '',
            'X-Powered-By' => '',
            'X-Generator' => ''
        ]);
        
        return $response;
    }
    
    /**
     * 判断是否为开发环境
     *
     * @return bool
     */
    protected function isDevelopmentEnvironment(): bool
    {
        return app()->isDebug() || 
               env('APP_ENV') === 'dev' || 
               env('APP_ENV') === 'development' ||
               env('APP_DEBUG') === true;
    }
    
    /**
     * 设置自定义安全头
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
    
    /**
     * 获取当前安全头配置
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}