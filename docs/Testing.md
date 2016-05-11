Testing in CleverStyle CMS is done using PHPT tests. This tests are very simple to read and write, they are used in PHP development itself.

However, in your custom project you can use any other testing tool as well.

### Run testing
To run existing tests you need:
* have full copy of repository source codes (since building, installing and functioning of the system will be checked, and all files are needed)
* have database with name `cscms.travis` created in local MySQL database and user `travis` without password should have access to this database
* PHP extensions `APCU` and `memcached` together with memcached server itself should be present to successfully pass all tests

If you have all of this - you are ready to run tests:

```bash
php run-tests.php -P --show-diff tests
```

`-P` means that we'll use default PHP interpreter and `--show-diff` will print difference between expected test output and actual when test fails.

### To write tests
First of all - read [PHPT - Test File Layout](https://qa.php.net/phpt_details.php)

Also you can look at `tests` directory for already existing tests to see how they works.

When you're ready - either choose appropriate directory from existing within `tests` directory or create new one and put new `*.phpt` test file there.

This works for very simple cases. Most of times you'll encounter errors about missing constants and things like that. This is because of system coupling, but this can be overcome easily.

### Environment
Since CleverStyle CMS have high coupling level most of times you'll need some basic environment for your tests, and there is already such.

To get basic system environment (all the build-in constants, functions, classes autoloader) you just need to include `tests/custom_loader.php` file.

It looks like system loader, but differs from default in few ways:
* configures superglobals like in `GET` request to home page
* doesn't output anything at the end of execution itself
* `cs\Singleton` trait is different, it allows to stub system objects and replace them with custom ones (for testing purposes), so, this doesn't cause overhead in production

### Hack system objects
`cs\Singleton` class usually have only one usable method `::instance()`, however, custom version for testing have also methods `::instance_stub()`, `::instance_replace()` and `::instance_reset()`.
These 3 methods gives you full control over all system methods and not only them.

#### cs\Singleton::instance_stub($properties = [], $methods = [])
Accepts array of properties and methods as arguments, and returns you object just like `::instance()`, also this object will be returned if you'll call `::instance()` after this.

Returned object will contain only properties and methods you've specified.

```php
<?php
$Core = Core::instance_stub([
	'cache_engine'	=> 'APC'
]);
```

So, if object you're testing uses system objects you can stub them in such way.

#### cs\Singleton::instance_replace($object)
Works similarly to `::instance_stub()`, but accepts as argument regular object that you've created yourself.

#### cs\Singleton::instance_reset()
Resets instance that will be returned by `::instance()` on next call independently whether it was stub, replaced object or regular instance of system class.
It is usable for running few tests during one script execution.

### do_request()
This is a convenient wrapper that allows to execute request, for instance:
```php
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$_SERVER['REQUEST_URI'] = '/admin';
do_request();
echo Response::instance()->body;
```
