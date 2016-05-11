`$Core` - is system object, that responses for some core functionality. This is first system object, which is created, instance can be obtained in such way:
```php
<?php
$Core	= \cs\Core::instance();
```

Object provides loading of base system configuration.

### [Methods](#methods) [Constants](#constants)

<a name="methods" />
###[Up](#) Methods

`$Core` object has next public methods:
* get()
* set()

#### get($item : string) : false|string
Getting of base configuration parameter (which are defined in configuration file).

Also there is simplified way to get item - to get it as property of object:
```php
<?php
$Core	= \cs\Core::instance();
$Core->get('timezone');
$Core->timezone;
```

#### set($item : string, $value : string)
Setting of base configuration parameter (available only at object construction, i.e. from file *config/main.php*). Also works with items as properties:
```php
<?php
$Core			= \cs\Core::instance();
$Core->set('parameter', 'value');
$Core->parameter	= 'value';
```

<a name="constants" />
###[Up](#) Constants

At creation `$Core` object defines several global constants:
* DOMAIN

#### DOMAIN
Domain of main mirror
