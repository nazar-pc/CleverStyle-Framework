--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Response::instance()->init_with_typical_default_settings();
Request::instance()->init_from_globals();
class CRUD_helpers_advanced {
	use
		CRUD_helpers;
	protected $table                       = '[prefix]crud_test_advanced';
	protected $data_model                  = [
		'id'            => null, // Set in constructor
		'title'         => 'ml:text',
		'description'   => 'ml:',
		'joined_table1' => [
			'data_model'     => [
				'id'    => 'int:1',
				'value' => 'int:1'
			],
			'language_field' => 'lang'
		],
		'joined_table2' => [
			'data_model' => [
				'id'     => 'int:1',
				'points' => null // Set in constructor
			]
		]
	];
	protected $data_model_ml_group         = 'crud_test/advanced';
	protected $data_model_files_tag_prefix = 'crud_test/advanced';
	public function __construct () {
		$this->data_model['id']                                    = function ($value) {
			return max(1, (int)$value);
		};
		$this->data_model['joined_table2']['data_model']['points'] = function ($value) {
			return max(1, (int)$value);
		};
	}
	protected function cdb () {
		return 0;
	}
	public function test () {
		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/advanced.$_ENV[DB].sql")),
				'trim'
			)
		);

		$Config                           = Config::instance();
		$Config->core['multilingual']     = 1;
		$Config->core['active_languages'] = [
			'English',
			'Ukrainian'
		];

		$L = Language::instance();

		for ($i = 1; $i <= 5; ++$i) {
			var_dump("create English $i");
			$L->change('English');
			$id = $this->create(
				"Title $L->clang $i",
				"Title $L->clang $i",
				$i,
				$i
			);

			var_dump("change Ukrainian $i");
			$L->change('Ukrainian');
			$this->update(
				[
					$id,
					"Title $L->clang $i",
					"Title $L->clang ".(5 - $i),
					5 - $i,
					5 - $i
				]
			);
		}

		foreach ($Config->core['active_languages'] as $clang) {
			$L->change($clang);

			var_dump("Exact match search (main table, multilingual column, $clang)");
			var_dump(
				$this->search(
					[
						'title' => "Title $L->clang 1"
					]
				)
			);

			var_dump("Exact match search (main table, multilingual column, $clang, multiple choice)");
			var_dump(
				$this->search(
					[
						'title' => ["Title $L->clang 1", "Title $L->clang 4"]
					]
				)
			);

			var_dump("Exact match search (joined table, multilingual, $clang)");
			var_dump(
				$this->search(
					[
						'joined_table1' => [
							'value' => 1
						]
					]
				)
			);

			var_dump("Exact match search (joined table, multilingual, $clang, multiple choice)");
			var_dump(
				$this->search(
					[
						'joined_table1' => [
							'value' => [1, 2]
						]
					]
				)
			);

			var_dump("Range search (joined table, multilingual, $clang)");
			var_dump(
				$this->search(
					[
						'joined_table1' => [
							'value' => [
								'from' => 3
							]
						]
					]
				)
			);
			var_dump(
				$this->search(
					[
						'joined_table1' => [
							'value' => [
								'to' => 2
							]
						]
					]
				)
			);
			var_dump(
				$this->search(
					[
						'joined_table1' => [
							'value' => [
								'from' => 2,
								'to'   => 3
							]
						]
					]
				)
			);

			var_dump("Exact match search (joined table, multilingual, $clang, simplified)");
			var_dump(
				$this->search(
					[
						'joined_table1' => 1
					]
				)
			);

			var_dump("Order by (multilingual column, $clang, desc)");
			var_dump(
				$this->search(
					[],
					1,
					100,
					'description'
				)
			);

			var_dump("Order by (multilingual column, $clang, asc)");
			var_dump(
				$this->search(
					[],
					1,
					100,
					'description',
					true
				)
			);
		}

		var_dump('Order by (joined table, desc)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table2:points'
			)
		);

		var_dump('Order by (joined table, asc)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table2:points',
				true
			)
		);

		var_dump('Order by (joined table, no column specified)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table2'
			)
		);

		var_dump('Order by (joined table, non-existent column)');
		var_dump(
			$this->search(
				[],
				1,
				100,
				'joined_table2:non-existent'
			)
		);

		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/advanced.cleanup.sql")),
				'trim'
			)
		);
	}
}

