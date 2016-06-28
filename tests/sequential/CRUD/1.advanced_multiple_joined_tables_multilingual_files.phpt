--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Request::instance()->init_from_globals();
Response::instance()->init_with_typical_default_settings();
class CRUD_advanced {
	use
		CRUD;
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
	function __construct () {
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
	function test () {
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
			'Russian',
			'Ukrainian'
		];

		Event::instance()
			->on(
				'System/upload_files/add_tag',
				function ($data) {
					var_dump('File tag added', $data);
				}
			)
			->on(
				'System/upload_files/del_tag',
				function ($data) {
					var_dump('File tag deleted', $data);
				}
			);

		$L = Language::instance();
		$L->change('English');

		var_dump('create English');
		var_dump(
			$id = $this->create(
				"Title $L->clang",
				"Description <a href=\"https://xyz.com/$L->clang\">XYZ Inc.</a> $L->clang",
				[1, 2, 3],
				[12]
			)
		);
		var_dump(
			$data = $this->read($id)
		);

		$L->change('Ukrainian');

		var_dump('change Ukrainian');
		var_dump(
			$this->update(
				[
					$id,
					"Title $L->clang",
					"Description <a href=\"https://xyz.com/$L->clang\">XYZ Inc.</a> $L->clang",
					[4, 5, 6],
					[24]
				]
			)
		);
		var_dump($this->read($id));

		$L->change('English');

		var_dump('read English after change Ukrainian', $this->read($id));

		$L->change('Russian');

		var_dump('read Russian', $this->read($id));

		$Text = Text::instance();
		Text::instance_stub(
			[],
			[
				'delete' => function (...$arguments) use ($Text) {
					var_dump('Text::del() called', $arguments);
					$Text->del(...$arguments);
				}
			]
		);
		var_dump('delete');
		var_dump($this->delete($id));

		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/advanced.cleanup.sql")),
				'trim'
			)
		);
	}
}

(new CRUD_advanced)->test();
?>
--EXPECT--
string(14) "create English"
string(14) "File tag added"
array(2) {
  ["tag"]=>
  string(23) "crud_test/advanced/1/en"
  ["url"]=>
  string(18) "https://xyz.com/en"
}
int(1)
array(5) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(8) "Title en"
  ["description"]=>
  string(56) "Description <a href="https://xyz.com/en">XYZ Inc.</a> en"
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
  ["joined_table2"]=>
  array(1) {
    [0]=>
    int(12)
  }
}
string(16) "change Ukrainian"
string(16) "File tag deleted"
array(2) {
  ["tag"]=>
  string(23) "crud_test/advanced/1/uk"
  ["url"]=>
  string(18) "https://xyz.com/en"
}
string(14) "File tag added"
array(2) {
  ["tag"]=>
  string(23) "crud_test/advanced/1/uk"
  ["url"]=>
  string(18) "https://xyz.com/uk"
}
bool(true)
array(5) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(8) "Title uk"
  ["description"]=>
  string(56) "Description <a href="https://xyz.com/uk">XYZ Inc.</a> uk"
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(4)
    [1]=>
    int(5)
    [2]=>
    int(6)
  }
  ["joined_table2"]=>
  array(1) {
    [0]=>
    int(24)
  }
}
string(35) "read English after change Ukrainian"
array(5) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(8) "Title en"
  ["description"]=>
  string(56) "Description <a href="https://xyz.com/en">XYZ Inc.</a> en"
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
  ["joined_table2"]=>
  array(1) {
    [0]=>
    int(24)
  }
}
string(12) "read Russian"
array(5) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(8) "Title en"
  ["description"]=>
  string(56) "Description <a href="https://xyz.com/en">XYZ Inc.</a> en"
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
  ["joined_table2"]=>
  array(1) {
    [0]=>
    int(24)
  }
}
string(6) "delete"
string(16) "File tag deleted"
array(1) {
  ["tag"]=>
  string(21) "crud_test/advanced/1%"
}
bool(true)
