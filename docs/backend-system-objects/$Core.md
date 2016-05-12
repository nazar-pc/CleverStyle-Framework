`$Core` - is system object, that responses for some core functionality. This is first system object, which is created, instance can be obtained in such way:
```php
<?php
$Core	= \cs\Core::instance();
```

Object provides loading of base system configuration.

### [Methods](#methods) [Properties](#properties)

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

<a name="properties" />
###[Up](#) Properties

`$Core` object has next public properties (which are not public, but instead available through magic methods as read-only):
* domain
* timezone
* db_host
* db_type
* db_name
* db_user
* db_password
* db_prefix
* db_charset
* storage_type
* storage_url
* storage_host
* storage_user
* storage_password
* language
* cache_engine
* memcache_host
* memcache_port
* public_key

All properties are the same as specified in `config/main.json`, so if you add more keys there, they'll be all available as read-only properties on `$Core`.
