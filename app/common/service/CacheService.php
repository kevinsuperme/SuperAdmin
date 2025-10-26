<?php

namespace app\common\service;

use think\facade\Cache;
use think\facade\Log;
use Closure;

/**
 * 缓存管理服务
 * 提供防穿透、防击穿、防雪崩的缓存策略
 */
class CacheService
{
    /**
     * 缓存空值的默认过期时间(秒)
     * 用于防止缓存穿透
     */
    const NULL_CACHE_EXPIRE = 300; // 5分钟

    /**
     * 互斥锁的默认过期时间(秒)
     * 用于防止缓存击穿
     */
    const LOCK_EXPIRE = 10;

    /**
     * 缓存前缀
     */
    const CACHE_PREFIX = 'cache:';

    /**
     * 锁前缀
     */
    const LOCK_PREFIX = 'lock:';

    /**
     * 空值标识
     */
    const NULL_VALUE = '__NULL__';

    /**
     * 获取缓存(带防穿透、防击穿、防雪崩机制)
     * 
     * @param string $key 缓存键
     * @param Closure $callback 回调函数,用于获取数据
     * @param int $expire 过期时间(秒),0表示永久
     * @param bool $preventPenetration 是否防止缓存穿透
     * @param bool $preventBreakdown 是否防止缓存击穿(使用互斥锁)
     * @param int $randomExpire 随机过期时间范围(秒),用于防止缓存雪崩
     * @return mixed
     */
    public function remember(
        string $key,
        Closure $callback,
        int $expire = 3600,
        bool $preventPenetration = true,
        bool $preventBreakdown = true,
        int $randomExpire = 300
    ) {
        $cacheKey = self::CACHE_PREFIX . $key;

        // 尝试从缓存获取
        $value = Cache::get($cacheKey);

        // 如果缓存存在
        if ($value !== null && $value !== false) {
            // 检查是否是空值标识(防穿透)
            if ($preventPenetration && $value === self::NULL_VALUE) {
                return null;
            }
            return $value;
        }

        // 防击穿:使用互斥锁
        if ($preventBreakdown) {
            return $this->getWithLock($key, $callback, $expire, $preventPenetration, $randomExpire);
        }

        // 不使用锁,直接获取数据
        return $this->getData($key, $callback, $expire, $preventPenetration, $randomExpire);
    }

    /**
     * 使用互斥锁获取数据(防止缓存击穿)
     * 
     * @param string $key
     * @param Closure $callback
     * @param int $expire
     * @param bool $preventPenetration
     * @param int $randomExpire
     * @return mixed
     */
    protected function getWithLock(
        string $key,
        Closure $callback,
        int $expire,
        bool $preventPenetration,
        int $randomExpire
    ) {
        $lockKey = self::LOCK_PREFIX . $key;
        $cacheKey = self::CACHE_PREFIX . $key;

        // 尝试获取锁(使用Redis的SET NX EX命令)
        try {
            $redis = Cache::store('redis')->handler();
            $lockAcquired = $redis->set($lockKey, 1, ['nx', 'ex' => self::LOCK_EXPIRE]);
        } catch (\Throwable $e) {
            $lockAcquired = false;
        }

        if ($lockAcquired) {
            try {
                // 获取锁成功,重新检查缓存(双重检查)
                $value = Cache::get($cacheKey);
                if ($value !== null && $value !== false) {
                    if ($preventPenetration && $value === self::NULL_VALUE) {
                        return null;
                    }
                    return $value;
                }

                // 获取数据
                return $this->getData($key, $callback, $expire, $preventPenetration, $randomExpire);
            } finally {
                // 释放锁
                Cache::delete($lockKey);
            }
        }

        // 获取锁失败,等待后重试
        usleep(50000); // 等待50ms
        
        // 重新尝试从缓存获取
        $value = Cache::get($cacheKey);
        if ($value !== null && $value !== false) {
            if ($preventPenetration && $value === self::NULL_VALUE) {
                return null;
            }
            return $value;
        }

        // 如果还是没有,直接获取数据(不加锁,避免等待时间过长)
        return $this->getData($key, $callback, $expire, $preventPenetration, $randomExpire);
    }

