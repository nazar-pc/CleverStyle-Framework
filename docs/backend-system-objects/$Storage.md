*$Storage* - is system object, that provides abstraction for storages, instance can be obtained in such way:
```php
<?php
$Storage = \cs\Storage::instance();
```

### [Methods](#methods) [\cs\Storage\\_Abstract class](#abstract-class)

<a name="methods" />
###[Up](#) Methods

*$Storage* object has next public methods:
* storage()
* get_connections_list()

Also if there is only one configured storage it is possible to call methods of \cs\Storage\\_Abstract class directly from this object:
```php
<?php
$Storage = \cs\Storage::instance();
$content = $Storage->storage(0)->file_get_content('some_file');
```

#### storage($connection : int) : cs\\Storage\\_Abstract|False_class
Method returns instance of class for storage abstraction.

#### get_connections_list($status = null : bool|null|string) : array|null
Is used for getting of successful and failed connections.

<a name="abstract-class" />
###[Up](#) \cs\Storage\\_Abstract class

This is abstract class is used as base for storage engine classes. It is described here because it shows major methods of storage abstraction.

This class has next public methods:
* connected()
* base_url()
* get_files_list()
* file()
* file_get_contents()
* file_put_contents()
* copy()
* unlink()
* file_exists()
* move_uploaded_file()
* rename()
* mkdir()
* rmdir()
* is_file()
* is_dir()
* url_by_source()
* source_by_url()

#### connected() : bool
Connection state

#### base_url() : string
Base URL for files in this storage


All other methods works completely similar to system functions, but pay attention, paths to files should be relative (not absolute).
