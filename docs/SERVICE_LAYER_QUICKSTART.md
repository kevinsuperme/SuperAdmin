# Service 层快速入门指南

## 5分钟快速上手

### 1. 创建一个新的Service类

```php
<?php
namespace app\common\service;

use app\common\model\Article;

/**
 * 文章服务类
 */
class ArticleService extends BaseService
{
    public function __construct()
    {
        // 指定使用的Model
        $this->model = new Article();
    }
    
    /**
     * 获取文章列表
     */
    public function getArticleList(array $where = [], int $limit = 10): array
    {
        return $this->select($where, ['id' => 'desc'], $limit);
    }
    
    /**
     * 发布文章
     */
    public function publishArticle(array $data): Article|false
    {
        // 设置发布时间
        $data['publish_time'] = time();
        $data['status'] = 1; // 已发布
        
        return $this->create($data);
    }
    
    /**
     * 文章浏览次数+1
     */
    public function incrementViews(int $articleId): bool
    {
        $article = $this->find($articleId);
        if (!$article) {
            throw new \Exception('文章不存在');
        }
        
        $article->views += 1;
        return $article->save();
    }
}
```

### 2. 在Controller中使用Service

```php
<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\service\ArticleService;

class Article extends Backend
{
    /**
     * @var ArticleService
     */
    protected ArticleService $articleService;
    
    public function initialize(): void
    {
        parent::initialize();
        // 初始化Service
        $this->articleService = new ArticleService();
    }
    
    /**
     * 添加文章
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            try {
                $article = $this->articleService->publishArticle($data);
                if ($article) {
                    $this->success('发布成功', ['id' => $article->id]);
                }
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
        }
        
        $this->success('', ['hello' => 'world']);
    }
    
    /**
     * 文章列表
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            // 获取列表数据
            $list = $this->articleService->getArticleList([], 20);
            $this->success('', ['list' => $list]);
        }
        
        $this->success('', ['hello' => 'world']);
    }
}
```

## 常见场景示例

### 场景1: 简单的CRUD操作

```php
// 创建
$article = $this->articleService->create([
    'title' => '标题',
    'content' => '内容'
]);

// 查询单条
$article = $this->articleService->find(1);

// 查询列表
$list = $this->articleService->select(['status' => 1]);

// 更新
$result = $this->articleService->update(1, ['title' => '新标题']);

// 删除
$result = $this->articleService->delete(1);
```

### 场景2: 带验证的创建操作

```php
class ArticleService extends BaseService
{
    public function createArticle(array $data): Article|false
    {
        // 1. 验证标题是否重复
        if ($this->titleExists($data['title'])) {
            throw new \Exception('标题已存在');
        }
        
        // 2. 处理数据
        $data['create_time'] = time();
        $data['author_id'] = $this->getCurrentUserId();
        
        // 3. 创建记录
        return $this->create($data);
    }
    
    /**
     * 检查标题是否存在
     */
    private function titleExists(string $title): bool
    {
        $article = $this->model->where('title', $title)->find();
        return !empty($article);
    }
}
```

### 场景3: 事务操作

```php
class OrderService extends BaseService
{
    protected GoodsService $goodsService;
    protected UserService $userService;
    
    public function __construct()
    {
        $this->model = new Order();
        $this->goodsService = new GoodsService();
        $this->userService = new UserService();
    }
    
    /**
     * 创建订单(涉及多表操作)
     */
    public function createOrder(array $data): Order|false
    {
        try {
            // 开启事务
            $this->model->startTrans();
            
            // 1. 创建订单
            $order = $this->create($data);
            
            // 2. 扣减库存
            $this->goodsService->decreaseStock($data['goods_id'], $data['num']);
            
            // 3. 扣减用户余额
            $this->userService->updateUserMoney($data['user_id'], -$data['amount']);
            
            // 提交事务
            $this->model->commit();
            
            return $order;
        } catch (\Throwable $e) {
            // 回滚事务
            $this->model->rollback();
            throw $e;
        }
    }
}
```

### 场景4: 分页查询

```php
class ArticleService extends BaseService
{
    /**
     * 获取文章分页列表
     */
    public function getArticleList(array $where = [], int $page = 1, int $limit = 15): array
    {
        // 使用paginate方法自动处理分页
        return $this->paginate($where, ['id' => 'desc'], $limit, $page);
    }
}

// Controller中使用
public function index(): void
{
    $page = $this->request->param('page', 1);
    $limit = $this->request->param('limit', 15);
    
    $result = $this->articleService->getArticleList([], $page, $limit);
    
    $this->success('', [
        'list' => $result['list'],
        'total' => $result['total'],
        'page' => $result['page'],
        'limit' => $result['limit'],
    ]);
}
```

### 场景5: 关联查询

```php
class ArticleService extends BaseService
{
    /**
     * 获取文章详情(包含作者信息)
     */
    public function getArticleDetail(int $id): ?Article
    {
        return $this->model
            ->with(['user']) // 关联用户表
            ->where('id', $id)
            ->find();
    }
    
    /**
     * 获取文章列表(包含分类信息)
     */
    public function getArticleListWithCategory(): array
    {
        return $this->model
            ->with(['category']) // 关联分类表
            ->order('id', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
    }
}
```

### 场景6: 统计查询

```php
class ArticleService extends BaseService
{
    /**
     * 获取文章统计信息
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->count([]), // 总数
            'published' => $this->count(['status' => 1]), // 已发布
            'draft' => $this->count(['status' => 0]), // 草稿
            'today' => $this->getTodayCount(), // 今日发布
        ];
    }
    
    /**
     * 获取今日发布数量
     */
    private function getTodayCount(): int
    {
        $todayStart = strtotime(date('Y-m-d'));
        return $this->count([
            ['create_time', '>=', $todayStart]
        ]);
    }
}
```

