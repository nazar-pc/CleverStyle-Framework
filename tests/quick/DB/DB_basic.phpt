--FILE--
<?php
namespace cs\DB {
	include __DIR__.'/../../unit.php';
	class Fake extends _Abstract {
		/**
		 * @var callable
		 */
		public static $connected_fake;
		public function __construct ($database, $user = '', $password = '', $host = 'localhost', $prefix = '') {
			$this->database  = $database;
			$connected_fake  = Fake::$connected_fake;
			$this->connected = $connected_fake();
		}
		protected function q_internal ($query, $parameters = []) { }
		protected function q_multi_internal ($query, $parameters = []) { }
		public function n ($query_result) { }
		public function f ($query_result, $single_column = false, $array = false, $indexed = false) { }
		public function id () { }
		public function affected () { }
		public function free ($query_result) { }
		protected function s_internal ($string, $single_quotes_around) { }
		public function server () { }
		public function __destruct () { }
		public function columns ($table, $like = false) { }
		public function tables ($like = false) { }
		public function queries_count () {
			return 10;
		}
		public function connecting_time () {
			return 3;
		}
		public function time () {
			return 10;
		}
	}
	Fake::$connected_fake = function () {
		return true;
	};
}
namespace cs {
	function trigger_error ($error) {
	}

	$Config = Config::instance_stub(
		[
			'core' => [
				'db_balance'     => 1,
				'db_mirror_mode' => DB::MIRROR_MODE_MASTER_SLAVE
			],
			'db'   => [
				0 => [
					'mirrors' => [
						[
							'driver'   => 'Fake',
							'host'     => 'localhost',
							'name'     => 'database00',
							'user'     => 'user',
							'password' => 'db 0, mirror 0',
							'prefix'   => '__prefix0__'
						]
					]
				],
				1 => [
					'driver'   => 'Fake',
					'host'     => 'localhost',
					'name'     => 'database1',
					'user'     => 'user',
					'password' => 'db 1',
					'prefix'   => '__prefix1__',
					'mirrors'  => []
				],
				2 => [
					'mirrors' => [
						[
							'driver'   => 'Fake',
							'host'     => 'localhost',
							'name'     => 'database20',
							'user'     => 'user',
							'password' => 'db 2, mirror 0',
							'prefix'   => '__prefix2__'
						]
					]
				]
			]
		]
	);
	$Core   = Core::instance_stub(
		[
			'db_driver'   => 'Fake',
			'db_host'     => 'localhost',
			'db_name'     => 'database0',
			'db_user'     => 'user',
			'db_password' => 'db 0',
			'db_prefix'   => '__prefix__'
		]
	);

	var_dump('Write DB instance');
	$DB             = DB::instance();
	$write_instance = $DB->db_prime(0);
	var_dump($write_instance instanceof DB\Fake);

	var_dump('Read DB instance after write created');
	var_dump(DB::instance()->db(0) === $write_instance);

	var_dump('Read DB instance first');
	DB::instance_reset();
	$DB             = DB::instance();
	$read_instance = $DB->db(0);
	var_dump($read_instance instanceof DB\Fake);

	var_dump('Write DB instance after read created');
	$DB             = DB::instance();
	$write_instance = $DB->db_prime(0);
	var_dump($write_instance instanceof DB\Fake);
	var_dump($write_instance !== $read_instance);

	var_dump('Queries number and time spent');
	var_dump($DB->queries());
	var_dump($DB->time());

	var_dump('Read DB instance, balance, master-slave');
	for ($i = 0; $i < 100; ++$i) {
		DB::instance_reset();
		if (DB::instance()->db(0)->database() == 'database0') {
			var_dump('Master database selected for reading');
		}
	}

	var_dump('Read DB instance, master-slave, same instance on repeated mirror call');
	DB::instance_reset();
	$DB = DB::instance();
	var_dump($DB->db(0) === $DB->db(0));

