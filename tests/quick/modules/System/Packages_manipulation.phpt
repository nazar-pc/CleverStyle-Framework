--FILE--
<?php
namespace cs\modules\System;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Mock_object;
use function
	cs\make_tmp_dir;

require_once __DIR__.'/../../../../modules/System/Packages_manipulation.php';
include __DIR__.'/../../../unit.php';
define('MODULES', make_tmp_dir());
define('TMP', make_tmp_dir());
define('TMP_READONLY', make_tmp_dir());
chmod(TMP_READONLY, 0555);

copy(__DIR__.'/Packages_manipulation/module_Test_module_1.0.0+build-1.phar.php', TMP.'/v1.phar.php');
copy(__DIR__.'/Packages_manipulation/module_Test_module_2.0.0+build-2.phar.php', TMP.'/v2.phar.php');

Core::instance_stub(
	[
		'db_type' => 'SQLite'
	]
);
Config::instance_stub();
DB::instance_stub(
	[],
	[
		'db_prime' => function () {
			$db = new Mock_object(
				[],
				[
					'transaction' => function ($callback) use (&$db) {
						var_dump('Transaction created');
						$callback($db);
					},
					'q' => function (...$arguments) {
						var_dump('SQL query with arguments', $arguments);
					}
				],
				[]
			);
			return $db;
		}
	]
);

var_dump('Install extract (directory not available for writing)');
var_dump(Packages_manipulation::install_extract(TMP_READONLY.'/Test_module', TMP.'/v1.phar.php'));

var_dump('Install extract');
var_dump(Packages_manipulation::install_extract(MODULES.'/Test_module', TMP.'/v1.phar.php'));
var_dump(file_get_json(MODULES.'/Test_module/fs.json'));

var_dump('Update extract (files extraction failed)');
chmod(MODULES.'/Test_module/meta.json', 0444);
var_dump(Packages_manipulation::update_extract(MODULES.'/Test_module', TMP.'/v2.phar.php'));

var_dump('Update extract');
chmod(MODULES.'/Test_module/meta.json', 0664);
var_dump(Packages_manipulation::update_extract(MODULES.'/Test_module', TMP.'/v2.phar.php'));
var_dump(file_get_json(MODULES.'/Test_module/fs.json'));
var_dump(file_get_json(MODULES.'/Test_module/fs_backup.json'));
var_dump(file_get_json(MODULES.'/Test_module/meta.json'));
var_dump(file_get_json(MODULES.'/Test_module/meta_backup.json'));
var_dump(file_exists(MODULES.'/Test_module/dir1/file1'));
var_dump(file_exists(MODULES.'/Test_module/index.php'));

var_dump('Execute PHP and SQL update scripts');
Packages_manipulation::update_php_sql(MODULES.'/Test_module', '1.0.0+build-1', ['test' => 0]);

?>
--EXPECTF--
string(53) "Install extract (directory not available for writing)"
%a
bool(false)
string(15) "Install extract"
bool(true)
array(3) {
  [0]=>
  string(10) "dir1/file1"
  [1]=>
  string(9) "index.php"
  [2]=>
  string(9) "meta.json"
}
string(40) "Update extract (files extraction failed)"
%a
bool(false)
string(14) "Update extract"
bool(true)
array(4) {
  [0]=>
  string(9) "meta.json"
  [1]=>
  string(19) "meta/update/1.1.php"
  [2]=>
  string(19) "meta/update/1.5.php"
  [3]=>
  string(34) "meta/update_db/test/1.3/SQLite.sql"
}
array(3) {
  [0]=>
  string(10) "dir1/file1"
  [1]=>
  string(9) "index.php"
  [2]=>
  string(9) "meta.json"
}
array(3) {
  ["package"]=>
  string(11) "Test_module"
  ["category"]=>
  string(7) "modules"
  ["version"]=>
  string(13) "2.0.0+build-2"
}
array(3) {
  ["package"]=>
  string(11) "Test_module"
  ["category"]=>
  string(7) "modules"
  ["version"]=>
  string(13) "1.0.0+build-1"
}
bool(false)
bool(false)
string(34) "Execute PHP and SQL update scripts"
Update script 1.1
string(19) "Transaction created"
string(24) "SQL query with arguments"
array(1) {
  [0]=>
  array(1) {
    [0]=>
    string(36) "UPDATE `[prefix]test` SET `one` = 1
"
  }
}
Update script 1.5
