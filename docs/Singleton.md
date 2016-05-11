Singleton - special system trait, that adds to other classes realization of singleton pattern.

### [Methods](#methods) [Example](#example)

<a name="methods" />
###[Up](#) Methods

Singleton defines next methods as `protected` and `final`:

* __construct
* __clone
* __wakeup

The only available public method is static method `instance`:

#### instance($check = false : bool)
If `$check == true` - then instead of object, boolean result will be returned: `true` if instance was already created, `false` otherwise.

If class need constructor - it is possible to add it by defining `protected` method `construct()` (as `__construct` is already defined as `final` in this trait).

<a name="example" />
###[Up](#) Example

```php
<?php
class My_class {
	use \cs\Singleton;

	protected function constructor () {
		$this->five	= 5;
	}
}

$object	= My_class::instance();
$object->five;			//5
```
