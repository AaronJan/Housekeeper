<a href="https://github.com/AaronJan/Housekeeper">
	<img src="https://aaronjan.github.io/Housekeeper/images/logo_v2.png" alt="Housekeeper" title="Housekeeper" align="right"/>
</a>

[![Latest Stable Version](https://poser.pugx.org/aaronjan/housekeeper/v/stable)](https://packagist.org/packages/aaronjan/housekeeper) 
[![License](https://poser.pugx.org/aaronjan/housekeeper/license)](https://packagist.org/packages/aaronjan/housekeeper)


# Housekeeper - Laravel

After nearly six months developing, testing and polishing, the first stable version of `Housekeeper 2` is finally released!

`Housekeeper` aims to be the coolest, handiest `Repository Pattern` implementation, any useful suggestion and PR are welcomed.

Increasing unit test code coverage is a work in progress (lots of works), but there is a set of integration tests running locally that covered most code.


## Introduction

`Housekeeper` is a flexable and powerful `Repository Pattern` implemention for `Laravel`. In addition to the basic `Repository Pattern` and elegant syntax, `Housekeeper` has features like `Injection system`, Auto-Booting Method that will let you creating endless possibilities. The goal of `Housekeeper` is free you from the redundant and boring [`DAL`](https://en.wikipedia.org/wiki/Data_access_layer) stuff, coding more intuitively.


## Sections

- [Repository Pattern and Housekeeper](#repository-pattern-and-housekeeper)
- [Installation](#installation)
- [TL;DR (Quick start)](#tldr)
- [Features](#features)
- [API](#api)
- [Abilities](#abilities)
    - [Adjustable](#adjustable)
- [Issue](#issue)
- [Lisence](#lisence)
- [Credits](#credits)

## Repository Pattern and Housekeeper

The `Repository Pattern` is a software design pattern. In a nutshell, it means to encapsulate your data interaction code as methods that belong to different classes (Base on data domain), we call this type of class as `Repository`. When your business logic layer needs to accessing data such as an article entry in the database, it should ask to the `Article Repository` instead of writing inline query that deal with database directly.

OK, but ... I already got Eloquent, why not just using that?

Of course you can! But there're people who's not a fan of the Active Record, it just doesn't feel right for them, for these people, the Repository Pattern makes more sense. Besides, you can write method that is more expressive on your repository class, like **getActivatedUsers()**, and you can write tests for them very easily.

More importantly, `Housekeeper` is a better version of `Repository Pattern` (In some ways), you could read more about it below.

Housekeeper loves Eloquent. Most query APIs are the same as the Eloquent's, so you can use them without the needing to learn anything, and the returns are like Eloquent's too.


## Installation

### Requirement

`PHP` `>= 5.5` and `Laravel` `>= 5.1`

### Install Via Composer

```
$ composer require aaronjan/housekeeper ~2.3
```

or add these to your `composer.json` file:

```
	"require": {
        "aaronjan/housekeeper": "~2.3"
    }
```

then execute console command:

```
$ composer install
```

add single line of code to your `config/app.php` at `providers` for the handy console generator:

After `Composer` finish running, add the HousekeeperServiceProvider to the providers in `config/app.php`:

```php
<?php

    // ...

    'providers' => [
        // ...
        
        // Add this:
        \Housekeeper\Providers\HousekeeperServiceProvider::class,
        
        // ...
    ],
    
    // ...

```

Make a configuration file for `Housekeeper` could allow you to tweak things:

```
>$ artisan vendor:publish --provider=xxx --tag=config
```

It's done! Now you can make a repository:

```
$ artisan housekeeper:make UserRepository
```


## TL;DR

If you have outstanding insight, this section will tell you how to use `Housekeeper` in the simplest words.

* Don't write `class constructor`, use `boot` method instead, supports `Type-Hinting`.

* Any public method that name starts with **boot** and followed by an upper-case letter, for instance, **bootForInject**, then this method will be called during class initializing, also support `Type-Hinting`.

* If you want to do something before/after some methods belong across multiple repository, encapsulate these logics as `Injections` then inject them into your repositorys.

* By using the two features above, you can write `Trait` to inject `Injection`, and **use** it in your repository.

Take a example:

```php
<?php

namespace App\Repositories\Injections;

use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;

class LogTimeBefore implements BasicInjectionContract, BeforeInjectionContract
{
    public function handle(BeforeFlowContract $beforeFlow)
    {
        \Log::alert('wow');
    }
}

```

```php
<?php

namespace App\Repositories\Abilities;

use App\Repositories\Injections\LogTimeBefore;

trait TimeLogger
{
    public function bootTimeLogger()
    {
        $this->injectIntoBefore(new LogTimeBefore());
    }
}

```

```php
<?php

namespace App\Repositories;

use Housekeeper\Repository;
use App\Repositories\Abilities\TimeLogger;

class MyRepository extends Repository
{
    use TimeLogger;
    
    // ...
}

```

* `Housekeeper` has some `Abilities` (traits) that are out-of-the-box:

    * Adjustable

    * CacheStatically


* You have to write **two** methods for every method you meant to write (Recommended):

```php
<?php

class ArticleRepository extends \Housekeeper\Repository
{
	protected function model()
    {
        return Article::class;
    }

    public function getByName($name)
    {
        return $this->simpleWrap(Action::READ, [$this, '_getByName']);
    }

    protected function _getByName($name)
    {
        return $this->getModel()  // this function give you an Eloquent / Builder instance
			->where('name', '=', $name)
			->get();
    }
}

```

That's it, take a look at the `Abilities`' code for more usages, Have fun!


## Features

### Extend-less & Flows

The `Repository Pattern` is usually used with the `Decorator Pattern`. For instance, you have a repository class to interacting with data source directly, later you decide to add cache logic on top of that, so instead of changing the repository class its self, you could create a new class that extending it, and the whole thing may looks like this:

```php
<?php

class Repository
{
	public function findBook($id)
	{
		return Book::find($id);
	}
}
```

```php
<?php

use Cache;

class CachedRepository extends Repository
{
	public function findBook($id)
	{
		return Cache::remember("book_{$id}", 60, function () use ($id) {
			return parent::findBook($id);
		});
	}
}
```

The `Repository class` is the bottom layer, `CachedRepository class` is another layer that base on the former, so one layer just do one thing ([SRP: Single Responsibility Principle](https://en.wikipedia.org/wiki/Single_responsibility_principle)).

That is a good approach. But `Housekeeper` wants to solving the problem with less code by using `Flows`.

`Flows` are three stages in every method execution, they're: `Before`, `After` and `Reset`. Every method in a `Housekeeper Repository` should be wrapped so it can go through these `Flows`. Here is an example:

```php
<?php

class ArticleRepository extends \Housekeeper\Repository
{
	protected function model()
    {
        return Article::class;
    }

    public function getByName($name)
    {
        return $this->simpleWrap(Action::READ, [$this, '_getByName']);
    }

    protected function _getByName($name)
    {
        return $this->getModel()  // this function give you an Eloquent / Builder instance
			->where('name', '=', $name)
			->get();
    }
}
```

Why there are two methods that had similar names? Well, the `getByName` method is basically a configuration and an API hint for the core method `_getByName`, it wrapped the core method by calling the `simpleWrap` with an `Callable` which is `[$this, '_getByName']`, It says what this method does is `reading` data (`Action::READ`), the whole reading logic is in the `_getByName` method. 

You don't have to worry about method arguments, `Housekeeper` will takes care of that. In fact, you don't even need to write `[$this, '_getByName']`, since it's a convention in `Housekeeper` (An underscore before your method name):

```php
<?php

public function getByName($name)
{
    return $this->simpleWrap(Action::READ);
}

```

Let's back to the `cache logic` topic. In `Housekeeper`, if you wrapped your method like above, than to adding cache process, all you need to do is writing a single line of code like this:

```php
<?php

class ArticleRepository extends \Housekeeper\Repository
{
	use \Housekeeper\Abilities\CacheStatically;  // Adding this
	
	//...
}
```

Now all your method returns will be cached automatically, just like that.

Is it cool?


### Injection & Booting

Here a sequence diagram of method execution in `Housekeeper`:

![method execution in Housekeeper](https://aaronjan.github.io/Housekeeper/2.x.x/images/method-execution.png)

`Housekeeper` allows you to **inject** logic (called `Injection`) into any `Flow`, in every `Flow`, the `Injections` that belong to the `Flow` will be executed. `Injection` is just like `Middleware` but with 3 types: `Before`, `After` and `Reset` (matching 3 different injectable `Flows`). Here is an example:

```php
<?php

class MyBeforeInjection implements \Housekeeper\Contracts\Injection\Before
{
	public function priority()
	{
		return 30;  // Smaller first
	}
	
	// main method
	public function handle(\Housekeeper\Contracts\Flow\Before $beforeFlow)
	{
		// In here you can get the `Action` object
		$action = $beforeFlow->getAction();
		
		// Or get the `Repository`
		$repository = $beforeFlow->getRepository();
		
		// And you can set the returns (Only in `Before Flow`)
		$beforeFlow->setReturnValue(1);
	}
}
```

The `handle` method in `Injection` takes a `Flow` object, depends on what `Flow` you injected into, the methods of the `Flow` object could be different, for instance, `Before Flow` provides `setReturnValue` method, you could call it by pass a value to it, then `Housekeeper` will use this value as the return and skip the actual method.

You can inject `Injection` by using the these methods: `injectIntoBefore`, `injectIntoAfter` and `injectIntoReset`.

```php
<?php

class ArticleRepository extends \Housekeeper\Repository
{
	// `Housekeeper` will call the `boot` method automatically with `Dependency Injection` process
	public function boot()
	{
        $this->injectIntoBefore(new MyBeforeInjection());
	}
	
	// ...
}
```


Here is flowchart of the `Before Flow` execution:

![method execution in Housekeeper](https://aaronjan.github.io/Housekeeper/2.x.x/images/flow.png)

`Housekeeper` also will calling every method in the `Repository` class that name start with `boot` (before calling the `boot` method) when `Repository` instance been creating, some of the out-of-the-box `Abilities` in `Housekeeper` are took advantage of this, like in `Adjustable` trait:

```php
<?php

trait Adjustable
{
	// ...
	
	public function bootAdjustable()
    {
        $this->injectIntoBefore(new ApplyCriteriasBefore());
    }
	
	// ...
}
```


### Wrapping layer

Let's assume someone wrote code like these:

```php
<?php

use Housekeeper\Action;

class ArticleRepository extends \Housekeeper\Repository
{
	public function getArticlesByAuthorId($authorId)
	{
		return $this->simpleWrap(Action::READ);
	}
	
	protected function _getArticlesByAuthorId($authorId)
	{
		return $this
			->applyWheres([
				['author_id', '=', $authorId],
			])
			->get();
	}
	
	public function getArticlesBySameAuthor($articleId)
	{
		return $this->simpleWrap(Action::READ);
	}
	
	protected function _getArticlesBySameAuthor($articleId)
	{
		$article = $this->getModel()->find($articleId, ['id', 'author_id']);
		
		return $this->getArticlesByAuthorId($article->author_id);
	}
	
	
	// ...
}
```

```php
<?php

class ArticleController
{
	public function getRecommendForArticle(ArticleRepository $articleRepository, $articleId)
	{
		$articles = $articleRepository
			->applyWheres([
				['language', '=', 'chinese'],
			])
			->getArticlesBySameAuthor($articleId);
		
		return view('article.recommend-for-article', compact('articles'));
	}	
	
	// ...
}
```

In this example, the `applyWheres` method has been used twice, one is in the `Controller`, the other is in the `Repository`, could the first one affecting the `_getArticlesByAuthorId` method? No. It will only affecting the `_getArticlesBySameAuthor` method, and be more precisely, it's affecting this line:

```php
$article = $this->getModel()->find($articleId, ['id', 'author_id']);
```

Every wrapped method in `Housekeeper` has their own `Scope`, means they have their own `Eloquent Model` (Or `Builder`), thus they will not taking any affect to each other. If you calling `applyWheres` or `ApplyOrderBy` outside the repository, they would only affecting the first wrapped method you called.


### Another Choice For Wrapping

Having two methods could be annoying, you can write an `Anonymous Function`, before the `simpleWrap` takes a `Callable`:

```php
<?php

	public function getByName($name)
    {
        return $this->simpleWrap(Action::READ, function (name) {
			return $this->getModel()
				->where('name', '=', $name)
				->get();
		});
    }
```


## API

-----------------------------

#### whereAre(array $wheres)

Add an array of where clauses to the query.

__Arguments__

* `$wheres` - An array of `where` conditions.

__Example__

```php
<?php

$userRepository
	->whereAre([
		['age', '>', 40],
		['area', 'west']
	])
	->all();
```

```php
<?php

$userRepository
    ->whereAre([
        ['area', 'east'],
        function ($query) {
            $query->whereHas('posts', function ($hasQuery) {
                $hasQuery->where('type', 1);
            });
            
            $query->whereNull('has_membership');
        },
    ])
    ->paginate(12);

```


-----------------------------

#### applyWheres(array $wheres)

Alias for the `whereAre` method.

__Arguments__

* `$wheres` - An array of `where` conditions.


-----------------------------

#### orderBy($column, $direction = 'asc')

Add an "order by" clause to the query.

__Arguments__

* `$column`
* `$direction`

__Example__

```php
<?php

$UserRepository
	->orderBy('age', 'desc')
	->all();
```


-----------------------------

#### applyOrderBy($column, $direction = 'asc')

Alias for the `orderBy` method.

__Arguments__

* `$column`
* `$direction`


-----------------------------

#### offset($value)

Set the "offset" value of the query.

__Arguments__

* `$value` - The specified offset of the first row to return.

__Example__

```php
<?php

$UserRepository
	->limit(10)
	->all();
```


-----------------------------

#### limit($value)

Set the "limit" value of the query.

__Arguments__

* `$value` - The maximum number of rows to return.

__Example__

```php
<?php

$UserRepository
	->limit(10)
	->all();
```


-----------------------------

#### exists($id, $column = null)

Determine if the record exists using its primary key.

__Arguments__

* `$id` - The primary key of the record.
* `$column` - You could also specify a column other than primary key, and change the value of `$id` correspondingly.

__Examples__

```php
<?php

$userRepository->exists(3);

```

```php
<?php

$userRepository->exists('name', 'John');

```

You could use this method with custom query conditions too:

```php
<?php

$userRepository->whereAre(['gender' => 'female'])->exists(1);

```


-----------------------------

#### count($columns = '*')

Retrieve the "count" result of the query.

__Arguments__

* `$columns`


-----------------------------

#### find($id, $columns = array('*'))

Find a model by its primary key.

__Arguments__

* `$id`
* `$columns` - Specify columns that you want to retrieve.

__Examples__

```php
<?php

$userRepository->find(1, ['id', 'name', 'gender', 'age']);

```


-----------------------------

#### findMany($ids, $columns = array('*'))

Find a collection of models by their primary key.

__Arguments__

* `$ids`
* `$columns` - Specify columns that you want to retrieve.


-----------------------------

#### update($id, array $attributes)

Update a record in the database.

__Arguments__

* `$id`
* `$attributes`

__Examples__

```php
<?php

$userRepository->update(24, [
    'name' => 'Kobe Bryant'
]);

```


-----------------------------

#### create(array $attributes)

Create a model with `$attributes`.

__Arguments__

* `$attributes`


-----------------------------

#### delete($id)

Delete a record from the database by its primary key.

__Arguments__

* `$id`


-----------------------------

#### all($columns = ['*'])

Execute the query as a "select" statement.

__Arguments__

* `$columns`


-----------------------------

#### paginate($limit = null, $columns = ['*'], $pageName = 'page', $page = null)

Paginate the given query.

__Arguments__

* `$limit`
* `$columns`
* `$pageName`
* `$page`


-----------------------------

#### getByField($field, $value = null, $columns = ['*'])

Retrieve models by a simple equality query.

__Arguments__

* `$field`
* `$value`
* `$columns`


-----------------------------

#### with($relations)

Set the relationships that should be eager loaded.

__Examples__

```php
<?php



```


-----------------------------

## Adjustable

For more complex queries, you could put them in a `Criteria` class that is more semantic and reuse them anywhere you want, for that, using the `Adjustable` ability.

### Examples

```php
<?php

namespace App\Repositories\Criterias;

class ActiveUserCriteria implements Housekeeper\Abilities\Adjustable\Contracts\Criteria
{
    public function apply(Housekeeper\Contracts\Repository $repository)
    {
        $repository->whereAre([
            ['paid', '=', 1],
            ['logged_recently', '=', 1],
        ]);
    }
}

```

Then in your **controller**:

```php
<?php

$activeUserCriteria = new ActiveUserCriteria();

// UserRepository must used the `Adjustable` trait
$activeUsers = $userRepository->applyCriteria($activeUserCriteria)->all();

// Or you can remember this Criteria:
$userRepository->rememberCriteria($activeUserCriteria);

$activeUsers = $userRepository->all();

$femaleActiveUsers = $userRepository->where('gender', '=', 'female')->all();

```


### API

-----------------------------

#### applyCriteria(\Housekeeper\Abilities\Adjustable\Contracts\Criteria $criteria)

Apply this `Criteria` only once.

__Arguments__

* `$criteria` - Criteria object.


-----------------------------

#### rememberCriteria(\Housekeeper\Abilities\Adjustable\Contracts\Criteria $criteria)

Remember this `Criteria`, it will be applied when every wrapped method been called (Only the first one, iternal method calling will be ignored).

__Arguments__

* `$criteria` - Criteria object.


-----------------------------

#### forgetCriterias()

Remove all remembered `Criterias` (Not applied).


-----------------------------

#### getCriterias()

Get all remembered `Criterias`.


-----------------------------

## Eloquently

This `Abilitiy` provides lots of `Eloquent` style query APIs that you are very familiar with.


### API

-----------------------------

#### where($column, $operator = null, $value = null, $boolean = 'and')


-----------------------------

#### orWhere($column, $operator = null, $value = null)


-----------------------------

#### has($relation, $operator = '>=', $count = 1, $boolean = 'and', \Closure $callback = null)


-----------------------------

#### whereHas($relation, Closure $callback, $operator = '>=', $count = 1)


-----------------------------

#### whereDoesntHave($relation, Closure $callback = null)


-----------------------------

#### orWhereHas($relation, Closure $callback, $operator = '>=', $count = 1)


-----------------------------

#### whereIn($column, $values, $boolean = 'and', $not = false)


-----------------------------

#### whereNotIn($column, $values, $boolean = 'and')


-----------------------------

#### orWhereNotIn($column, $values)


-----------------------------

#### whereNull($column, $boolean = 'and', $not = false)


-----------------------------

#### orWhereNull($column)


-----------------------------

#### whereNotNull($column, $boolean = 'and')


-----------------------------

#### orWhereNotNull($column)


-----------------------------

## CacheStatically

This `Ability` implemented a very simple cache system: Caching all method returns, and delete them all when creating/updating/deleting, you can clear cache manually too.

Once you use this `Ability`, everything is automatic. `all()`, `find()`, `paginate()` and others will go through the cache logic, if any cached return be found, then no database query will be executed. Different method has different cache key, even applying query will change the cache key.

This `Ability` may not be much practical in large project, but it shows the flexibility of `Housekeeper`, and other cache system is in the roadmap.


### Examples

```php
<?php

// Cache is disabled by default, you have to enable it first.
$userRepository->enableCache()->all();

// This also will be cached!
$userRepository->where('age', '<', '30')->orderBy('age', 'desc')->all();

```

Wrapped methods has their own cache:

```php
<?php

class UserRepository extends Housekeeper\Repository
{
    use Housekeeper\Abilities\CacheStatically;
    
    public function getOnlyActive() // Cached
    {
        return $this->simpleWrap(Housekeeper\Action::READ);
    }
    
    protected function _getOnlyActive()
    {
        // Every wrapped method has it's own scope, they don't interfere with each other
        return $this->whereAre([
            ['paid', '=', 1],
            ['logged_recently', '=', 1],
        ])
            ->all(); // Cached too
    }
}

```

### API

-----------------------------

#### enableCache()

Enable cache system.


-----------------------------

#### disableCache()

Disable cache system.


-----------------------------

#### isCacheEnabled()

Indicate whether cache system is enabled or not. 


-----------------------------

#### clearCache()

Delete all caches of this repository.


-----------------------------

## Guardable

`Housekeeper` ignored `Mass Assignment Protection` by default, use this `Ability` if you need it.

`Guardable` disabled `Mass Assignment Protection` by default too, you have to turn it on manually.


### Examples

```php
<?php

// For inputs that we can't trust
$userRepository->guardUp()->create($request->all());

// But we can trust our internal process
$userRepository->guardDown()->create($attributes);

```


### API

-----------------------------

#### guardUp()

Enable `Mass Assignment Protection`。


-----------------------------

#### guardDown()

Disable `Mass Assignment Protection`。


-----------------------------

#### isGuarded()

Whether or not the `Mass Assignment Protection` is enabled.


-----------------------------

## SoftDeletes

To utilize the `SoftDeletes` trait of the `Eloquent`, you should use this `Ability` in your repository.


### API

-----------------------------

#### startWithTrashed()

Include soft deletes.


-----------------------------

#### startWithTrashedOnly()

Include soft deletes only.


-----------------------------

#### forceDelete($id)

Hard delete a record by primary key.

__Arguments__

* `$id` 


-----------------------------

#### restore($id)

Restore a soft-deleted record by primary key.

__Arguments__

* `$id` 


-----------------------------


## Console Commands

Create a new repository：

```
php artisan housekeeper:make MyRepository
```

Create a new repsitory and a new model：

```
php artisan housekeeper:make MyRepository --create=Models\\Student
```

Create a new repository with some `Abilities`：

```
php artisan housekeeper:make MyRepository --cache=statically --eloquently --adjustable --sd
```


## Issue

If you have any question about `Housekeeper`, feel free to create an issue, I'll reply you ASAP.

Any useful pull request are welcomed too.


## Lisence

Licensed under the [APACHE LISENCE 2.0](http://www.apache.org/licenses/LICENSE-2.0)


## Credits

Thanks to [prettus/l5-repository](https://github.com/prettus/l5-repository) for inspiring.

Thanks to [sunkey](https://github.com/sunkeyfong) for the awesome LOGOs!

Thanks to [Laravel](https://github.com/laravel/framework) for making our life easier!
