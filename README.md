# ThaiLe Repository package

## Overview

The ThaiLe Repository is used to abstract the data layer, making our application more flexible to maintain

You want to know a little more about the Repository pattern? [Read this great article](http://bit.ly/1IdmRNS).

## Table of Contents

- [Installation](#installation)
    - [Composer](#composer)
    - [Laravel](#laravel)
- [Methods](#methods)
    - [Repository Interface](#repositoryinterface)
- [Usage](#usage)


## Installation

### Composer

Update the composer.json file:

```
{
    "require": {
        "thaile/repository": "dev-master"
    }
}
```

From the root directory of your project run the command:

```
composer update
```

### Laravel

In the config/app.php add `ThaiLe\Repository\Providers\RepositoryServiceProvider::class` to the end of the `provider` array

Publish Configuration

```
php artisan vendor:publish --provider="ThaiLe\Repository\Providers\RepositoryServiceProvider"
```

## Methods

### Repository Interface

ThaiLe\Repository\Contracts\RepositoryInterface

- all($columns = array('*'))
- paginate($limit = null, $columns = ['*'])
- find($id, $columns = ['*'])
- findSoftDelete($id, $columns = ['*'])
- findByField($field, $value, $columns = ['*'])
- findWhere(array $where, $columns = ['*'])
- findWhereIn($field, array $where, $columns = [*])
- findWhereNotIn($field, array $where, $columns = [*])
- create(array $attributes)
- update(array $attributes, $id)
- delete($id)
- with(array $relations)
- applyCriteria()
- pushCriteria()
- popCriteria()
- getCriteria()
- getByCriteria()
- skipCriteria()
- resetCriteria()


## Usage

### In your model

Create a model with the fillable attributes

```php

namespace App;

use Illuminate\Database\Eloquent

class Book extends Eloquent
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'category', 'price',
    ];
}
```

### In your repository

Create a repository interface
```php

namespace App\Repositories\Contracts;

class BookRepositoryInterface
{

}
```

Create a repository

```php

namespace App\Repositories\Eloquents;

use ThaiLe\Repository\Eloquent\BaseRepository;
use App\Book;
use App\Repositories\Contracts\BookRepositoryInterface;

class BookRepository extends BaseRepository implements BookRepositoryInterface
{

    /**
         * Specify Model class name
         *
         * @return string
         */
        function model()
        {
            return Book::class;
        }
}
```

### In the register of the AppServiceProvider or any custom service provider

```
$this->app->bind(BookRepositoryInterface::class, BookRepository::class);
```

### In your controller

```php
namespace App\Http\Controllers;

use App\Repositories\Contracts\BookRepositoryInterface;

class BookController extends Controller
{

    /**
     * View the listing books.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BookRepositoryInterface $bookRepository)
    {
        $bookRepository->all();

        return view('home');
    }
}

```

### Create a Criteria

Criteria is a way to change query logic by specific conditions.

- Create a MyBookCriteriaInterface

```php
namespace App\Repositories\Contracts\Criteria;

interface MyBookCriteriaInterface {}
```

- Implements in the MyBooksCriteria

```php

namespace App\Repositories\Eloquents\Criteria;

use ThaiLe\Repository\Contracts\CriteriaInterface;
use ThaiLe\Repository\Contracts\RepositoryInterface;
use App\Repositories\Contracts\Criteria\MyBookCriteriaInterface;

class MyBooksCriteria implements CriteriaInterface, MyBookCriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
        {
            $model = $model->where('user_id','=', Auth::user()->id );
            return $model;
        }
}
```

### In the register of the AppServiceProvider or any custom service provider

```
$this->app->bind(MyBookCriteriaInterface::class, MyBooksCriteria::class);
```

### Using in your controller or whatever logical file

```php
namespace App\Http\Controllers;

use App\Repositories\Contracts\BookRepositoryInterface;
use App\Repositories\Contracts\Criteria\MyBookCriteriaInterface;

class BookController extends Controller
{

    /**
     * View the listing books.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BookRepositoryInterface $bookRepository, MyBookCriteriaInterface $criteria)
    {
        $bookRepository->pushCriteria($criteria);
        $bookRepository->all();

        return view('home');
    }
}

```

### Generators

Create your repositories (Repository, RepositoryInterface, Model, Migration file) quickly thought the generator

### Configuration

All generator configurations are located in the config/repository.php file.

```
'generator'  => [
        'basePath'      => app_path(),
        'rootNamespace' => 'App\\',
        'paths'         => [
            'repositories' => 'Repositories/Eloquents',
            'interfaces'   => 'Repositories/Contracts',
            'models'       => 'Models',
        ]
    ]
```

### Commands

Using the following command to generate a repository for your Book model

```
php artisan make:repository Book
```

Generating with some fields are fillable

```
php artisan make:repository Book --fillable="string:title,text:content"
```
