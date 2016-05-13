Database level contains no ORM or ODM. Plain SQL queries usage is encouraged, but there are some wrappers to usual functions to make queries more convenient and easier manipulate with query results.

### General
It is recommended to enclose tables and fields names in `` ` `` for consistency.

Also use `[prefix]` before table names, it will be replaced with system prefix that is used throughout all system tables and base components.

### Prepared statements
First thing you need to know - how to use prepared statements, because this is the first thing you should think about if you want to make it secure.

There are few ways to use them:
```php
<?php
\cs\DB::instance()->q(
    "DELETE FROM `[prefix]table`
    WHERE
        `param`    = '%s' AND
        `id`    > %d
    LIMIT 1",
    'param value',
    10
);
```

Method `::q()` just executes the query. First argument is query string followed by one, few or array of statements that should be substituted into query string.

```php
<?php
\cs\DB::instance()->q(
    "DELETE FROM `[prefix]table`
    WHERE
        `param`    = '%s' AND
        `id`    > %d
    LIMIT 1",
    [
        'param value',
        10
    ]
);
```

Query string itself may be an array too.

```php
<?php
\cs\DB::instance()->q(
    [
        "DELETE FROM `[prefix]table`
        WHERE `id` = %d",
        "DELETE FROM `[prefix]other_table`
        WHERE `id` = %d"
    ],
    10
);
```

The syntax of query string is similar to [sprintf()](https://php.net/manual/en/function.sprintf.php), so you can use any specifiers from there in query string.
Any statement you specify in any way mentioned before will be secured before substitution in order to avoid SQL injections.

Actually, you can combine whole SQL query manually, but this is not recommended, and should be only limited to substituting alphanumeric symbols,
anything else should be used with prepared statements.

### Simplified manipulation with query results
There are few vary useful methods that simplify working with query results.

Normally, you need to make query, then fetch result or make it few times to get all rows. But instead you can combine few operations together:

```php
<?php
$db     = \cs\DB::instance();
$result = $db->qf([
    "SELECT `id`, `name`
    FROM `[prefix]table`
    WHERE `id` = 10
    LIMIT 1,
    10
]);
var_dump($result); // ['id' => '10', 'name' => 'Some name']
```

`::qf()` is a combination Query + Fetch at once. First argument is either query string or array where all elements are just like arguments of `::q()` method.
There are more of such methods:
* `::fqs()` - Query + Fetch + Single - if you query only one row with only one column - you can get that value directly
* `::qfa()` - Query + Fetch + Array - array of all queried rows together
* `::qfas()` - if you query few rows with only one column in each - you can get array with just that values directly

Use them where applicable, methods names are short but very easy to remember.

### Read only databases and write databases
In order to simplify all the things we used `\cs\DB::instance()->*` methods, which actually works, but is limited to cases when there is only one database and doesn't have necessary context for system.

Database may have mirrors, mirrors may be used for read only access, while master database is used for writes.

To get instance of specific database in read only mode call:

```php
<?php
$db_index = 0;
$db       = \cs\DB::instance();
$cdb      = $db->$db_index;
$cdb->q(...);
```

To get instance in write mode you need to call method instead of getting property:

```php
<?php
$db_index = 0;
$db       = \cs\DB::instance();
$cdb      = $db->$db_index();
$cdb->q(...);
```

While it is likely that most of times you'll deal with single database it doesn't make things very complex to separate different queries by default.

Also, if you have one write query and one read query in the same block of code - you can make both operations on database with write mode in order to open less connections.
