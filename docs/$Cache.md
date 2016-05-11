`$Cache` - is system object, that responses for system cache, instance can be obtained in such way:
```php
<?php
$Cache	= \cs\Cache::instance();
```

This object allow to get store/get/delete any number, string or array value in cache. Data may be lost or erased any time, so this is not permanent storage for data, it is used to store some frequently used data for fasted access.

### [Methods](#methods) [Engines](#engines) [Examples](#examples) [\cs\Cache\\_Abstract class](#abstract-class) [\cs\Cache\\_Abstract_with_namespace class](#abstract-with-namespace-class) [\cs\Cache\Prefix class](#prefix-class)

<a name="methods" />
###[Up](#) Methods

`$Cache` object has next public methods:
* init()
* cache_state()
* disable()
* get()
* set()
* del()
* clean()

#### init()
Function for initialization, is used by system.

#### cache_state() : bool
Method returns boolean value, `true` if cache enabled, and `false` otherwise.

#### disable()
Disable cache for current session.

#### get($item : string, $closure = null : Closure|null) : false|mixed
Get value of specified item from cache. Returns stored value, or boolean `false` otherwise.

Also there is simplified way to get item - to get it as property of object. (see example)

If item not found and $closure parameter specified - closure must return value for item. This value will be set for current item, and returned.

This allows to replace construction like:

```php
<?php
function foo () {
	$Cache	= Cache::instance();
	if (($result = $Cache->long_item) === false) {
		...
		$Cache->long_item	= $result;
	}
	return $result;
}
```

by

```php
<?php
function foo () {
	return Cache::instance()->get('long_item', function () {
		...
		return $result;
	}
}
```

#### set($item : string, $data : mixed) : bool
Stores some data in cache, like get() also works with items as properties (see example).

#### del($item : string) : bool
Deletes specified item from cache if it exists, like get() also works with items as properties (see example)

#### clean() : bool
Is used to clean all system cache.

All items names looks like paths in file system, and are processed correspondingly. This means, that you can store such items:
* Blog/posts/2
* Blog/posts/3
* Blog/posts/4

Also, if you will try to delete item, that may be considered as directory, for example:
* Blog/posts
* Blog

This will delete directory, and all subdirectories with items inside.

<a name="engines" />
###[Up](#) Engines

Cache system is based on engines. They may be different, but provides the same external interface.

As for now, there are 3 built-in cache engines:
* APC
* BlackHole
* FileSystem
* Memcached

First uses APC (Alternative PHP Cache) as storage for cache values, actually it uses RAM. Memcached uses memcached as backend. The last uses file system. BlackHole engine works as it is named, namely does nothing, it may be used for debugging purposes, when there are no need to save any data in cache.

FileSystem is fastest and most stable cache engine, and it is recommended for production usage as for now.

<a name="examples" />
###[Up](#) Examples

```php
<?php
$Cache			= \cs\Cache::instance();
//Setting
$Cache->set('dir/item1', 1);
$Cache->{'dir/item2'}	= 2;
//Getting
$one			= $Cache->get('dir/item1');
$two			= $Cache->{'dir/item2'};
//Deleting
$Cache->del('dir/item1');
unset($Cache->dir);
```

<a name="abstract-class" />
###[Up](#) \cs\Cache\\_Abstract class

This is an abstract class is used as base for cache engine classes. It is described here because it shows major methods of cache abstraction.

This class has next public methods:
* false|mixed get($item : string)
* bool set($item : string, $data : mixed)
* bool del($item : string)
* bool clean()

#### get($item : string) : false|mixed
Get item from cache

#### set($item : string, $data : mixed) : bool
Put or change data of cache item

#### del($item : string) : bool
Delete item from cache

#### clean() : bool
Clean cache by deleting all items

<a name="abstract-with-namespace-class" />
###[Up](#) \cs\Cache\\_Abstract_with_namespace class

This is an abstract class that extends `\cs\Cache\_Abstract`. It exists to provide simplified integration of cache engines without native namespaces support (required by CleverStyle CMS), but which have incrementing support (currently APC and Memcached engines are using this class).

Class implements methods `get()`, `set()`, `del()` and `clean()` from ``\cs\Cache\_Abstract`` and requires to implement following methods instead:
* available_internal()
* get_internal()
* set_internal()
* del_internal()
* increment_internal()
* clean_internal()

Main benefit is that these methods in most cases are trivial proxy calls to respecting engine-specific methods without any additional checks, which makes engine implementation extremely simple.

#### available_internal() : bool
Whether current cache engine is available (might be `false` if necessary extension is not installed or something similar)

#### get_internal($item : string) : bool|mixed
Get item by key

#### set_internal($item : string, $data : mixed) : bool
Set item by key with value that can be anything that can be encoded as JSON

#### del_internal($item : string) : bool
Delete item by key

#### increment_internal($item : string) : bool
Increment item by key

#### clean_internal() : bool
Clean the whole cache

<a name="prefix-class" />
###[Up](#) \cs\Cache\Prefix class

This class is used for simplified work with cache, when using common prefix.
It includes methods for getting/setting/deletion of cache items just like `cs\Cache` class, but automatically adds prefix specified in constructor to every item.
