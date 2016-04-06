<a href="https://github.com/AaronJan/Housekeeper">
	<img src="https://aaronjan.github.io/Housekeeper/images/logo_v2.png" alt="Housekeeper" title="Housekeeper" align="right"/>
</a>

[![Latest Stable Version](https://poser.pugx.org/aaronjan/housekeeper/v/stable)](https://packagist.org/packages/aaronjan/housekeeper) 
[![License](https://poser.pugx.org/aaronjan/housekeeper/license)](https://packagist.org/packages/aaronjan/housekeeper)


# Housekeeper - Laravel

**`Housekeeper 2.1.*` is now in public beta, more works will be done soon.**

For version `0.9.x`, here are the [Documents](https://aaronjan.github.io/Housekeeper/0.9.x/).


## Introduction

`Housekeeper` is a powerful `DAL` (data-access-layer) library for `Laravel`, base on the design philosophy of the `Repository Pattern`, provides a lot of out-of-box features that could accelerate your development.


## Installation

### Requirement

`PHP` `>= 5.5` and `Laravel` `>= 5.1` (Not tested on `5.2`, **TODO**)


### Install Via Composer

```
$ composer require aaronjan/housekeeper ~2.1-beta
```

or add these to your `composer.json` file:

```
	"require": {
        "aaronjan/housekeeper": "~2.1-beta"
    }
```

then execute console command:

```
$ composer install
```


## Repository Pattern and Housekeeper

The `Repository Pattern` is a software design pattern. In simple words, it means to encapsulate your data interaction code as methods that belong to different classes, we call this type of class as `Repository`. When your business logic needs to accessing data such as an article entry in the database, it should ask to the `Article Repository` instead of writing inline query code that deal with database directly.

Normally `Repository` should return object (as contract between business logic to data interacting logic), but in `Laravel` we already have the powerful `Eloquent`, thus `Housekeeper` just takes advantage of it. In fact, most of the APIs in `Housekeeper` are just like the `Eloquent`'s, very easy to use.


## Quick Start

**TODO**


## Features

### Extend-less & Flow

In common situations, the `Repository Pattern` is always used with the `Decorator Pattern`. What is it? For instance, you have a repository class to interacting with data source directly, later you decide to add cache logic on top of that. So instead of changing the repository class, you could create a new class that extending it, and write something like this:

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

The `Repository class` could be seen as the bottom layer, `CachedRepository class` is another layer that base on the former, so one layer just do one thing ([SRP: Single responsibility principle](https://en.wikipedia.org/wiki/Single_responsibility_principle)).

That is a good approach. But `Housekeeper` wants to solving the problem with less code by using `Flows`.

`Flows` are four stages in every method which interacts with data must go through, they're: `Before`, `Core`, `After` and `Reset`. Every method in a `Housekeeper Repository` should be wrapped so it can runs that way. Let's see an example.

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
        return $this->getModel()  // this function give you an Eloquent instance
			->where('name', '=', $name)
			->get();
    }
}
```

Why there are two methods that had similar names? Well, the `getByName` method is basically a configuration and an API hint for the core method `_getByName`, it wrapped the core method by calling the `simpleWrap` with an `Callable` which is the `[$this, '_getByName']`, and it says what the method does is `reading` data (`Action::READ`). You don't have to worry about method arguments, `Housekeeper` will takes care of that. In fact, you don't even need to write the `[$this, '_getByName']`, since it's a convention in `Housekeeper`.

At this point, you know 'how', but you may ask 'why': why should I do this?

Let's back to the `cache logic` topic. In `Housekeeper`, if you wrapped your method like above, to add cache process, all you need to do is adding a single line of code:

```php
<?php

class ArticleRepository extends \Housekeeper\Repository
{
	use \Housekeeper\Abilities\Cache\Statically;  // Adding this
	
	//...
}
```

Now all your method results will be cached automatically, just like that.

Before you wondering why, let's continue.


### Injection & Booting

This is a flowchart of method execution in `Housekeeper`:

![method execution in Housekeeper](https://aaronjan.github.io/Housekeeper/2.x.x/images/method-execution.png)

`Housekeeper` allows you to `Inject` logic (called `Injection`) into any `Flow`, it's close to the `Middleware` but comes with 3 types: `Before`, `After` and `Reset` (because there are 3 `Flows`). Here is an example:

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

You can inject `Injection` by using the `inject` method:

```php
<?php

class ArticleRepository extends \Housekeeper\Repository
{
	// `Housekeeper` will call the `boot` method automatically with `Dependency Injection` process
	public function boot()
	{
		$this->inject(new MyBeforeInjection());
	}
	
	// ...
}
```

Here is a simple flowchart about how `Flow` works:

![method execution in Housekeeper](https://aaronjan.github.io/Housekeeper/2.x.x/images/flow.png)

`Housekeeper` also will calling every method in the `Repository` class that name start with `boot` (before calling `boot` method) when `Repository` instance been creating, some of the out-of-the-box `Abilities` in `Housekeeper` are took advantage of this, like in `Adjustable` trait:

```php
<?php

trait Adjustable
{
	// ...
	
	public function bootAdjustable()
    {
        $this->inject(new ApplyCriteriasBefore());
    }
	
	// ...
}
```


### Wrapping layer

You can write your method like this in `Housekeeper 2`:

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

Every wrapped method in `Housekeeper` has their own `Scope`, so they will not taking any affect to each other. If you call `applyWheres` or `ApplyOrderBy` outside the repository, they would only affecting the first wrapped method that you call.


### Another Choice For Wrapping

Having two methods could be annoying, but that's a choice that `Housekeeper` made.

However, the `simpleWrap` takes a `Callable`, so you can write the core method as a `Closure` instead (not recommended):

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

### Basic

-----------------------------

#### wheres(array $wheres)

**TODO**

__Arguments__

* `$wheres` - An array of `where` conditions.


-----------------------------

#### applyWheres(array $wheres)

**TODO**

__Arguments__

* Alias for the `wheres` method.


-----------------------------

#### orderBy($column, $direction = 'asc')

__Arguments__

* `$column` -
* `$direction` -


-----------------------------

#### applyOrderBy($column, $direction = 'asc')

__Arguments__

* `$column` - 
* `$direction` - 


-----------------------------

#### limit($value)

__Arguments__

* `$value` -


-----------------------------

#### exists($id [, $column = null])

**TODO**


-----------------------------

#### count()

**TODO**


-----------------------------

#### find($id [, $columns = array('*')])

**TODO**


-----------------------------

#### findMany(todo)

**TODO**


-----------------------------

#### update(todo)

**TODO**


-----------------------------

#### create(todo)

**TODO**


-----------------------------

#### delete(todo)

**TODO**


-----------------------------

#### all(todo)

**TODO**


-----------------------------

#### paginate(todo)

**TODO**


-----------------------------

#### getByField(todo)

**TODO**


-----------------------------

#### findByField(todo)

**TODO**


-----------------------------

#### with(todo)

**TODO**


-----------------------------


### Ability `Adjustable`

-----------------------------

**todo**


### Ability `Eloquently`

-----------------------------

**todo**


### Ability `Cache\\*` (All cache abilities have these methods)

-----------------------------

**todo**


### Ability `SoftDeletes`

-----------------------------

**todo**


### Ability `Vintage`

-----------------------------

**todo**


## Console Command

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
php artisan housekeeper:make MyRepository --cache=statically --eloquently --adjustable
```


## Common Example

### Caching Manually

**todo**


## TODO

1. test `Housekeeper` on `Laravel 5.2`
2. stabilize `Housekeeper 2.x` by writing more tests



## Issue

If you have any question about `Housekeeper`, feel free to create an issue, I'll reply you ASAP.

Any useful pull request are welcomed too.


## Lisence

Licensed under the [APACHE LISENCE 2.0](http://www.apache.org/licenses/LICENSE-2.0)


## Credits

Thanks to [prettus/l5-repository](https://github.com/prettus/l5-repository) for inspiring.

Thanks to [egofang](https://github.com/egofang) for the awesome LOGOs!

