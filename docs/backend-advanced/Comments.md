First of all, comments are not implemented by system core, but might be provided by some modules with `comments` functionality.

However, since it is crucial feature for many applications, how it should work is specified here in order to maintain cross-compatible modules.

[Frontend integration can be found here](/docs/frontend-advanced/Comments.md)

On server side any module that supports comments should confirm comments posting by subscribing to 3 events and can force comments deletion by firing another event.

Examples (Blogs module):
```php
<?php
\cs\Event::instance()->on(
    'api/Comments/add',
    function ($data) {
        $User    = \cs\User::instance();
        if (
            $data['module'] == 'Blogs' &&
            \cs\Config::instance()->module('Blogs')->enable_comments &&
            $User->user() &&
            \cs\modules\Blogs\Posts::instance()->get($data['item'])
        ) {
            $data['allow'] = true;
            return false;
        }
    }
);
```
This code checks if someone is trying to post comment for an existing article in Blogs module, whether comments are enabled in Blogs module and whether user is registered.
If everything is fine it sets `allow = true` and stops further calls to event handlers.

```php
<?php
\cs\Event::instance()->on(
    'api/Comments/edit',
    function ($data) {
        $User    = \cs\User::instance();
        if (
            $data['module'] == 'Blogs' &&
            \cs\Config::instance()->module('Blogs')->enable_comments &&
            $User->user() &&
            ($data['user'] == $User->id || $User->admin())
        ) {
            $data['allow'] = true;
            return false;
        }
    }
);
```
This code is similar to comments posting, but also checks whether user is either an author of original comment or is an administrator, otherwise user is not allowed to edit comment.

```php
<?php
\cs\Event::instance()->on(
    'api/Comments/delete',
    function ($data) {
        $User    = \cs\User::instance();
        if (
            $data['module'] == 'Blogs' &&
            \cs\Config::instance()->module('Blogs')->enable_comments &&
            $User->user() &&
            ($data['user'] == $User->id || $User->admin())
        ) {
            $data['allow'] = true;
            return false;
        }
    }
);
```
This code is similar to comments editing, but for comments deletion.

```php
\cs\Event::instance()->on(
    'api/Comments/deleted',
    [
        'module' => 'Blogs',
        'item'   => $id
    ]
);
```
This code removes all of the comments associated with an article on its deletion.

##### api/Comments/add
Event is fired when user tries to post comment, event handler should set `allow = true` when user is allowed to post comment.
```
[
    'item'   => item
    'module' => module
    'allow'  => &$allow
]
```

* `item` - Item id
* `module` - Module name
* `allow` - Whether to allow or not

##### api/Comments/edit
Event is fired when user tries to edit comment, event handler should set `allow = true` when user is allowed to edit comment.
```
[
    'id'     => id
    'user'   => user_id
    'item'   => item
    'module' => module
    'allow'  => &$allow
]
```

* `id` - Comment it
* `user` - User id (comment author)
* `item` - Item id
* `module` - Module name
* `allow` - Whether to allow or not

##### api/Comments/delete
Event is fired when user tries to delete comment, event handler should set `allow = true` when user is allowed to delete comment.
```
[
    'id'     => id
    'user'   => user_id
    'item'   => item
    'module' => module
    'allow'  => &$allow
]
```

* `id` - Comment it
* `user` - User id (comment author)
* `item` - Item id
* `module` - Module name
* `allow` - Whether to allow or not

#### Comments/deleted
Event should be fired when module item is removed and comments associated with it should be cleaned.
```
[
    'item'   => item
    'module' => module
]
```

* `item` - Item id
* `module` - Module name
