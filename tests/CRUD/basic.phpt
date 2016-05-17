--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
class CRUD_test {
	use
		CRUD;
	protected $table      = '[prefix]crud_test';
	protected $data_model = [
		'id'            => 'int:1',
		'title'         => 'text',
		'description'   => 'html',
		'data'          => 'json',
		'joined_table1' => [
			'data_model' => [
				'id'    => 'int:1',
				'value' => 'int:1'
			]
		]
	];
	protected function cdb () {
		return 0;
	}
	function test () {
		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/create.$_ENV[DB].sql")),
				'trim'
			)
		);

		var_dump(
			'create #1',
			$this->create(
				'Title 1',
				'Description 1',
				['d1-1', 'd1-2', false, null, 12, [], true, 10.5],
				[1, 2, 3]
			)
		);
		var_dump(
			'create #2',
			$this->create(
				[
					'Title 2',
					'Description 2',
					['d2-1', 'd2-2', false, null, 12, [], true, 10.5],
					[2, 3, 4]
				]
			)
		);
		var_dump(
			'create #3',
			$this->create(
				3,
				'Title 3',
				'Description 3',
				null,
				[]
			)
		);
		var_dump(
			'create #4',
			$this->create(
				4,
				'Title 4',
				'Description 4',
				true,
				[]
			)
		);

		var_dump(
			'read #1',
			$data_1 = $this->read(1)
		);
		var_dump(
			'read #2',
			$data_2 = $this->read(2)
		);
		var_dump('read #3', $this->read([1, 2]));

		$data_1['title'] .= '+';
		var_dump(
			'update #1',
			$this->update($data_1),
			$this->read(1)
		);
		$data_2['title'] .= '+';
		var_dump(
			'update #2',
			$this->update(array_values($data_2)),
			$data_2 = $this->read(2)
		);
		$data_2['title'] .= '-';
		var_dump(
			'update #3',
			$this->update(...array_values($data_2)),
			$this->read(2)
		);
		var_dump(
			'delete #1',
			$this->delete(1),
			$this->read(1)
		);
		var_dump(
			'delete #2',
			$this->delete([2, 3]),
			$this->read([2, 3, 4])
		);

		$this->db_prime()->q(
			array_filter(
				explode(';', file_get_contents(__DIR__."/cleanup.sql")),
				'trim'
			)
		);
	}
}

(new CRUD_test)->test();
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
string(7) "read #1"
array(5) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(7) "Title 1"
  ["description"]=>
  string(13) "Description 1"
  ["data"]=>
  array(8) {
    [0]=>
    string(4) "d1-1"
    [1]=>
    string(4) "d1-2"
    [2]=>
    bool(false)
    [3]=>
    NULL
    [4]=>
    int(12)
    [5]=>
    array(0) {
    }
    [6]=>
    bool(true)
    [7]=>
    float(10.5)
  }
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
}
string(7) "read #2"
array(5) {
  ["id"]=>
  int(2)
  ["title"]=>
  string(7) "Title 2"
  ["description"]=>
  string(13) "Description 2"
  ["data"]=>
  array(8) {
    [0]=>
    string(4) "d2-1"
    [1]=>
    string(4) "d2-2"
    [2]=>
    bool(false)
    [3]=>
    NULL
    [4]=>
    int(12)
    [5]=>
    array(0) {
    }
    [6]=>
    bool(true)
    [7]=>
    float(10.5)
  }
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(2)
    [1]=>
    int(3)
    [2]=>
    int(4)
  }
}
string(7) "read #3"
array(2) {
  [0]=>
  array(5) {
    ["id"]=>
    int(1)
    ["title"]=>
    string(7) "Title 1"
    ["description"]=>
    string(13) "Description 1"
    ["data"]=>
    array(8) {
      [0]=>
      string(4) "d1-1"
      [1]=>
      string(4) "d1-2"
      [2]=>
      bool(false)
      [3]=>
      NULL
      [4]=>
      int(12)
      [5]=>
      array(0) {
      }
      [6]=>
      bool(true)
      [7]=>
      float(10.5)
    }
    ["joined_table1"]=>
    array(3) {
      [0]=>
      int(1)
      [1]=>
      int(2)
      [2]=>
      int(3)
    }
  }
  [1]=>
  array(5) {
    ["id"]=>
    int(2)
    ["title"]=>
    string(7) "Title 2"
    ["description"]=>
    string(13) "Description 2"
    ["data"]=>
    array(8) {
      [0]=>
      string(4) "d2-1"
      [1]=>
      string(4) "d2-2"
      [2]=>
      bool(false)
      [3]=>
      NULL
      [4]=>
      int(12)
      [5]=>
      array(0) {
      }
      [6]=>
      bool(true)
      [7]=>
      float(10.5)
    }
    ["joined_table1"]=>
    array(3) {
      [0]=>
      int(2)
      [1]=>
      int(3)
      [2]=>
      int(4)
    }
  }
}
string(9) "update #1"
bool(true)
array(5) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(8) "Title 1+"
  ["description"]=>
  string(13) "Description 1"
  ["data"]=>
  array(8) {
    [0]=>
    string(4) "d1-1"
    [1]=>
    string(4) "d1-2"
    [2]=>
    bool(false)
    [3]=>
    NULL
    [4]=>
    int(12)
    [5]=>
    array(0) {
    }
    [6]=>
    bool(true)
    [7]=>
    float(10.5)
  }
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
}
string(9) "update #2"
bool(true)
array(5) {
  ["id"]=>
  int(2)
  ["title"]=>
  string(8) "Title 2+"
  ["description"]=>
  string(13) "Description 2"
  ["data"]=>
  array(8) {
    [0]=>
    string(4) "d2-1"
    [1]=>
    string(4) "d2-2"
    [2]=>
    bool(false)
    [3]=>
    NULL
    [4]=>
    int(12)
    [5]=>
    array(0) {
    }
    [6]=>
    bool(true)
    [7]=>
    float(10.5)
  }
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(2)
    [1]=>
    int(3)
    [2]=>
    int(4)
  }
}
string(9) "update #3"
bool(true)
array(5) {
  ["id"]=>
  int(2)
  ["title"]=>
  string(9) "Title 2+-"
  ["description"]=>
  string(13) "Description 2"
  ["data"]=>
  array(8) {
    [0]=>
    string(4) "d2-1"
    [1]=>
    string(4) "d2-2"
    [2]=>
    bool(false)
    [3]=>
    NULL
    [4]=>
    int(12)
    [5]=>
    array(0) {
    }
    [6]=>
    bool(true)
    [7]=>
    float(10.5)
  }
  ["joined_table1"]=>
  array(3) {
    [0]=>
    int(2)
    [1]=>
    int(3)
    [2]=>
    int(4)
  }
}
string(9) "delete #1"
bool(true)
bool(false)
string(9) "delete #2"
bool(true)
array(3) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
  [2]=>
  array(5) {
    ["id"]=>
    int(4)
    ["title"]=>
    string(7) "Title 4"
    ["description"]=>
    string(13) "Description 4"
    ["data"]=>
    bool(true)
    ["joined_table1"]=>
    array(0) {
    }
  }
}
