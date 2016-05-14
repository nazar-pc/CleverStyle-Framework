`exit()` and `die()` constructions terminates execution entirely. Most likely you'll not need to call them at all, but if you do - please, throw `\cs\ExitException` instead.

```php
<?php
throw new \cs\ExitException;
```

If you need to exit with error - status code might be specified as argument:
```php
<?php
throw new \cs\ExitException(404);
```

If you also need to provide custom status text, you can do that as well:
```php
<?php
throw new \cs\ExitException('Login required', 400);
```

It is also possible to specify short and long description instead of just status text (which is sort description), especially useful for JSON responses:
```php
<?php
throw new \cs\ExitException(
    [
        'invalid_request',
        'redirect_uri parameter invalid'
    ],
    400
);
```

Last thing is forcing JSON response for non-API requests:
```php
<?php
$e = new \cs\ExitException(
    [
        'invalid_request',
        'redirect_uri parameter invalid'
    ],
    400
);
$e->setJson();
throw $e;
```


Throwing `\cs\ExitException` will allow system to handle that exit nicely, since there can be situations when multiple requests are handled during single script execution.

Following this approach will guarantee good performance and reliability of system operation.
