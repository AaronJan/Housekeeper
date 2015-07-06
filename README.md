# Housekeeper - Laravel


## Introduction


Powerful, simple `Repository-Pattern` implementation for Laravel `(>= 5.0)`, and it come with tests.


## Sections

- [About Repository-Pattern](#repository-pattern)
- [About Housekeeper](#about-housekeeper)
	- [Housekeeper Features](#housekeeper-features)
	- [What's the Differents And How Housekeeper Works](#whats-the-differents and-how-housekeeper-works)
- [Installation(TODO)](#installation)
- [Usage(TODO)](#usage)
- [Credits](#credits)


## Repository-Pattern

There are many articles about **How to implement `Repository-Pattern` in `Laravel`**, and they're a little different from one another. but in general, the idea is you only ask data from your `repository`. 

If you want to caching result, you can `extend` your `basic repository`, override methods, add some caching logic to them, or using `Decorator-Pattern` to do the same, by utilizing `IOC` in `Laravel`, you can switch them easily, usage still be the same.


## About Housekeeper


I searched `GitHub` for `Repository-Pattern` `PHP` packages, there are some, but most seems incomplete or not catching up with `Laravel 5`, but there is one stood up, it's [prettus/l5-repository](https://github.com/prettus/l5-repository).

After reading the documents and codes, I'm start to wondering: *maybe `Repository-Pattern` could be simpler*? And `prettus/l5-repository` is too much for me, `RequestCriteria`, `Validation`... They are great features, but what if I don't want them in my repository? Can I just import them in on my demond? So I wrote `Housekeeper`. 

I started from the code of `prettus/l5-repository` in `2015/06/25`, but they're completely different now.


### Housekeeper Features


* Repository console generators
* Provides basic repository method: `create`, `find`, `update`, `delete`, `paginate`, `all`, `findByField`, `findWhere`, `with`, only the basics
* `Cache`, `Criteria` as class traits
* Easy to extending
* With tests


### What's the Differents And How Housekeeper Works


Traditional `Repository-Pattern` is all about `layer`, want to caching data, you add a layer! That's great concept, you can still do that in `Housekeeper`. But features like `cache`, `criteria` I used something like `middleware`.

Each repository method calling in `Housekeeper` is called `Action`, it go through four `Flows`: *Before*, *Your method logic*, *Reset* and *After*, result could be returned in *Before* or *After* `Flows`. You can write `Injections` to extending any `Flow`.

#### Example

Let's talk about an example, like `Cache`.

	Check if cache exists, if it's, then return the cached result, if it's not, do the logic then caching result.

That's `Cache` right? if you do the traditional way, you will end up with a lot of duplicated code.

But with `Injection`, you just put the `cache-checking` in a *Before* injection, and put `cache-setting` in an *After* injection. You can identify `read`/`create`/`update`/`delete` `Actions` in the `Flow` (even get the name of method), so you write `cache-cleanning` logic in an *After* injection specify for `create`/`update`/`delete` `Action`. In the end, write a `Trait` with just one method `setupCache` like this:

```php
trait Cache
{
	protected function setupCache()
	{
		$this->inject(new GetCacheIfExistsBefore($this->app)); // Your before injection
		$this->inject(new CacheResultAfter($this->app)); // Your after injection
	}
}
```

That's all done.

Each `Injection` has a `priority` method, so if you want to add some logic before `Cache`, just return a integer that lower then `Cache`'s `priority`.


#### Setup


In repository's constructor, it will calling every method that name started with *setup*, you can inject your `Injections` in there.


## Installation


TODO


## Usage


TODO


## Lisence


TODO


## Credits


Thinks to [prettus/l5-repository](https://github.com/prettus/l5-repository) for inspired me.

