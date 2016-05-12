`\cs\CRUD_helpers` - special system trait, it extends CRUD trait with other useful methods using existing data model

### [Methods](#methods) [Example](#example)

<a name="methods" />
###[Up](#) Methods

CRUD_helper currently defines following single major method as `protected`:

* array|false|int search($search_parameters = [] : mixed[], $page = 1 : int, $count = 100 : int, $order_by = 'id' : string, $asc = false : bool)

#### search($search_parameters = [] : mixed[], $page = 1 : int, $count = 100 : int, $order_by = 'id' : string, $asc = false : bool) : array|false|int
Generic search, all specified parameters will be connected with `AND` condition, basically, this is more like filter than real search, but anyway.

Also there are some `private` methods which also might be useful sometimes, please, refer to source code for details.

<a name="example" />
###[Up](#) Example

```php
<?php
...
$this->search([
	'argument'     => 'Value',              // Exact equality
	'argument2'    => ['Value1', 'Value2'], // Any of specified, exact equality
	'argument2'    => [                     // Range search, margins are included in range
		'from' => 2,                    // more or equal, optional
		'to'   => 5                     // less or equal, optional
	],
	'joined_table' => [                     // Joined tables also supported, syntax inside the same as here
		'argument' => 'Yes'
	]
]);
...
```