### 场景7: 批量操作

```php
class ArticleService extends BaseService
{
    /**
     * 批量发布文章
     */
    public function batchPublish(array $ids): bool
    {
        try {
            $this->model->startTrans();
            
            foreach ($ids as $id) {
                $this->update($id, [
                    'status' => 1,
                    'publish_time' => time()
                ]);
            }
            
            $this->model->commit();
            return true;
        } catch (\Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }
    
    /**
     * 批量删除文章
     */
    public function batchDelete(array $ids): bool
    {
        return $this->model->where('id', 'in', $ids)->delete();
    }
}
```

### 场景8: 缓存使用

```php
use think\facade\Cache;

class ArticleService extends BaseService
{
    /**
     * 获取热门文章(带缓存)
     */
    public function getHotArticles(int $limit = 10): array
    {
        $cacheKey = "hot_articles:{$limit}";
        
        // 尝试从缓存获取
        $articles = Cache::get($cacheKey);
        if ($articles) {
            return $articles;
        }
        
        // 查询数据库
        $articles = $this->model
            ->where('status', 1)
            ->order('views', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
        
        // 缓存1小时
        Cache::set($cacheKey, $articles, 3600);
        
        return $articles;
    }
    
    /**
     * 更新文章时清除缓存
     */
    public function updateArticle(int $id, array $data): bool
    {
        $result = $this->update($id, $data);
        
        if ($result) {
            // 清除相关缓存
            Cache::delete("hot_articles:10");
        }
        
        return $result;
    }
}
```

## 错误处理最佳实践

### 1. Service层抛出异常
```php
public function createArticle(array $data): Article|false
{
    if (empty($data['title'])) {
        throw new \Exception('标题不能为空');
    }
    
    if ($this->titleExists($data['title'])) {
        throw new \Exception('标题已存在');
    }
    
    return $this->create($data);
}
```

### 2. Controller层捕获异常
```php
public function add(): void
{
    $data = $this->request->post();
    
    try {
        $article = $this->articleService->createArticle($data);
        if ($article) {
            $this->success('添加成功');
        }
    } catch (\Throwable $e) {
        // 统一的错误处理
        $this->error($e->getMessage());
    }
}
```

## 日志记录建议

```php
use think\facade\Log;

class ArticleService extends BaseService
{
    /**
     * 删除文章(带日志)
     */
    public function deleteArticle(int $id): bool
    {
        try {
            $article = $this->find($id);
            if (!$article) {
                throw new \Exception('文章不存在');
            }
            
            // 记录操作日志
            Log::info("删除文章", [
                'article_id' => $id,
                'title' => $article->title,
                'operator' => $this->getCurrentUserId(),
                'time' => date('Y-m-d H:i:s')
            ]);
            
            return $this->delete($id);
        } catch (\Throwable $e) {
            Log::error("删除文章失败: " . $e->getMessage());
            throw $e;
        }
    }
}
```

## 性能优化技巧

### 1. 避免N+1查询
```php
// ❌ 不好的做法
$articles = $this->select([]);
foreach ($articles as $article) {
    $article->user; // 每次都查询用户表
}

// ✅ 好的做法
$articles = $this->model->with(['user'])->select();
```

### 2. 只查询需要的字段
```php
// ❌ 查询所有字段
$articles = $this->select([]);

// ✅ 只查询需要的字段
$articles = $this->model
    ->field('id,title,create_time')
    ->select();
```

### 3. 使用批量操作
```php
// ❌ 循环插入
foreach ($data as $item) {
    $this->create($item);
}

// ✅ 批量插入
$this->batchCreate($data);
```

## 常见问题

### Q1: Service层能直接使用Request对象吗?
**A:** 不能。Service层应该保持独立,所有参数通过方法参数传入。

```php
// ❌ 错误
public function createArticle(): Article
{
    $data = request()->post();
    return $this->create($data);
}

// ✅ 正确
public function createArticle(array $data): Article
{
    return $this->create($data);
}
```

### Q2: 如何在Service之间共享数据?
**A:** 通过依赖注入或方法参数传递。

```php
class OrderService extends BaseService
{
    protected UserService $userService;
    
    public function __construct()
    {
        $this->model = new Order();
        $this->userService = new UserService();
    }
    
    public function createOrder(array $data): Order
    {
        // 使用userService
        $user = $this->userService->find($data['user_id']);
        // ...
    }
}
```

### Q3: Service层的方法应该返回什么?
**A:** 根据操作类型决定:
- 查询单条: `Model|null`
- 查询列表: `array`
- 创建: `Model|false`
- 更新/删除: `bool`
- 复杂操作: `array`

## 下一步

- 📖 阅读 [Service层完整文档](./SERVICE_LAYER.md)
- 🏗️ 查看 [Service层实现总结](./SERVICE_LAYER_IMPLEMENTATION.md)
- 📚 学习 [项目架构说明](./ARCHITECTURE.md)

## 总结

使用Service层的关键点:

1. ✅ 继承BaseService
2. ✅ 在构造函数中初始化Model
3. ✅ 业务逻辑放在Service层
4. ✅ Controller只负责参数接收和响应
5. ✅ 使用try-catch统一处理异常
6. ✅ 复杂操作使用事务
7. ✅ 合理使用缓存
8. ✅ 记录关键操作日志

遵循这些原则,你的代码将更加清晰、可维护和可测试!