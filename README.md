<a href="https://github.com/AaronJan/Housekeeper">
	<img src="https://aaronjan.github.io/Housekeeper/images/logo.png" alt="Housekeeper" title="Housekeeper" align="right"/>
</a>

[![Latest Stable Version](https://poser.pugx.org/aaronjan/housekeeper/v/stable)](https://packagist.org/packages/aaronjan/housekeeper) [![Total Downloads](https://poser.pugx.org/aaronjan/housekeeper/downloads)](https://packagist.org/packages/aaronjan/housekeeper) [![Latest Unstable Version](https://poser.pugx.org/aaronjan/housekeeper/v/unstable)](https://packagist.org/packages/aaronjan/housekeeper) [![License](https://poser.pugx.org/aaronjan/housekeeper/license)](https://packagist.org/packages/aaronjan/housekeeper)


# Housekeeper - Laravel

Here is the [Documents](https://aaronjan.github.io/Housekeeper/).


## Introduction

Powerful, simple `Repository-Pattern` implementation for Laravel `(>= 5.0)` and PHP `(>= 5.5)`, and it come with tests.


## Sections


- [About Repository-Pattern](#repository-pattern)
- [About Housekeeper](#about-housekeeper)
	- [Housekeeper Features](#housekeeper-features)
	- [What's the Differents And How Housekeeper Works](#whats-the-differents-and-how-housekeeper-works)
- [Installation](#installation)
- [Usage](#usage)
    - [Create a repository](#create-a-repository)
	- [Write your repository method](#write-your-repository-method)
	- [Action](#action)
	- [Traits](#traits)
		- [Cacheable](#cacheable)
		- [Adjustable](#adjustable)
		- [Metadata](#metadata)
- [Development Logs](#development-logs)
- [Issue](#issue)
- [Lisence](#lisence)
- [Credits](#credits)


## Repository-Pattern


There are many articles about **How to implement `Repository-Pattern` in `Laravel`**, and they're a little different from one another. but in general, the idea is you only ask data from your `repository`. 

If you want to caching result, you can `extend` your `basic repository`, override methods, add some caching logic to them, or using `Decorator-Pattern` to do the same, by utilizing `IOC` in `Laravel`, you can switch them easily, usage still be the same.


## About Housekeeper


I searched `GitHub` for `Repository-Pattern` `PHP` packages, there are some, but most seems incomplete or not catching up with `Laravel 5`, but there is one stood up, it's [prettus/l5-repository](https://github.com/prettus/l5-repository).

After reading the documents and codes, I'm start to wondering: *maybe `Repository-Pattern` could be simpler*? And `prettus/l5-repository` is too much for me, `RequestCriteria`, `Validation`... They are great features, but what if I don't want them in my repository? Can I just import them in on my demond? So I wrote `Housekeeper`. 

I started from the code of `prettus/l5-repository` in `2015/06/25`, but they're completely different now.

As of today, `Housekeeper` have been using by multiple projects.


### Housekeeper Features


* Laravel console generators
* **Provides basic repository method**
	* `create`, `find`, `update`, `delete`, `paginate`, `all`, `findByField`, `findWhere`, `with`, only the basics
* **`Cache`, `Criteria` features are traits**
	* Say bye-bye to boring duplicated code
* **Easy to extend**
	* See [here](https://github.com/AaronJan/Housekeeper/tree/master/src/Housekeeper/Traits/Repository), really easy
* **Clean code**
	* If you like the old-fashioned layer technic, `Housekeeper` will not get in your way
* **Comes with tests**
	* Aim to `85%` (or even more), still adding


### What's the Differents And How Housekeeper Works


Traditional `Repository-Pattern` is all about `layer`, want to caching data, you add a layer. That's great concept, and you can still do that in `Housekeeper`. But features like `cache`, `criteria` I used something like `middleware`.

Each repository method calling in `Housekeeper` is called `Action`, it go through four `Flows`: *Before*, *Your method logic*, *Reset* and *After*, result could be returned in *Before* or *After* `Flows`. You can write `Injections` to extending any `Flow`'s logic.

#### Example

Talk is cheap, I'll show you some code. Let's talk about an example, like the logic of `Caching`:

	When a request coming in, Check if cache exists
	If it's, then return the cached result
	If it's not, do the logic then caching result
	When creating, updating or deleting happens, clear the cache
	
That's `Cache` right? if you do the traditional way, you will end up with a lot of duplicated code.

But with `Injection`, you just put the `cache-checking` in a *Before* injection, and put `cache-setting` in an *After* injection. You can identify `read`/`create`/`update`/`delete` `Actions` in the `Flow` (even get the name of method), so you write `cache-cleanning` logic in an *After* injection specify for `create`/`update`/`delete` `Action`. In the end, write a `Trait` with just one method `setupCache` like this:

```php
trait Cache
{
	protected function setupCache()
	{
		// Your before injection
		$this->inject(new GetCacheIfExistsBefore($this->app));
		
		// Your after injection
		$this->inject(new CacheResultAfter($this->app));
	}
}
```

That's all. And that's how `Housekeeper` did, you can check it at [here](https://github.com/AaronJan/Housekeeper/blob/master/src/Housekeeper/Traits/Repository/Cacheable.php).

Each `Injection` has a `priority` method, so if you want to add some logic before `Cache`, just return a integer that lower then `Cache`'s `priority`.


#### What's this Setup


In repository's constructor, it will calling every method that name started with *setup* and followed with an `upper-case` letter. you can inject your `Injections` in such method.


## Installation


`Housekeeper` comes with a `Composer` package, you can install `Housekeeper` very easily.

Require this package with `Composer`:

```shell
composer require aaronjan/housekeeper
```

After `Composer` command completed, then add the service provider to the to `config/app.php`:

```php

/*
 * Housekeeper
 */
\Housekeeper\Providers\HousekeeperServiceProvider::class,

```

That's all. If you run `php artisan`, you should see something in the command list like this:

	 housekeeper
       housekeeper:make:repository  Make a repository file.


## Usage


### Create a repository


#### Prepare your model


First create your model anyway you like, since `create` and other some methods in `Housekeeper` used the [`Mass Assignment`](http://laravel.com/docs/5.1/eloquent#mass-assignment) feature of `Laravel`, you need to specify `fillable` variables in your model like this:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'content'];
}

```


#### Using Repository Generator


Then you can create a repository using `generator`:

```shell
php artisan housekeeper:make:repository ArticleRepository --cacheable --adjustable
```

You may not need `--cacheable` or `--adjustable`, but you should keep that in mind, they're easy to use.

After this command, a repository file named `ArticleRepository.php` should in your `app/Repositories/` folder, and looks like this:

```php
<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use Housekeeper\Traits\Repository\Adjustable;
use Housekeeper\Traits\Repository\Cacheable;

class ArticleRepository extends BaseRepository {

    use Adjustable, Cacheable;

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        //
    }

}
```

Add a line of code in the `model` method, return the model name of this repository (I put models in `app/Models/`):

```php
protected function model()
{
    return \App\Models\Article::class;
}
```

Then you just created your first `Housekeeper` repository.


### Write your repository method


All methods of repository should be wrapped like this:

```php
public function yourMethod($argument)
{
    return $this->wrap(function ($argument) {

		//Method logic

        return $result;

    }, new Action(__METHOD__, func_get_args(), Action::READ));
}
```

Looks complicated, but just two parts: first is your actual method that returns result, second is an `Action` object that represents this method, so all `Flows` could identity this method.

First argument of `wrap` just need to be a `Callable`, means you can use things other than `Closure`.


### Action


`Action` represents a method call, whole class is `Housekeeper\Action`, it's constructor needs **three** parameters: `Method Name`, `Method Arguments` and `Method Type`.

`Method Type` could be any one in `UNKNOW`, `CREATE`, `UPDATE`, `READ` and `DELETE`, it's a convention for `Injection` to work with.


### Traits


`Housekeeper` comes with **Three** traits that you can use, like `Cacheable`, simple add `use \Housekeeper\Traits\Repository\Cacheable;` to your repository is all.


#### Cacheable


Priority **50**.

`Cacheable` works with `Redis`.

`Cacheable` trait will caching all result returned by a `Read Action`, and clear all cache of this repository when `Create Action`, `Update Action` or `Delete Action` be called.

Notice this, every method has it's own cache key, arguments that pass to the method will change the cache key too.


#### Adjustable

Priority **10**.

`Adjustable` allow you to pack some search conditions into a `Criteria` object, so you can reuse them, `Criteria` object should implements `Housekeeper\Contracts\CriteriaInterface`, has only one `apply` method.

`Adjustable` add **Three** methods to your repository.


##### rememberCriteria(CriteriaInterface $criteria)


Remember this `Criteria`, so it would be applied before all methods, this will change the cache key too.


##### forgetCriterias()


Remove all `Criteria` that repository remembered.


##### getCriterias()


Returns all `Criteria` that repository remembered.


#### Metadata

Priority **30**.

`Housekeeper` returns `Eloquent` object or `Collection` object by default, if you like `Array` a lot, use `Metadata` trait, it will converting all result to `Array` (If the result can't be converted, nothing will happen).


## Development Logs

### DEV-MASTER


### v0.9.16 - 2015/11/03

[Bug fix] Fixed an issue that causing crush when hash closure in `Cacheable` trait.

### v0.9.15 - 2015/10/21

[Bug fix] Execute `reset flow` when `before flow` has return value.

### v0.9.14 - 2015/08/30

Add conditions for `startWithTrashed` and `startWithTrashedOnly`.

### v0.9.13 - 2015/08/30

[Bug fix] `delete` in `BaseRepository` now fetch model directly from database instead of through `find` method of `BaseRepository`;

Add `startWithTrashed` and `startWithTrashedOnly` methods to `BaseRepository` for interacting model that with `softDeletes` trait;

Fix a test.

### v0.9.12 - 2015/08/30

[Bug fix] The `Cacheable` trait now takes `$this` of `Closure` coditions in cache key calculation, thanks to [@DarKDinDoN](https://github.com/AaronJan/Housekeeper/issues/1#issuecomment-135993974).

### v0.9.11 - 2015/08/29

[Bug fix] Thanks to [@DarKDinDoN](https://github.com/AaronJan/Housekeeper/issues/1#issuecomment-135114137), now `Cacheable` could works with `Closure` condition.

### v0.9.10 - 2015/08/17

[Bug fix] When pass a closure to `applyWhere`, this entry doesn't need a `key` now.

### v0.9.9 - 2015/08/12

[Bug fix] clear cache when deleting.

### v0.9.8 - 2015/08/12

Modified `Adjustable` trait, make it **fluently**, optimize docs.

### v0.9.7 - 2015/08/11

If an exception been throwed, "reset" will still be called.

### v0.9.6 - 2015/07/31

(fixed bug) return `$this` in `applyOrder`;

optimized `PHP Doc`;

### v0.9.5 - 2015/07/30

return `$this` in `applyWhere`, `applyOrder` and `with`, make them **fluent**;

### v0.9.4 - 2015/07/16

Fix bug, add more tests, now code coverage on `Eloquent\BaseRepository` is `85.96%`;

### v0.9.3 - 2015/07/14

Enhancing `applyWhere`, now takes `orWhere` operation;

There're a lot of people who think `Criteria` should take `Model` as parameter, I gave that some serious thought about that, but pass `Model` to `Criteria` that's too much, you should make a method to do such complex thing.

### v0.9.2 - 2015/07/13

Added an "applyOrder" method and more docs, got some ideas but been very busy lately, will implating them a couple days later.

### v0.9.1 - 2015/07/07

Documents are mostly completed, package is usable, still needs more tests.


## Issue


If you have any question for `Housekeeper`, feel free to create an issue, I'll reply you ASAP.

Any useful pull request are welcomed too.


## Lisence


Licensed under the [APACHE LISENCE V2](http://www.apache.org/licenses/LICENSE-2.0)


## Credits


Thanks to [prettus/l5-repository](https://github.com/prettus/l5-repository) for inspiring.

Thanks to [egofang](https://github.com/egofang) for this awesome logo!

