*$Key* - is system object, that provides functionality of keys generation. Keys are used for security purposes, have expiration date, and may be accompanied with some additional information. Also, keys may be stored in different databases (for different purposes), instance can be obtained in such way:
```php
<?php
$Key = \cs\Key::instance();
```

### [Methods](#methods)

<a name="methods" />
###[Up](#) Methods

*$Key* object has next public methods:
* add()
* get()
* del()
* generate()

#### add($database : int|\cs\DB\_Abstract, $key : bool|string, $data = null : null|mixed, $expire = 0 : int) : false|string
Adding key into specified database

#### get($database : int|\cs\DB\_Abstract, $key : string, $get_data = false : bool) : false|mixed
Check key existence and/or getting of data stored with key. After this key will be deleted automatically.

#### del($database : int|\cs\DB\_Abstract, $key : string) : bool
Key deletion from database

#### generate($database : int|\cs\DB\_Abstract) : string
Generates guaranteed unique key. Usually is not used, because key may become non-unique before actual usage, and it may be generated during key addition operation.