	var_dump('Connections list (master, mirror, successful, failed)');
	var_dump($DB->get_connections_list(DB::CONNECTIONS_MASTER));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_MIRROR));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_SUCCESSFUL));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_FAILED));

	var_dump('Read DB instance, balance, master-master');
	$tmp                            = [];
	$Config->core['db_mirror_mode'] = DB::MIRROR_MODE_MASTER_MASTER;
	for ($i = 0; $i < 100; ++$i) {
		DB::instance_reset();
		$tmp[DB::instance()->db(0)->database()] = 1;
	}
	/** @noinspection OffsetOperationsInspection */
	var_dump(count($tmp) > 1);
	unset($tmp);

	var_dump('Read DB instance, no balance');
	$Config->core['db_balance'] = 0;
	DB::instance_reset();
	$instance = DB::instance()->db(0);
	var_dump($instance->database() == $Core->db_name);

	var_dump('Get successful connection again');
	var_dump(DB::instance()->db(0) === $instance);

	var_dump('Connection fails once');
	DB\Fake::$connected_fake = function () {
		static $success = false;
		if (!$success) {
			$success = true;
			return false;
		}
		return true;
	};
	DB::instance_reset();
	var_dump(DB::instance()->db(0) instanceof DB\Fake);

	var_dump('Connection fails hard');
	$Config->core['db_balance']     = 1;
	$Config->core['db_mirror_mode'] = DB::MIRROR_MODE_MASTER_SLAVE;
	DB\Fake::$connected_fake        = function () {
		return false;
	};
	DB::instance_reset();
	var_dump(DB::instance()->db(2) instanceof False_class);

	var_dump('Connections list (master, mirror, successful, failed)');
	var_dump($DB->get_connections_list(DB::CONNECTIONS_MASTER));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_MIRROR));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_SUCCESSFUL));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_FAILED));

	var_dump('Get failed connection again');
	var_dump(DB::instance()->db(2) instanceof False_class);

	var_dump('Failed connection to core DB throws an exception');
	try {
		DB::instance()->db(0);
	} catch (ExitException $e) {
		var_dump('ExitException', $e->getCode());
	}

	var_dump('Connections list (master, mirror, successful, failed)');
	var_dump($DB->get_connections_list(DB::CONNECTIONS_MASTER));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_MIRROR));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_SUCCESSFUL));
	var_dump($DB->get_connections_list(DB::CONNECTIONS_FAILED));
}
?>
--EXPECTF--
string(17) "Write DB instance"
bool(true)
string(36) "Read DB instance after write created"
bool(true)
string(22) "Read DB instance first"
bool(true)
string(36) "Write DB instance after read created"
bool(true)
bool(true)
string(29) "Queries number and time spent"
int(20)
int(26)
string(39) "Read DB instance, balance, master-slave"
string(69) "Read DB instance, master-slave, same instance on repeated mirror call"
bool(true)
string(53) "Connections list (master, mirror, successful, failed)"
array(0) {
}
array(1) {
  [0]=>
  object(cs\DB\Fake)#%d (%d) {
    ["connected":protected]=>
    bool(true)
    ["db_type":protected]=>
    string(0) ""
    ["database":protected]=>
    string(10) "database00"
    ["prefix":protected]=>
    NULL
    ["time":protected]=>
    int(0)
    ["queries_count":protected]=>
    int(0)
    ["connecting_time":protected]=>
    int(0)
    ["in_transaction":protected]=>
    bool(false)
  }
}
array(1) {
  [0]=>
  string(29) "Core DB (Fake)/localhost/Fake"
}
array(0) {
}
string(40) "Read DB instance, balance, master-master"
bool(true)
string(28) "Read DB instance, no balance"
bool(true)
string(31) "Get successful connection again"
bool(true)
string(21) "Connection fails once"
bool(true)
string(21) "Connection fails hard"
bool(true)
string(53) "Connections list (master, mirror, successful, failed)"
array(0) {
}
array(1) {
  [0]=>
  object(cs\DB\Fake)#%d (%d) {
    ["connected":protected]=>
    bool(true)
    ["db_type":protected]=>
    string(0) ""
    ["database":protected]=>
    string(10) "database00"
    ["prefix":protected]=>
    NULL
    ["time":protected]=>
    int(0)
    ["queries_count":protected]=>
    int(0)
    ["connecting_time":protected]=>
    int(0)
    ["in_transaction":protected]=>
    bool(false)
  }
}
array(1) {
  [0]=>
  string(29) "Core DB (Fake)/localhost/Fake"
}
array(0) {
}
string(27) "Get failed connection again"
bool(true)
string(48) "Failed connection to core DB throws an exception"
string(13) "ExitException"
int(500)
string(53) "Connections list (master, mirror, successful, failed)"
array(0) {
}
array(1) {
  [0]=>
  object(cs\DB\Fake)#%d (%d) {
    ["connected":protected]=>
    bool(true)
    ["db_type":protected]=>
    string(0) ""
    ["database":protected]=>
    string(10) "database00"
    ["prefix":protected]=>
    NULL
    ["time":protected]=>
    int(0)
    ["queries_count":protected]=>
    int(0)
    ["connecting_time":protected]=>
    int(0)
    ["in_transaction":protected]=>
    bool(false)
  }
}
array(1) {
  [0]=>
  string(29) "Core DB (Fake)/localhost/Fake"
}
array(0) {
}