    /**
     * 获取数据并缓存
     * 
     * @param string $key
     * @param Closure $callback
     * @param int $expire
     * @param bool $preventPenetration
     * @param int $randomExpire
     * @return mixed
     */
    protected function getData(
        string $key,
        Closure $callback,
        int $expire,
        bool $preventPenetration,
        int $randomExpire
    ) {
        $cacheKey = self::CACHE_PREFIX . $key;

        try {
            // 执行回调获取数据
            $data = $callback();

            // 防穿透:缓存空值
            if ($preventPenetration && ($data === null || $data === false || $data === [])) {
                Cache::set($cacheKey, self::NULL_VALUE, self::NULL_CACHE_EXPIRE);
                return null;
            }

            // 防雪崩:添加随机过期时间
            if ($randomExpire > 0 && $expire > 0) {
                $expire = $expire + mt_rand(0, $randomExpire);
            }

            // 缓存数据
            Cache::set($cacheKey, $data, $expire);

            return $data;
        } catch (\Throwable $e) {
            Log::error('缓存数据获取失败: ' . $e->getMessage(), [
                'key' => $key,
                'trace' => $e->getTraceAsString()
            ]);
            
            // 如果获取数据失败,缓存一个短期的空值,避免频繁查询
            if ($preventPenetration) {
                Cache::set($cacheKey, self::NULL_VALUE, 60);
            }
            
            return null;
        }
    }

    /**
     * 获取缓存
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        $value = Cache::get($cacheKey);

        if ($value === null || $value === false) {
            return $default;
        }

        if ($value === self::NULL_VALUE) {
            return null;
        }

        return $value;
    }

    /**
     * 设置缓存
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return bool
     */
    public function set(string $key, $value, int $expire = 3600): bool
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        // 防止缓存null值导致的问题
        if ($value === null) {
            $value = self::NULL_VALUE;
        }

        return Cache::set($cacheKey, $value, $expire);
    }

    /**
     * 删除缓存
     * 
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        return Cache::delete($cacheKey);
    }

    /**
     * 批量删除缓存(支持通配符)
     * 
     * @param string $pattern
     * @return int 删除的键数量
     */
    public function deletePattern(string $pattern): int
    {
        $pattern = self::CACHE_PREFIX . $pattern;
        
        try {
            // 使用Redis的SCAN命令遍历匹配的键
            $redis = Cache::store('redis')->handler();
            $iterator = null;
            $count = 0;

            do {
                $keys = $redis->scan($iterator, $pattern, 100);
                if ($keys !== false && !empty($keys)) {
                    foreach ($keys as $key) {
                        Cache::delete($key);
                        $count++;
                    }
                }
            } while ($iterator > 0);

            return $count;
        } catch (\Throwable $e) {
            Log::error('批量删除缓存失败: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 清空所有缓存
     * 
     * @return bool
     */
    public function flush(): bool
    {
        try {
            return Cache::clear();
        } catch (\Throwable $e) {
            Log::error('清空缓存失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 检查缓存是否存在
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        return Cache::has($cacheKey);
    }

    /**
     * 增加计数器
     * 
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function inc(string $key, int $step = 1)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        return Cache::inc($cacheKey, $step);
    }

    /**
     * 减少计数器
     * 
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function dec(string $key, int $step = 1)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        return Cache::dec($cacheKey, $step);
    }

    /**
     * 获取多个缓存
     * 
     * @param array $keys
     * @return array
     */
    public function multiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * 设置多个缓存
     * 
     * @param array $values [key => value]
     * @param int $expire
     * @return bool
     */
    public function setMultiple(array $values, int $expire = 3600): bool
    {
        try {
            foreach ($values as $key => $value) {
                $this->set($key, $value, $expire);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('批量设置缓存失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 标签缓存:设置带标签的缓存
     * 
     * @param string|array $tags
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return bool
     */
    public function tagSet($tags, string $key, $value, int $expire = 3600): bool
    {
        try {
            $cacheKey = self::CACHE_PREFIX . $key;
            return Cache::tag($tags)->set($cacheKey, $value, $expire);
        } catch (\Throwable $e) {
            Log::error('设置标签缓存失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 标签缓存:清除指定标签的所有缓存
     * 
     * @param string|array $tags
     * @return bool
     */
    public function tagClear($tags): bool
    {
        try {
            return Cache::tag($tags)->clear();
        } catch (\Throwable $e) {
            Log::error('清除标签缓存失败: ' . $e->getMessage());
            return false;
        }
    }
}