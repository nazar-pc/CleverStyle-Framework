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

Also if there is only one configured database it is possible to call methods of `\cs\DB\\_Abstract` class directly from this object:
```php
<?php
$db    = \cs\DB::instance();
$query    = $db->q(
    "SELECT `id`
    FROM `[prefix]users`
    LIMIT 1"
);
```
#### db($database_id : int) : cs\\DB\\_Abstract|False_class
Method returns instance of class for database abstraction. This object guaranteed will have read access to database.

Also there is simplified way to get instance - to get it as property of object:
```php
<?php
$db    = \cs\DB::instance();
$cdb    = $db->{'0'};
```
#### db_prime($database_id : int) : cs\\DB\\_Abstract|False_class
Similar to `db()`, but guaranteed will have write access to database. These two methods were separated in order to balance load on DB when database replication is used.

Also there is simplified way to get instance - to get it as result of calling of object function:
```php
<?php
$db    = \cs\DB::instance();
$cdb    = $db->{'0'}();
```
#### get_connections_list($status = null : bool|null|string) : array|null
Is used for getting of successful, failed and mirror connections.

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

#### q($query : string|string[], $params = [] : string|string[], ...$param : string[]) : false|object|resource
Query method. `$query` may be SQL string or array of strings. Strings may be formatted according to [sprintf()](http://www.php.net/manual/en/function.sprintf.php) PHP function. In this case `$params` argument may contain array of arguments for formatting of string, another way - to specify arguments as arguments of this method started from second argument.

For example:
```php
<?php
$db    = \cs\DB::instance();
$query = $db->q(
    "SELECT `id`
    FROM `[prefix]users`
    WHERE `login`    = '%s'
    LIMIT %d",
    $login,
    1
);
```
Every parameter, passed in such way will have escaped special characters for use in an SQL statement.

#### f($query_result : object|resource, $single_column = false : bool, $array = false : bool, $indexed = false : bool) : array[]|false|int|int[]|string|string[]
Fetch a result row


#### qf (...$query : string[]) : array|false
Query, Fetch

Short for `::f(::q())`, arguments are exactly the same as in `::q()`

For example:
```php
<?php
$db    = \cs\DB::instance();
$query = $db->qf(
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
$result = $db->insert(
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

#### time() : int
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
Similar to `$db->db()`

#### db_prime() : DB\_Abstract|False_class
Similar to `$db->db_prime()`

#### protected int cdb() : abstract
This is abstract method, that should be defined in classes, that use this trait. Method return integer index of database in system configuration.
