# Laravel Package: Counter Cache
Package Counter Cache for Laravel 5

[![Latest Stable Version](https://poser.pugx.org/quankim/laravel-counter-cache/v/stable)](https://packagist.org/packages/quankim/laravel-counter-cache)
[![Total Downloads](https://poser.pugx.org/quankim/laravel-counter-cache/downloads)](https://packagist.org/packages/quankim/laravel-counter-cache)
[![Latest Unstable Version](https://poser.pugx.org/quankim/laravel-counter-cache/v/unstable)](https://packagist.org/packages/quankim/laravel-counter-cache)
[![License](https://poser.pugx.org/quankim/laravel-counter-cache/license)](https://packagist.org/packages/quankim/laravel-counter-cache)
## Feature Overview
* Increment counter automatically when creating a new record.
* Decrement counter automatically when deleting a record.
* Update counter automatically when updating a record
* Update rating_average automatically when CRUD a record
* Custom field when CRUD a record
## Install
```bash
composer require quankim/laravel-counter-cache
```
# Usage
I will use the example products/comments, one product have many comments

### Migration
Table products:
```php
Schema::create('products', function (Blueprint $table) {
      $table->increments('id');
      $table->string('name');
      $table->integer('comments_count')->default(0);
      $table->float('rating_average', 15, 1)->nullable();
      $table->timestamps();
  });
```
Table comments:
```php
Schema::create('comments', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('product_id');
      $table->string('content');
      $table->integer('rating_value')->nullable();
      $table->timestamps();
  });
```
### Model
Model Product:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;

class Product extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ratingAverage()
    {
        return round($this->comments()->avg('rating_value'), 1);
    }
}

```
Model Comment:
```php
<?php
namespace App\Models;

use QuanKim\LaravelCounterCache\Traits\CounterCache;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Comment extends Model
{
    use CounterCache;

    public $counterCacheOptions = [
        'product' => [
            'comments_count' => [],
            'rating_average' => [
                'method' => 'ratingAverage',
            ],
        ],
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

```

### Add Conditions
```php
public $counterCacheOptions = [
    'product' => [
        'comments_count' => [],
        'rating_average' => [
            'method' => 'ratingAverage',
            'conditions' => [
                'is_publish' => true,
            ],
        ],
    ],
];
```
### Credits
Vo Hong Quan
