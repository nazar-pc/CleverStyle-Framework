--TEST--
DB class check
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/_Fake_engine.php';
$Config = Config::instance_stub(
	[
		'core' => [
			'db_balance'       => 0,
			'maindb_for_write' => 1
		],
		'db'   => [
			0 => [
				'mirrors' => [
					[
						'type'     => 'Fake',
						'host'     => 'localhost',
						'name'     => 'database',
						'user'     => 'user',
						'password' => 'db 0, mirror',
						'charset'  => 'utf8',
						'prefix'   => '__prefix0__'
					]
				]
			],
			2 => [
				'mirrors' => [
					[
						'type'     => 'Fake',
						'host'     => 'localhost',
						'name'     => 'database',
						'user'     => 'user',
						'password' => 'db 2, mirror',
						'charset'  => 'utf8',
						'prefix'   => '__prefix2__'
					]
				]
			]
		]
	]
);
Core::instance_stub(
	[
		'db_type'     => 'Fake',
		'db_host'     => 'localhost',
		'db_name'     => 'database',
		'db_user'     => 'user',
		'db_password' => 'db 0',
		'db_charset'  => 'utf8',
		'db_prefix'   => '__prefix__'
	]
);
Language::instance_stub();
DB\Fake::$connected_fake = function () {
	return true;
};

var_dump('DB for read');
var_dump(DB::instance()->db(0));
DB::instance_reset();

var_dump('DB for write');
/** @noinspection ForgottenDebugOutputInspection */
var_dump(DB::instance()->db_prime(0));
DB::instance_reset();

var_dump('Write only master check');
$Config->core['db_balance'] = 1;
var_dump(DB::instance()->db(2));
DB::instance_reset();

var_dump('Check for switching to mirror if master failed');
// Return false only first time
DB\Fake::$connected_fake          = function () {
	static $connected = false;
	$result = $connected;
	if (!$connected) {
		$connected = true;
	}
	return $result;
};
$Config->core['maindb_for_write'] = 0;
var_dump(DB::instance()->db(0));
?>
--EXPECT--
string(11) "DB for read"
string(34) "Fake engine called with arguments:"
array(6) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(4) "db 0"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(4) "utf8"
  [5]=>
  string(10) "__prefix__"
}
string(19) "Connection: succeed"
object(cs\DB\Fake)#8 (9) {
  ["connected":protected]=>
  bool(true)
  ["db_type":protected]=>
  bool(false)
  ["database":protected]=>
  NULL
  ["prefix":protected]=>
  NULL
  ["time":protected]=>
  NULL
  ["query":protected]=>
  array(2) {
    ["time"]=>
    string(0) ""
    ["text"]=>
    string(0) ""
  }
  ["queries":protected]=>
  array(3) {
    ["num"]=>
    string(0) ""
    ["time"]=>
    array(0) {
    }
    ["text"]=>
    array(0) {
    }
  }
  ["connecting_time":protected]=>
  NULL
  ["async":protected]=>
  bool(false)
}
string(12) "DB for write"
string(34) "Fake engine called with arguments:"
array(6) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(4) "db 0"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(4) "utf8"
  [5]=>
  string(10) "__prefix__"
}
string(19) "Connection: succeed"
object(cs\DB\Fake)#8 (9) {
  ["connected":protected]=>
  bool(true)
  ["db_type":protected]=>
  bool(false)
  ["database":protected]=>
  NULL
  ["prefix":protected]=>
  NULL
  ["time":protected]=>
  NULL
  ["query":protected]=>
  array(2) {
    ["time"]=>
    string(0) ""
    ["text"]=>
    string(0) ""
  }
  ["queries":protected]=>
  array(3) {
    ["num"]=>
    string(0) ""
    ["time"]=>
    array(0) {
    }
    ["text"]=>
    array(0) {
    }
  }
  ["connecting_time":protected]=>
  NULL
  ["async":protected]=>
  bool(false)
}
string(23) "Write only master check"
string(34) "Fake engine called with arguments:"
array(6) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(12) "db 2, mirror"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(4) "utf8"
  [5]=>
  string(11) "__prefix2__"
}
string(19) "Connection: succeed"
object(cs\DB\Fake)#8 (9) {
  ["connected":protected]=>
  bool(true)
  ["db_type":protected]=>
  bool(false)
  ["database":protected]=>
  NULL
  ["prefix":protected]=>
  NULL
  ["time":protected]=>
  NULL
  ["query":protected]=>
  array(2) {
    ["time"]=>
    string(0) ""
    ["text"]=>
    string(0) ""
  }
  ["queries":protected]=>
  array(3) {
    ["num"]=>
    string(0) ""
    ["time"]=>
    array(0) {
    }
    ["text"]=>
    array(0) {
    }
  }
  ["connecting_time":protected]=>
  NULL
  ["async":protected]=>
  bool(false)
}
string(46) "Check for switching to mirror if master failed"
string(34) "Fake engine called with arguments:"
array(6) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(4) "db 0"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(4) "utf8"
  [5]=>
  string(10) "__prefix__"
}
string(18) "Connection: failed"
string(34) "Fake engine called with arguments:"
array(6) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(12) "db 0, mirror"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(4) "utf8"
  [5]=>
  string(11) "__prefix0__"
}
string(19) "Connection: succeed"
object(cs\DB\Fake)#9 (9) {
  ["connected":protected]=>
  bool(true)
  ["db_type":protected]=>
  bool(false)
  ["database":protected]=>
  NULL
  ["prefix":protected]=>
  NULL
  ["time":protected]=>
  NULL
  ["query":protected]=>
  array(2) {
    ["time"]=>
    string(0) ""
    ["text"]=>
    string(0) ""
  }
  ["queries":protected]=>
  array(3) {
    ["num"]=>
    string(0) ""
    ["time"]=>
    array(0) {
    }
    ["text"]=>
    array(0) {
    }
  }
  ["connecting_time":protected]=>
  NULL
  ["async":protected]=>
  bool(false)
}
