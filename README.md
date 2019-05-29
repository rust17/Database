# Database

#### 介绍

自用 ORM，原生 PHP，模拟 Laravel 的调用方式

### 可用 API

* 拼接 sql 条件
    1. where
        * where('name', 'John')
        * where('status', '<>', 1)
        * where([['status', '=', 1], ['subscribed', '<>', '1']])
        * where(function ($query) { 

            $query->where('votes', '>', 200)
            
            ->orWhere('title', '=', 'Admin'); 
        })
    2. join
        * join('contacts', 'users.id', '=', 'contacts.user_id')
    3. leftjoin
        * leftjoin('posts', 'users.id', '=', 'posts.user_id')
    4. rightjoin
        * rightjoin('posts', 'users.id', '=', 'posts.user_id')
    5. whereNull
        * whereNull('first_name')
    6. orWhere
        * orWhere('name', 'John')
        * orWhere('title', '=', 'Admin')
    7. whereIn
        * whereIn('id', [1, 2, 3])
    8. whereNotIn
        * whereNotIn('id', [1, 2, 3])
    9. whereNotNull
        * whereNotNull('updated_at')

* 执行 sql 语句
    1. find
        * find(1)
        * find([1, 2, 3])
    2. first
    3. get
    4. count
    5. insert
    6. update
        * update(['active' => true])
    7. increment
        * increment('votes')
        * increment('votes', 5)
    8. decrement
        * decrement('votes')
        * decrement('votes', 5)
    9. delete
    10. softDeletes
        * 使用 is_deleted 删除
    11. exists
    
* 聚合、排序
    1. orderBy
        * orderBy('id')
        * orderBy('name', 'desc')
    2. latest
        * 使用 created_at 排序
    3. oldest
        * 使用 created_at 排序
    4. groupBy
        * groupBy('account_id')
        * groupBy('first_name', 'status')
    5. offset
        * offset(10)
    6. limit
        * limit(5)

### 使用方式

引入

```php
require('/path/to/Database/DatabaseLoad.php');
Database\DatabaseLoad::register_autoloader();
```

定义一个模型

```php
use Database\Eloquent\Model;

class Animal extends Model
{
    public $table = 'table_name'; // 指定模型对应的表

    // 定义一个关联关系
    public function Bird()
    {
        return $this->hasOne(Bird::class, 'foreign_id'); // 指定关联模型的类名，外键名
    }
}
```

使用

```php
$animal = new Animal();
$bird   = $animal->Bird();
$birds  = $bird
          ->where(function ($query) {
              $query->where('can_fly', 1)
                    ->whereIn('id', [6, 7]);
          })
          ->where('is_del', 0)
          ->get();
```
### 设计思路


### 参考
* [lizhichao / one](https://github.com/lizhichao/one)
* [laravel / framework](https://github.com/laravel/framework)