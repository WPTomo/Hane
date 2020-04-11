<h1 align="center"> Hane </h1>

<p align="center"> A lightweight API data conversion layer use for Laravel framework.</p>


## 安装

```shell
$ composer require wptomo/hane -vvv
```

## 使用

```shell
$ php artisan make:converter UserConverter
```

### 基本

```php
use Wptomo\Hane\Converter;

class UserConverter extends Converter
{
    public function toArray($model) : array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'gender' => $model->gender,
            'address' => $model->address,
        ];
    }
}

// Model
$array = new UserConverter(User::find(1))->convert();

// Collection
$array = new UserConverter(User::all())->convert();
```

示例（Model）：

```json
{
    "id": 1,
    "name": "wptomo",
    "gender": 1,
    "address": "China" 
}
```

Collection:

```json
[
    {
        "id": 1,
        "name": "wptomo",
        "gender": 1,
        "address": "China" 
    },
    {
        "id": 2,
        "name": "wptomo",
        "gender": 1,
        "address": "China" 
    }
]
```

### 保留部分数据

```php
$array = new UserConverter(User::find(1), ['id', 'name'])->convert();
```

示例：

```json
{
    "id": 1,
    "name": "wptomo"
}
```

### 自定义包裹

```php
$array = new UserConverter(User::find(1), ['id', 'name'], 'user')->convert();
```

示例：

```json
{
    "user": {
        "id": 1,
        "name": "wptomo"
    }
}
```

### 附加数据

```php
$array = new UserConverter(User::all(), ['id', 'name'], 'data', ['pagination' => []])->convert();
```

示例：

```json
{
    "data": [
        {
            "id": 1,
            "name": "wptomo"
        }
    ],
    "pagination": {}
}
```

> {注意}：附加数据一般配合数据包裹使用

### 包含数据

```php
use Wptomo\Hane\Converter;

class UserConverter extends Converter
{
    protected $include = ['posts'];

    public function toArray($model) : array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'gender' => $model->gender,
            'address' => $model->address,
        ];
    }

    protected function includePosts($model)
    {
        return new PostConverter($model->posts);
    }
}
```

访问：

```text
https://api.wptomo.hane/user?include=posts
```

示例：

```json
{
    "id": 1,
    "name": "wptomo",
    "gender": 1,
    "address": "China",
    "posts": []
}
```

### 回调

```php
use Wptomo\Hane\Converter;

class UserConverter extends Converter
{
    protected $shouldCallback = 'doSomethingElse';

    public function toArray($model) : array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'gender' => $model->gender,
            'address' => $model->address,
        ];
    }

    protected function doSomethingElse($convertedData, $originalDataType)
    {
        // Do something...
    }
}
```

## License

MIT
