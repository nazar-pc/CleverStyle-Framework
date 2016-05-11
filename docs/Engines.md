Engines are of several types:
* Cache
* DB
* Storage

They are created to provide simple unified interfaces for working with different Cache/DataBase/Storage types.

For example if you want to get some item from cache you can just write:
```php
<?php
$Cache	= \cs\Cache::instance();
$item	= $Cache->item;
```
and it doesn't matter what type of cache engine is used.

Every engine directory contain files with classes (files names are the same as classes names). Each class inherits `_Abstract` class of the same directory. `_Abstract` class is, obviously, abstract and provides interface for its engine type. Some methods have realization, but may be redefined, other methods are abstract and must be defined in inherited classes in order to provide unified interface.

All engines are located in directory **core/engines**
