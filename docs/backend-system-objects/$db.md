`$db` - is system object, that provides abstraction for databases, instance can be obtained in such way:
```php
<?php
$db    = \cs\DB::instance();
```

### [Methods](#methods) [Properties](#properties) [\cs\DB\\_Abstract class](#abstract-class) [\cs\DB\Accessor class](#accessor-trait)

<a name="methods" />
###[Up](#) Methods

`$db` object has next public methods:
* db()
* db_prime()
* get_connections_list()

#### db($database_id : int) : cs\\DB\\_Abstract|False_class
Method returns instance of class for database abstraction. This object guaranteed will have read access to database.

#### db_prime($database_id : int) : cs\\DB\\_Abstract|False_class
Similar to `db()`, but guaranteed will have write access to database. These two methods were separated in order to balance load when database replication is used.

#### get_connections_list($status : int) : array
Is used for getting of master, mirror, successful and failed connections.

<a name="properties" />
###[Up](#) Properties

`$db` object has next public properties:

* queries
* time

#### queries
Total number of queries performed to all databases.

#### time
Time taken to perform all queries to all databases.

<a name="abstract-class" />
###[Up](#) \cs\DB\\_Abstract class

This is abstract class is used as base for database engine classes. It is described here because it shows major methods of database abstraction.

This class has next public methods:
* q()
* f()
* qf()
* qfa()
* qfs()
* qfas()
* insert()
* id()
* affected()
* transaction()
* free()
* columns()
* tables()
* s()
* server()
* db_type()
* database()
* queries()
* query()
* time()
* connecting_time()

#### q($query : string|string[], ...$params : array) : bool|object|resource
Query method. `$query` may be SQL string or array of strings. Strings may be formatted according to [sprintf()](http://www.php.net/manual/en/function.sprintf.php) PHP function or may contain markers for prepared statements (but not both at the same time).

There might be arbitrary number of parameters for formatting SQL statement or for using in prepared statements.
If an array provided as second argument - its items will be used, so that you can either specify parameters as an array, or in line.

For example:
```php
<?php
$db    = \cs\DB::instance();
$query = $db->db(0)->q(
    "SELECT `id`
    FROM `[prefix]users`
    WHERE `login`    = '%s'
    LIMIT %d",
    $login,
    1
);
```
Every parameter, passed in such way will have escaped special characters for use in an SQL statement.

Or the same example with prepared statements:
```php
<?php
$db    = \cs\DB::instance();
$query = $db->db(0)->q(
    "SELECT `id`
    FROM `[prefix]users`
    WHERE `login`    = ?
    LIMIT ?",
    $login,
    1
);
```

And the same example with array of parameters:
```php
<?php
$db    = \cs\DB::instance();
$query = $db->db(0)->q(
    "SELECT `id`
    FROM `[prefix]users`
    WHERE `login`    = ?
    LIMIT ?",
    [
        $login,
        1
    ]
);
```

You can even provide more arguments for prepared statements than needed (which is especially useful when dealing with arrays of queries):
```php
<?php
$db    = \cs\DB::instance();
$query = $db->db_prime(0)->q(
    [
        "DELETE FROM FROM `[prefix]articles` WHERE `id` = ?",
        "DELETE FROM FROM `[prefix]articles_comments` WHERE `article` = ? OR `date` < ?",
        "DELETE FROM FROM `[prefix]articles_tags` WHERE `article` = ?"
    ],
    [
        $article_to_delete,
        time() - 24 * 3600
    ]
);
```

#### f($query_result : object|resource, $single_column = false : bool, $array = false : bool, $indexed = false : bool) : array[]|false|int|int[]|string|string[]
Fetch a result row


#### qf (...$query : string[]) : array|false
Query, Fetch

Short for `::f(::q())`, arguments are exactly the same as in `::q()`

For example:
```php
<?php
$db    = \cs\DB::instance();
$query = $db->db(0)->qf(
    "SELECT `id`
    FROM `[prefix]users`
    WHERE `login`    = '%s'
    LIMIT %d",
    $login,
    1
);
```

#### qfa (...$query : string[]) : array[]|false
Query, Fetch, Array

Short for `::f(::q(), false, true)`, arguments are exactly the same as in `::q()`

#### qfs (...$query : string[]) : false|int|string
Query, Fetch, Single

Short for `::f(::q(), true)`, arguments are exactly the same as in `::q()`

#### qfas (...$query : string[]) : false|int[]|string[]
Query, Fetch, Array, Single

Short for `::f(::q(), true, true)`, arguments are exactly the same as in `::q()`

#### id() : int
Returns id of last inserted row

#### insert($query : string, $params : array|array[], $join = true : bool) : bool
Method for simplified inserting of several rows

For example:
```php
<?php
$db     = \cs\DB::instance();
$result = $db->db_prime(0)->insert(
    "INSERT INTO `[prefix]table`
    (
        `id`,
        `value`
    ) VALUES (
        '%s',
        '%s'
    )",
    [
        [
            1,
            'Value 1'
        ],
        [
            2,
            'Value 2'
        ],
        [
            3,
            'Value 3'
        ]
    ]
);
```

#### affected() : int
Returns number of row affected by last query

#### transaction($callback : callable) : bool
Execute transaction

All queries done inside callback will be within single transaction, throwing any exception or returning boolean `false` from callback will cause
rollback. Nested transaction calls will be wrapped into single big outer transaction, so you might call it safely if needed.

#### free($query_result : object|resource)
Free result memory

#### columns($table : string, $like = false : false|string) : string[]
Get columns list of table

#### tables($like = false : false|string) : string[]
Get tables list

#### s($string : string|string[], $single_quotes_around = true : bool) : string|string[]
Preparing string for using in SQL query. SQL Injection Protection.
Is used automatically with query string formatting.

#### server() : string
Get information about server

#### connected() : bool
Connection state

#### db_type() : string
Database type (lowercase, for example *mysql*)

#### database() : string
Database name

#### queries() : array
Queries array, has 3 properties:
* num - total number of performed queries
* time - array with time of each query execution
* text - array with text text of each query

#### query() : array
Last query information, has 2 properties:
* time - execution time
* text - query text

#### time() : float
Total working time (including connection, queries execution and other delays)

#### connecting_time() : float
Connecting time

<a name="accessor-trait" />
###[Up](#) \cs\DB\Accessor trait

This is trait, that is used by other classes in order to simplify work with database.

This class has next public methods:
* db()
* db_prime()

And one protected abstract method:
* cdb()

#### db() : DB\_Abstract|False_class
Similar to `$db->db()`, uses database id returned by `::cdb()` method 

#### db_prime() : DB\_Abstract|False_class
Similar to `$db->db_prime()`, uses database id returned by `::cdb()` method 

#### protected int cdb() : abstract
Current database id. Method return integer index of database in system configuration.