(new CRUD_helpers_advanced)->test();
?>
--EXPECT--
string(16) "create English 1"
string(18) "change Ukrainian 1"
string(16) "create English 2"
string(18) "change Ukrainian 2"
string(16) "create English 3"
string(18) "change Ukrainian 3"
string(16) "create English 4"
string(18) "change Ukrainian 4"
string(16) "create English 5"
string(18) "change Ukrainian 5"
string(61) "Exact match search (main table, multilingual column, English)"
array(1) {
  [0]=>
  int(1)
}
string(78) "Exact match search (main table, multilingual column, English, multiple choice)"
array(2) {
  [0]=>
  int(4)
  [1]=>
  int(1)
}
string(56) "Exact match search (joined table, multilingual, English)"
array(1) {
  [0]=>
  int(1)
}
string(73) "Exact match search (joined table, multilingual, English, multiple choice)"
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(1)
}
string(50) "Range search (joined table, multilingual, English)"
array(3) {
  [0]=>
  int(5)
  [1]=>
  int(4)
  [2]=>
  int(3)
}
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(1)
}
array(2) {
  [0]=>
  int(3)
  [1]=>
  int(2)
}
string(68) "Exact match search (joined table, multilingual, English, simplified)"
array(1) {
  [0]=>
  int(1)
}
string(45) "Order by (multilingual column, English, desc)"
array(5) {
  [0]=>
  int(5)
  [1]=>
  int(4)
  [2]=>
  int(3)
  [3]=>
  int(2)
  [4]=>
  int(1)
}
string(44) "Order by (multilingual column, English, asc)"
array(5) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(4)
  [4]=>
  int(5)
}
string(63) "Exact match search (main table, multilingual column, Ukrainian)"
array(1) {
  [0]=>
  int(1)
}
string(80) "Exact match search (main table, multilingual column, Ukrainian, multiple choice)"
array(2) {
  [0]=>
  int(4)
  [1]=>
  int(1)
}
string(58) "Exact match search (joined table, multilingual, Ukrainian)"
array(1) {
  [0]=>
  int(4)
}
string(75) "Exact match search (joined table, multilingual, Ukrainian, multiple choice)"
array(2) {
  [0]=>
  int(4)
  [1]=>
  int(3)
}
string(52) "Range search (joined table, multilingual, Ukrainian)"
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(1)
}
array(2) {
  [0]=>
  int(4)
  [1]=>
  int(3)
}
array(2) {
  [0]=>
  int(3)
  [1]=>
  int(2)
}
string(70) "Exact match search (joined table, multilingual, Ukrainian, simplified)"
array(1) {
  [0]=>
  int(4)
}
string(47) "Order by (multilingual column, Ukrainian, desc)"
array(5) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(4)
  [4]=>
  int(5)
}
string(46) "Order by (multilingual column, Ukrainian, asc)"
array(5) {
  [0]=>
  int(5)
  [1]=>
  int(4)
  [2]=>
  int(3)
  [3]=>
  int(2)
  [4]=>
  int(1)
}
string(29) "Order by (joined table, desc)"
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(4)
}
string(28) "Order by (joined table, asc)"
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
string(44) "Order by (joined table, no column specified)"
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(4)
}
string(44) "Order by (joined table, non-existent column)"
array(5) {
  [0]=>
  int(5)
  [1]=>
  int(4)
  [2]=>
  int(3)
  [3]=>
  int(2)
  [4]=>
  int(1)
}
