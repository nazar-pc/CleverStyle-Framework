--SKIPIF--
<?php
if (getenv('DB') != 'SQLite') {
	exit('skip only running for database SQLite engine');
}
?>
--FILE--
<?php
include __DIR__.'/../bootstrap.php';
$db = new \cs\DB\SQLite('', '', '', __DIR__.'/../../cscms.travis/storage/sqlite.db', '', 'xyz_');
/**
 * @var \cs\DB\_Abstract $db
 */
if (!$db->connected()) {
	die('Connection failed:(');
}
$result = $db->q('SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC');
if (!$result) {
	die('Simple query failed');
}
if (!$db->q(
	[
		'SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC',
		'SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC'
	]
)) {
	die('Multi query failed');
}
$u = $db->f($result);
var_dump('single row', $u);
$u = $db->f($result, true);
var_dump('single row single column', $u);

$result = $db->q('SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC');
var_dump('multiple rows', $db->f($result, false, true));

$result = $db->q('SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC');
$u      = $db->f($result, true, true);
var_dump('multiple rows single column', $u);

$result = $db->q('SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC');
$u      = $db->f($result, false, true, true);
var_dump('multiple rows indexed array', $u);

$result = $db->q('SELECT `id`, `login` FROM `[prefix]users` ORDER BY `id` ASC');
$u      = $db->f($result, true, true, true);
var_dump('multiple rows indexed array single column', $u);

$db->q(
	/** @lang SQLite */
	'CREATE TABLE `test` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT , `title` VARCHAR(1024) NOT NULL , `description` TEXT NOT NULL , `value` FLOAT NOT NULL);'
);

