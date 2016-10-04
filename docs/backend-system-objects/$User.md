`$User` - is system object, that provides users manipulating interface, instance can be obtained in such way:
```php
<?php
$User = \cs\User::instance();
```

### [Methods](#methods) [Properties](#properties) [Constants](#constants) [Events](#events) [\cs\User\Properties class](#properties-class)

<a name="methods" />
###[Up](#) Methods

`$User` object has next public methods:
* get()
* set()
* get_data()
* set_data()
* del_data()
* admin()
* user()
* guest()
* get_id()
* avatar()
* username()
* search_users()
* get_permission()
* set_permission()
* del_permission()
* get_permissions()
* set_permissions()
* del_permissions_all()
* add_groups()
* get_groups()
* set_groups()
* del_groups()
* registration()
* registration_confirmation()
* registration_cancel()
* set_password()
* validate_password()
* restore_password()
* restore_password_confirmation()
* del_user()
* get_users_columns()
* dnt()
* disable_memory_cache()

#### get($item : string|string[], $user = false : false|int) : false|int|mixed[]|string|cs\User\Properties
Get data item of specified user. If `$item` is integer - instance of `cs\User\Properties` will be returned.
List of possible item may be found in [properties section](#properties).

Also there is simplified way to get item - to get it as property of object:
```php
<?php
$User  = \cs\User::instance();
$login = $User->login;
```

#### set($item : array|string, $value = null : int|null|string, $user = false : false|int) : bool
Set data item of specified user, like get() also works with items as properties.
List of possible items may be found in [properties section](#properties) (except `login_hash` and `email_hash` - they changes automatically with `login` and `email` respectively).
```php
<?php
$User        = \cs\User::instance();
$User->about = 'Some about info';
```

#### get_data($item : string|string[], $user = false : false|int) : false|string|mixed[]
Getting additional data item(s) of specified user

#### set_data($item : array|string, $value = null : mixed|null, $user = false : false|int) : bool
Setting additional data item(s) of specified user

#### del_data($item : string|string[], $user = false : false|int) : bool
Deletion of additional data item(s) of specified user

#### admin() : bool
Is admin

#### user() : bool
Is user

#### guest() : bool
Is guest

#### get_id($login_hash : string) : false|int
Get user id by login or email hash (sha224) (hash from lowercase string)

#### avatar($size = null : int|null, $user = false : false|int) : string
Get user avatar, if no one present - uses Gravatar

#### username($user = false : false|int) : string
Get user name or login or email, depending on existing information

#### search_users($search_phrase : string) : false|int[]
Search keyword in login, username and email

#### get_permission($group : string, $label : string, $user = false : false|int) : bool
Get permission state for specified user.

Group - usually module name, or module name plus some section. Label - usually page route (without module), id of some item or section + id. Such structure is made for simpler managing of permissions.

#### set_permission($group : string, $label : string, $value : int, $user = false : false|int) : bool
Set permission state for specified user.

#### del_permission($group : string, $label : string, $user = false : false|int) : bool
Delete permission state for specified user.

#### get_permissions($user : false : bool|int) : int[]|false
Get array of all permissions states for specified user

#### set_permissions($data : array, $user = false : false|int) : bool
Set user's permissions according to the given array

#### del_permissions_all($user = false : false|int) : bool
Delete all user's permissions

#### add_groups($group : int|int[], $user = false : false|int) : bool
Add user's groups

#### get_groups($user = false : false|int) : false|int[]
Get user's groups

#### set_groups($data : array, $user : bool|int) : bool
Set user's groups

#### del_groups($group : int|int[], $user = false : false|int) : bool
Delete user's groups

#### registration($email : string, $confirmation = true : bool, $auto_sign_in = true : bool) : array|false|string
User registration

#### registration_confirmation($reg_key : string) : array|false
Confirmation of registration process

#### registration_cancel()
Canceling of bad/failed registration

#### set_password($new_password : string, $user = false : false|int, $already_prepared = false : bool) : bool
Proper password setting without any need to deal with low-level implementation

#### validate_password($new_password : string, $user = false : false|int, $already_prepared = false : bool) : bool
Proper password validation without any need to deal with low-level implementation

#### restore_password($user : int) : false|string
Restoring of password

#### restore_password_confirmation($key : string) : array|false
Confirmation of password restoring process

#### del_user($user : int|int[]) : bool
Delete specified user or array of users

#### get_users_columns() : array
Returns array of users columns, available for getting of data

#### dnt() : bool
Do not track checking

#### () : disable_memory_cache
Disable memory cache to decrease RAM usage when working with large number of users.

<a name="properties" />
###[Up](#) Properties

`$User` object has next public properties:
* id
* login
* login_hash
* username
* password_hash
* email
* email_hash
* language
* timezone
* reg_date
* reg_ip
* reg_key
* status
* avatar

All properties are accessed through "magic" methods. Every property have PhpDoc section, so, type and format you can see there. All properties have simple scalar values.

<a name="constants" />
###[Up](#) Constants

`User` class has several constants:
* GUEST_ID
* ROOT_ID
* ADMIN_GROUP_ID
* USER_GROUP_ID
* STATUS_ACTIVE
* STATUS_INACTIVE
* STATUS_NOT_ACTIVATED

#### GUEST_ID
Id of system guest user

#### ROOT_ID
Id of first, primary system administrator

#### ADMIN_GROUP_ID
Id of system group for administrators

#### USER_GROUP_ID
Id of system group for users

#### STATUS_ACTIVE
Status of active user

#### STATUS_INACTIVE
Status of inactive user

#### STATUS_NOT_ACTIVATED
Status of not activated user

<a name="events" />
###[Up](#) Events

`$User` object supports next events:
* System/User/construct/before
* System/User/construct/after
* System/User/registration/before
* System/User/registration/after
* System/User/registration/confirmation/before
* System/User/registration/confirmation/after
* System/User/del/before
* System/User/del/after

#### System/User/construct/before
Event is fired at the beginning of constructor

#### System/User/construct/after
Event is fired at the end of constructor

#### System/User/registration/before
Is running before registration, `return false` stops and cancels registration process. Parameters array:
```
[
    'email' => email
]
```

#### System/User/registration/after
Is running after registration, `return false` stops and cancels registration process. Parameters array:
```
[
    'id' => user_id
]
```

#### System/User/registration/confirmation/before
Is running before registration confirmation, `return false` stops and cancels registration process. Parameters array:
```
[
    'reg_key' => reg_key
]
```

#### System/User/registration/confirmation/after
Is running after registration confirmation, `return false` stops and cancels registration process. Parameters array:
```
[
    'id' => user_id
]
```

#### System/User/del/before
Is running before user deletion. Parameters array:
```
[
    'id' => user_id    //id or array of ids
]
```

#### System/User/del/after
Is running after user  deletion. Parameters array:
```
[
    'id' => user_id    //id or array of ids
]
```

<a name="properties-class" />
###[Up](#) \cs\User\Properties class

This is abstract class, that is used for simplified access for various information about particular user.

This class has next public methods:
* get()
* set()
* avatar()
* username()
* get_data()
* set_data()
* del_data()

All methods are similar to the same [methods of User class](#methods), except they do not require/support specifying of user id.

This class has next public properties:
* id
* login
* login_hash
* username
* password_hash
* email
* email_hash
* language
* timezone
* reg_date
* reg_ip
* reg_key
* status
* avatar

All properties are accessed through "magic" methods. Every property have PhpDoc section, so, type and format you can see there. All properties have simple scalar values.
