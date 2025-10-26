<?php
return [
    \app\common\middleware\AllowCrossDomain::class,
    \app\common\middleware\SecurityHeaders::class,
    \app\common\middleware\RateLimit::class . ':120,60', // 120次请求/60秒
    \app\common\middleware\EnhancedAuth::class,
    \app\common\middleware\CsrfProtection::class,
    \app\common\middleware\AdminLog::class,
    \think\middleware\LoadLangPack::class,
];
