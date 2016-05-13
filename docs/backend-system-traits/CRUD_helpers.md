`\cs\CRUD_helpers` - special system trait, it extends CRUD trait with other useful methods using existing data model

### [Methods](#methods) [Example](#example)

<a name="methods" />
###[Up](#) Methods

CRUD_helper currently defines following single major method as `protected`:

* search()

#### search($search_parameters = [] : mixed[], $page = 1 : int, $count = 100 : int, $order_by = 'id' : string, $asc = false : bool) : false|int|int[]|string[]
Generic search, all specified parameters will be connected with `AND` condition, basically, this is more like filter than real search, but anyway.

<a name="example" />
###[Up](#) Examples

Basic:
```php
<?php
...
$this->search([
    'argument'     => 'Value',              // Exact equality
    'argument2'    => ['Value1', 'Value2'], // Any of specified, exact equality
    'argument2'    => [                     // Range search, margins are included in range
        'from' => 2,                        // more or equal, optional
        'to'   => 5                         // less or equal, optional
    ],
    'joined_table' => [                     // Joined tables also supported, syntax inside the same as here
        'argument' => 'Yes'
    ]
]);
...
```

Sorting by `value` column in joined table `joined_table`:
```php
<?php
...
$this->search([], 1, PHP_INT_MAX, 'joined_table:value');
...
```

Sorting by id column (whatever name it have) in joined table `joined_table`:
```php
<?php
...
$this->search([], 1, PHP_INT_MAX, 'joined_table');
...
```

In multilingual table search for language that is different that current (will return items where language field `lang`, specified in data model as `language_field` is `English` or empty string):
```php
<?php
...
$this->search([
    'lang' => 'English'
]);
...
```

Similarly, multilingual field is supported in joined tables:
```php
<?php
...
$this->search([
    'tags' => [
        'lang' => 'English'
    ]
]);
...
```