$query = "INSERT INTO `test` (`title`, `description`, `value`) VALUES ('%s', '%s', '%f')";
$result = $db->insert(
	$query,
	[
		[
			'Title 1',
			'Description 1',
			10.5
		]
	]
);
if ($result) {
	var_dump('single insert id', $db->id(), $db->affected());
}
$result = $db->insert(
	$query,
	[
		[
			'Title 2',
			'Description 2',
			11.5
		],
		[
			'Title 3',
			'Description 3',
			12.5
		]
	]
);
if ($result) {
	var_dump('multiple insert id', $db->affected());
}
var_dump('->qf()', $db->qf("SELECT * FROM `test` ORDER BY `id` ASC"));
var_dump('->qf(..., 2)', $db->qf("SELECT * FROM `test` WHERE `id` = '%d' ORDER BY `id` ASC", 2));
var_dump('->qfs()', $db->qfs("SELECT * FROM `test` ORDER BY `id` ASC"));
var_dump('->qfa()', $db->qfa("SELECT * FROM `test` ORDER BY `id` ASC"));
var_dump('->qfas()', $db->qfas("SELECT * FROM `test`"));
var_dump('columns list', $db->columns('test'));
var_dump('columns list like title', $db->columns('test', 'title'));
var_dump('columns list like titl%', $db->columns('test', 'titl%'));
var_dump('tables list', $db->tables());
var_dump('tables list like test', $db->tables('test'));
var_dump('tables list like [prefix]users%', $db->tables('[prefix]users%'));
$db->transaction(function ($db) {
	/**
	 * @var \cs\DB\MySQLi $db
	 */
	$db->q('DELETE FROM `test` WHERE `id` = 2');
	return false;
});
var_dump('transaction for deletion: rollback #1', $db->qfs("SELECT `id` FROM `test` WHERE `id` = 2"));
try {
	$db->transaction(function ($db) {
		/**
		 * @var \cs\DB\MySQLi $db
		 */
		$db->q('DELETE FROM `test` WHERE `id` = 2');
		throw new Exception;
	});
} catch (Exception $e) {
	var_dump('thrown exception '.get_class($e));
}
var_dump('transaction for deletion: rollback #2', $db->qfs("SELECT `id` FROM `test` WHERE `id` = 2"));
try {
	$db->transaction(function ($db) {
		/**
		 * @var \cs\DB\MySQLi $db
		 */
		$db->q('DELETE FROM `test` WHERE `id` = 2');
		$db->transaction(function () {
			throw new Exception;
		});
	});
} catch (Exception $e) {
	var_dump('thrown exception '.get_class($e));
}
var_dump('transaction for deletion: rollback #3 (nested transaction)', $db->qfs("SELECT `id` FROM `test` WHERE `id` = 2"));
try {
	$db->transaction(function ($db) {
		/**
		 * @var \cs\DB\MySQLi $db
		 */
		$db->transaction(function ($db) {
			/**
			 * @var \cs\DB\MySQLi $db
			 */
			$db->q('DELETE FROM `test` WHERE `id` = 2');
		});
		throw new Exception;
	});
} catch (Exception $e) {
	var_dump('thrown exception '.get_class($e));
}
var_dump('transaction for deletion: rollback #4 (nested transaction)', $db->qfs("SELECT `id` FROM `test` WHERE `id` = 2"));
$db->transaction(function ($db) {
	/**
	 * @var \cs\DB\MySQLi $db
	 */
	$db->q('DELETE FROM `test` WHERE `id` = 2');
});
var_dump('transaction for deletion: commit', $db->qfs("SELECT `id` FROM `test` WHERE `id` = 2"));
$db->q('DROP TABLE `test`');
?>
--EXPECT--
string(10) "single row"
array(2) {
  ["id"]=>
  int(1)
  ["login"]=>
  string(5) "guest"
}
string(24) "single row single column"
int(2)
string(13) "multiple rows"
array(2) {
  [0]=>
  array(2) {
    ["id"]=>
    int(1)
    ["login"]=>
    string(5) "guest"
  }
  [1]=>
  array(2) {
    ["id"]=>
    int(2)
    ["login"]=>
    string(5) "admin"
  }
}
string(27) "multiple rows single column"
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
string(27) "multiple rows indexed array"
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    string(5) "guest"
  }
  [1]=>
  array(2) {
    [0]=>
    int(2)
    [1]=>
    string(5) "admin"
  }
}
string(41) "multiple rows indexed array single column"
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
string(16) "single insert id"
int(1)
int(1)
string(18) "multiple insert id"
int(2)
string(6) "->qf()"
array(4) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(7) "Title 1"
  ["description"]=>
  string(13) "Description 1"
  ["value"]=>
  float(10.5)
}
string(12) "->qf(..., 2)"
array(4) {
  ["id"]=>
  int(2)
  ["title"]=>
  string(7) "Title 2"
  ["description"]=>
  string(13) "Description 2"
  ["value"]=>
  float(11.5)
}
string(7) "->qfs()"
int(1)
string(7) "->qfa()"
array(3) {
  [0]=>
  array(4) {
    ["id"]=>
    int(1)
    ["title"]=>
    string(7) "Title 1"
    ["description"]=>
    string(13) "Description 1"
    ["value"]=>
    float(10.5)
  }
  [1]=>
  array(4) {
    ["id"]=>
    int(2)
    ["title"]=>
    string(7) "Title 2"
    ["description"]=>
    string(13) "Description 2"
    ["value"]=>
    float(11.5)
  }
  [2]=>
  array(4) {
    ["id"]=>
    int(3)
    ["title"]=>
    string(7) "Title 3"
    ["description"]=>
    string(13) "Description 3"
    ["value"]=>
    float(12.5)
  }
}
string(8) "->qfas()"
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
}
string(12) "columns list"
array(4) {
  [0]=>
  string(2) "id"
  [1]=>
  string(5) "title"
  [2]=>
  string(11) "description"
  [3]=>
  string(5) "value"
}
string(23) "columns list like title"
array(1) {
  [0]=>
  string(5) "title"
}
string(23) "columns list like titl%"
array(1) {
  [0]=>
  string(5) "title"
}
string(11) "tables list"
array(14) {
  [0]=>
  string(4) "test"
  [1]=>
  string(10) "xyz_config"
  [2]=>
  string(10) "xyz_groups"
  [3]=>
  string(22) "xyz_groups_permissions"
  [4]=>
  string(8) "xyz_keys"
  [5]=>
  string(15) "xyz_permissions"
  [6]=>
  string(12) "xyz_sessions"
  [7]=>
  string(12) "xyz_sign_ins"
  [8]=>
  string(9) "xyz_texts"
  [9]=>
  string(14) "xyz_texts_data"
  [10]=>
  string(9) "xyz_users"
  [11]=>
  string(14) "xyz_users_data"
  [12]=>
  string(16) "xyz_users_groups"
  [13]=>
  string(21) "xyz_users_permissions"
}
string(21) "tables list like test"
array(1) {
  [0]=>
  string(4) "test"
}
string(31) "tables list like [prefix]users%"
array(4) {
  [0]=>
  string(9) "xyz_users"
  [1]=>
  string(14) "xyz_users_data"
  [2]=>
  string(16) "xyz_users_groups"
  [3]=>
  string(21) "xyz_users_permissions"
}
string(37) "transaction for deletion: rollback #1"
int(2)
string(26) "thrown exception Exception"
string(37) "transaction for deletion: rollback #2"
int(2)
string(26) "thrown exception Exception"
string(58) "transaction for deletion: rollback #3 (nested transaction)"
int(2)
string(26) "thrown exception Exception"
string(58) "transaction for deletion: rollback #4 (nested transaction)"
int(2)
string(32) "transaction for deletion: commit"
bool(false)
