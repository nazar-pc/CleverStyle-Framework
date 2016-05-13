`$Session` - is system object, that is responsible for current user session, instance can be obtained in such way:
```php
<?php
$Session = \cs\Session::instance();
```

### [Methods](#methods) [Events](#events)

<a name="methods" />
###[Up](#) Methods

`$Session` object has next public methods:
* admin()
* user()
* guest()
* get_id()
* get_user()
* get()
* load()
* add()
* del()
* del_all_sessions()
* get_data()
* set_data()
* del_data()
* is_session_owner()

#### admin() : bool
Is admin

#### user() : bool
Is user

#### guest() : bool
Is guest

#### get_id() : false|int
Returns id of current session

#### get_user() : false|int
Returns user id of current session

#### get($session_id : false|null|string) : false|array
Returns session details by session id

#### load($session_id = null : false|null|string) : int
Load session by id and return id of session owner (user), updates last_sign_in, last_ip and last_online information

#### add($user = false : false|int, $delete_current_session = true : bool) : false|string
Create the session for the user with specified id

#### del($session_id = null : null|string) : bool
Destroying of the session

#### del_all_sessions($user = false : false|int) : bool
Deletion of all user sessions

#### get_data($item : string, $session_id = null : null|string) : false|mixed
Get data, stored with session

#### set_data($item : string, $value : mixed, $session_id = null : null|string) : bool
Store data with session

#### del_data($item : string, $session_id = null : null|string) : bool
Delete data, stored with session

#### is_session_owner($session_id : string, $user_agent : string, $remote_addr : string, $ip : string) : bool

<a name="events" />
###[Up](#) Events

`$Session` object supports next events:
* System/Session/init/before
* System/Session/init/before
* System/Session/load
* System/Session/add
* System/Session/del_session/before
* System/Session/del_session/after
* System/Session/del_all_sessions

#### System/Session/init/before
Is fired before initialization of user session (at object creation)

#### System/Session/init/after
Is fired after initialization of user session (at object creation)

#### System/Session/load
Event is fired during session loading
```
[
    'session_data' => $session_data
]
```

#### System/Session/add
Event is fired during session addition
```
[
    'session_data' => $session_data
]
```

#### System/Session/del/before
Event is fired before session deletion
```
[
    'id'    => session_id
]
```

#### System/Session/del/after
Event is fired after session deletion
```
[
    'id'    => session_id
]
```

#### System/Session/del_all
Event is fired before deleting of all sessions. Parameters array:
```
[
    'id'    => user_id
]
```
