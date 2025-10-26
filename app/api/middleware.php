<?php
return [
    \app\common\middleware\AllowCrossDomain::class,
    \app\common\middleware\SecurityHeaders::class,
    \app\common\middleware\RateLimit::class . ':60,60', // 60次请求/60秒
    \app\common\middleware\EnhancedAuth::class,
    \think\middleware\LoadLangPack::class,
];
