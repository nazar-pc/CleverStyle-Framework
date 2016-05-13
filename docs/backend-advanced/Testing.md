Testing in CleverStyle CMS is done using slightly modified dialect of PHPT tests. These tests are very simple to read and write, they are used in PHP development itself.

However, in your custom project you can use any other testing tool as well.

### Run testing
To run existing tests you need:
* have full copy of repository source codes (since building, installing and functioning of the system will be checked, and all files are needed)
* For MySQL: have database with name `travis` created in local MySQL database and user `travis` without password should have access to this database
* For PostgreSQL: have database with name `travis` created in local PostgreSQL database and user `postgres` without password should have access to this database
* For SQLite: nothing special needed
* PHP extensions `APCu` and `memcached` together with memcached server itself should be present to successfully pass all tests

If you have all of this - you are ready to run tests:

```bash
php -d variables_order=EGPCS phpt-tests-runner tests
```

`SKIP_SLOW_TESTS` and `DB` environment variables are used to skip slow tests and specifying which database engine to use during tests:

```bash
DB=MySQLi SKIP_SLOW_TESTS=1 php -d variables_order=EGPCS phpt-tests-runner tests
DB=PostgreSQL SKIP_SLOW_TESTS=1 php -d variables_order=EGPCS phpt-tests-runner tests
DB=SQLite SKIP_SLOW_TESTS=1 php -d variables_order=EGPCS phpt-tests-runner tests
```

### To write tests
First of all - read [PHPT - Test File Layout](https://qa.php.net/phpt_details.php) and then differences of [phpt-tests-runner dialect](https://github.com/nazar-pc/phpt-tests-runner#phpt-tests-runner---runner-for-phpt-tests-with-few-differences-comparing-to-original-phpt-format)

Also you can look at `tests` directory for already existing tests to see how they work.

When you're ready - either choose existing directory within `tests` directory or create new one and put new `*.phpt` test file there.

### Environment
Since CleverStyle CMS have high coupling level most of times you'll need some basic environment for your tests, and there is already such.

To get basic system environment (all the system functions, classes autoloader) you just need to include `tests/bootstrap.php` file.

It looks like system bootstrap, but differs from default in few ways:
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
$Core = \cs\Core::instance_stub([
    'cache_engine' => 'APC'
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
include __DIR__.'/../bootstrap.php';
$_SERVER['REQUEST_URI'] = '/admin';
do_request();
echo Response::instance()->body;
```
