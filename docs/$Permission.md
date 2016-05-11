`$Permission` - is system object, that provides permissions manipulating interface, instance can be obtained in such way:
```php
<?php
$Permission	= \cs\Permission::instance();
```

### [Methods](#methods) [\cs\Permission\All trait](#all-trait)

<a name="methods" />
###[Up](#) Methods

`$Permission` object has next public methods:
* add()
* get()
* set()
* del()
* get_all()

#### add($group : string, $label : string) : false|int
Add permission

#### get($id = null : int|null, $group = null : null|string, $label = null : null|string, $condition = 'and' : string) : array|false
Get permission data

#### set($id : int, $group : string, $label : string) : bool
Set permission

#### del($id : int|int[]) : bool
Deletion of permission or array of permissions

#### get_all() : array
Returns array of all permissions grouped by permissions groups

<a name="all-trait" />
###[Up](#) \cs\Permission\All

This trait has several protected methods and is used by `cs\Group` and `cs\User` classes. Usually there is no need to use it somewhere else.
