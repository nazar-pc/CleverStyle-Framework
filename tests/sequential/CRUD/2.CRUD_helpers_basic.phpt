--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
class CRUD_helpers_basic {
	use
		CRUD_helpers;
	protected $table      = '[prefix]crud_test_basic';
	protected $data_model = [
		'id'           => 'int:1',
		'max'          => 'int:1..5',
		'set'          => 'set:x,y,z',
		'number'       => null, // Set in constructor
		'title'        => 'text',
		'description'  => 'html:13:###',
		'data'         => 'json',
		'joined_table' => [
			'data_model' => [
				'id'    => 'int:1',
				'value' => 'int:1'
			]
		]
	];
	function __construct () {
		$this->data_model['number'] = function ($value) {
			return max(1, (int)$value);
		};
	}
	protected function cdb () {
		return 0;
	}
	function test () {
		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/basic.$_ENV[DB].sql")),
				'trim'
			)
		);

		var_dump('create #1');
		var_dump(
			$this->create(
				5,
				'x',
				5,
				'Title 1',
				'Description 1',
				['d1-1', 'd1-2', false, null, 12, [], true, 10.5],
				[1, 2, 3]
			)
		);
		var_dump('create #2');
		var_dump(
			$this->create(
				[
					2,
					'y',
					2,
					'Title 2',
					'Description 2 Longer than needed',
					['d2-1', 'd2-2', false, null, 12, [], true, 10.5],
					[2, 3, 4]
				]
			)
		);
		var_dump('create #3');
		var_dump(
			$this->create(
				3,
				2,
				'z',
				3,
				'Title 3',
				'Description 3',
				null,
				[2, 3]
			)
		);
		var_dump('create #4');
		var_dump(
			$this->create(
				4,
				-1,
				'n',
				-1,
				'Title 4',
				'Description 4',
				true,
				[]
			)
		);

		var_dump('Exact match search (main table)');
		var_dump(
			$this->search(
				[
					'title' => 'Title 1'
				]
			)
		);

		var_dump('Exact match search (main table, count)');
		var_dump(
			$this->search(
				[
					'title'       => 'Title 1',
					'total_count' => 1
				]
			)
		);

		var_dump('Exact match search (main table, multiple choice)');
		var_dump(
			$this->search(
				[
					'title' => ['Title 1', 'Title 2']
				]
			)
		);

		var_dump('Range search (main table)');
		var_dump(
			$this->search(
				[
					'max' => [
						'from' => 2
					]
				]
			)
		);
		var_dump(
			$this->search(
				[
					'max' => [
						'to' => 3
					]
				]
			)
		);
		var_dump(
			$this->search(
				[
					'max' => [
						'from' => 2,
						'to'   => 3
					]
				]
			)
		);

		var_dump('Exact match search (joined table)');
		var_dump(
			$this->search(
				[
					'joined_table' => [
						'value' => 1
					]
				]
			)
		);

		var_dump('Exact match search (joined table, multiple choice)');
		var_dump(
			$this->search(
				[
					'joined_table' => [
						'value' => [1, 2]
					]
				]
			)
		);

		var_dump('Range search (joined table)');
		var_dump(
			$this->search(
				[
					'joined_table' => [
						'value' => [
							'from' => 4
						]
					]
				]
			)
		);
		var_dump(
			$this->search(
				[
					'joined_table' => [
						'value' => [
							'to' => 1
						]
					]
				]
			)
		);
		var_dump(
			$this->search(
				[
					'joined_table' => [
						'value' => [
							'from' => 2,
							'to'   => 3
						]
					]
				]
			)
		);

		var_dump('Exact match search (joined table, simplified)');
		var_dump(
			$this->search(
				[
					'joined_table' => 1
				]
			)
		);

		var_dump('Order by (main table, desc)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'number'
			)
		);

		var_dump('Order by (main table, asc)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'number',
				true
			)
		);

		var_dump('Order by (main table, non-existent column)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'non-existent'
			)
		);

		var_dump('Order by (joined table, desc)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table:value'
			)
		);

		var_dump('Order by (joined table, asc)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table:value',
				true
			)
		);

		var_dump('Order by (joined table, no column specified)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table'
			)
		);

		var_dump('Order by (joined table, non-existent column)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table:non-existent'
			)
		);

		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/basic.cleanup.sql")),
				'trim'
			)
		);
	}
}

(new CRUD_helpers_basic)->test();
?>
--EXPECT--
string(9) "create #1"
int(1)
string(9) "create #2"
int(2)
string(9) "create #3"
int(3)
string(9) "create #4"
int(4)
string(31) "Exact match search (main table)"
array(1) {
  [0]=>
  int(1)
}
string(38) "Exact match search (main table, count)"
int(1)
string(48) "Exact match search (main table, multiple choice)"
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(1)
}
string(25) "Range search (main table)"
array(3) {
  [0]=>
  int(3)
  [1]=>
  int(2)
  [2]=>
  int(1)
}
array(3) {
  [0]=>
  int(4)
  [1]=>
  int(3)
  [2]=>
  int(2)
}
array(2) {
  [0]=>
  int(3)
  [1]=>
  int(2)
}
string(33) "Exact match search (joined table)"
array(1) {
  [0]=>
  int(1)
}
string(50) "Exact match search (joined table, multiple choice)"
array(3) {
  [0]=>
  int(3)
  [1]=>
  int(2)
  [2]=>
  int(1)
}
string(27) "Range search (joined table)"
array(1) {
  [0]=>
  int(2)
}
array(1) {
  [0]=>
  int(1)
}
array(3) {
  [0]=>
  int(3)
  [1]=>
  int(2)
  [2]=>
  int(1)
}
string(45) "Exact match search (joined table, simplified)"
array(1) {
  [0]=>
  int(1)
}
string(27) "Order by (main table, desc)"
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(3)
  [2]=>
  int(2)
  [3]=>
  int(4)
}
string(26) "Order by (main table, asc)"
array(4) {
  [0]=>
  int(4)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(1)
}
string(42) "Order by (main table, non-existent column)"
array(4) {
  [0]=>
  int(4)
  [1]=>
  int(3)
  [2]=>
  int(2)
  [3]=>
  int(1)
}
string(29) "Order by (joined table, desc)"
array(3) {
  [0]=>
  int(2)
  [1]=>
  int(1)
  [2]=>
  int(3)
}
string(28) "Order by (joined table, asc)"
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(3)
  [2]=>
  int(2)
}
string(44) "Order by (joined table, no column specified)"
array(3) {
  [0]=>
  int(2)
  [1]=>
  int(1)
  [2]=>
  int(3)
}
string(44) "Order by (joined table, non-existent column)"
array(4) {
  [0]=>
  int(4)
  [1]=>
  int(3)
  [2]=>
  int(2)
  [3]=>
  int(1)
}
