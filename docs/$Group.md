`$Group` - is system object, that provides groups manipulating interface, instance can be obtained in such way:
```php
<?php
$Group	= \cs\Group::instance();
```

### [Methods](#methods) [Events](#events)

<a name="methods" />
###[Up](#) Methods

`$Group` object has next public methods:
* add()
* get()
* set()
* del()
* get_all()
* get_permissions()
* set_permissions()
* del_permissions_all()

#### add($title : string, $description : string) : false|int
Add new group

#### get($id : int|int[]) : array|array[]|false
Get group data

#### set($group : int, $title :  string, $description : string) : bool
Set group data

#### del($group : int|int[]) : bool
Delete group

#### get_all() : array|bool
Get list of all groups

#### get_permissions($group : int) : int[]|false
Get group permissions

#### set_permissions($data : array, $group : int) : bool
Set group permissions

#### del_permissions_all($group : int) : bool
Delete all permissions of specified group

<a name="events" />
###[Up](#) Events

`$Group` object supports next events:
* System/Group/Group/add
* System/Group/Group/del/before
* System/Group/Group/del/after

#### System/Group/Group/add
Is running after successful group addition. Parameters array:
```
[
	'id'	=> group_id
]
```

#### System/Group/Group/del/before
Is running before group deletion. Parameters array:
```
[
	'id'	=> group_id
]
```

#### System/Group/Group/del/after
Is running after group deletion. Parameters array:
```
[
	'id'	=> group_id
]
```
