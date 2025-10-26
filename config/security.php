<?php
// +----------------------------------------------------------------------
// | 安全配置文件
// +----------------------------------------------------------------------

return [
    // API限流配置
    'rate_limit' => [
        // 默认限流规则
        'default' => [
            'requests' => 60,    // 请求次数
            'duration' => 60,    // 时间窗口（秒）
        ],
        
        // 特定路径的限流规则
        'paths' => [
            'api/admin/login' => [
                'requests' => 5,     // 登录接口限制更严格
                'duration' => 300,   // 5分钟内最多5次尝试
            ],
            'api/admin/*' => [
                'requests' => 120,   // 管理员接口限制更宽松
                'duration' => 60,
            ],
            'api/*' => [
                'requests' => 60,    // 普通API接口
                'duration' => 60,
            ],
        ],
        
        // 限流缓存配置
        'cache' => [
            'prefix' => 'rate_limit:',
            'expire' => 3600,       // 缓存过期时间
        ],
        
        // 限流响应配置
        'response' => [
            'message' => '请求过于频繁，请稍后再试',
            'retry_after_header' => true,  // 是否添加Retry-After头
        ],
    ],
    
    // 增强认证配置
    'enhanced_auth' => [
        // 令牌黑名单配置
        'token_blacklist' => [
            'enabled' => true,
            'cache_prefix' => 'token_blacklist:',
            'expire' => 86400,  // 黑名单过期时间（秒）
        ],
        
        // 会话验证配置
        'session_validation' => [
            'enabled' => true,
            'max_idle_time' => 3600,  // 最大空闲时间（秒）
        ],
        
        // IP检查配置
        'ip_check' => [
            'enabled' => true,
            'whitelist' => [],        // IP白名单
            'blacklist' => [],        // IP黑名单
        ],
        
        // 用户代理检查配置
        'user_agent_check' => [
            'enabled' => true,
            'blacklist' => [
                // 恶意用户代理模式
                'bot',
                'crawler',
                'spider',
                'scraper',
            ],
        ],
        
        // 并发会话控制
        'concurrent_sessions' => [
            'enabled' => true,
            'max_sessions' => 3,      // 每个用户最大并发会话数
        ],
    ],
    
    // CSRF保护配置
    'csrf_protection' => [
        'enabled' => true,
        'token_name' => '_token',
        'token_expire' => 7200,     // 令牌过期时间（秒）
        'cache_prefix' => 'csrf_token:',
        
        // 排除的路径模式
        'except_patterns' => [
            'api/admin/login',
            'api/admin/logout',
            'api/health',
            'api/webhook/*',
        ],
        
        // API请求是否需要CSRF保护
        'protect_api' => false,
    ],
    
    // 安全头配置
    'security_headers' => [
        'enabled' => true,
        
        // X-Frame-Options 防止点击劫持
        'x_frame_options' => 'SAMEORIGIN',
        
        // X-Content-Type-Options 防止MIME类型嗅探攻击
        'x_content_type_options' => 'nosniff',
        
        // X-XSS-Protection 启用XSS保护
        'x_xss_protection' => '1; mode=block',
        
        // Referrer-Policy 控制referrer信息
        'referrer_policy' => 'strict-origin-when-cross-origin',
        
        // Content-Security-Policy 内容安全策略
        'content_security_policy' => [
            'default' => "default-src 'self'",
            'script' => "'self' 'unsafe-inline' 'unsafe-eval'",
            'style' => "'self' 'unsafe-inline'",
            'img' => "'self' data: https:",
            'font' => "'self' data:",
            'connect' => "'self'",
            'frame_ancestors' => "'self'",
        ],
        
        // Permissions-Policy 控制浏览器功能权限
        'permissions_policy' => [
            'geolocation' => '()',
            'microphone' => '()',
            'camera' => '()',
            'payment' => '()',
            'usb' => '()',
            'magnetometer' => '()',
            'gyroscope' => '()',
            'accelerometer' => '()',
        ],
        
        // Strict-Transport-Security 强制HTTPS
        'strict_transport_security' => [
            'max_age' => 31536000,
            'include_subdomains' => true,
            'preload' => true,
        ],
        
        // 开发环境下的宽松策略
        'development_policies' => [
            'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' ws: wss:; frame-ancestors 'self';",
        ],
    ],
    
    // 密码策略配置
    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'forbidden_patterns' => [
            '123456',
            'password',
            'admin',
            'qwerty',
        ],
    ],
    
    // 登录保护配置
    'login_protection' => [
        'max_attempts' => 5,          // 最大尝试次数
        'lockout_duration' => 900,    // 锁定时间（秒）
        'reset_attempts_after' => 300, // 重置尝试计数的时间（秒）
    ],
    
    // 审计日志配置
    'audit_log' => [
        'enabled' => true,
        'log_level' => 'info',
        'log_sensitive_data' => false,  // 是否记录敏感数据
        'retention_days' => 90,          // 日志保留天数
        'events' => [
            'login' => true,
            'logout' => true,
            'password_change' => true,
            'permission_change' => true,
            'data_create' => true,
            'data_update' => true,
            'data_delete' => true,
        ],
    ],
];