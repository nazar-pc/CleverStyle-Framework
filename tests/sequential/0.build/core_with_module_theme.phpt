--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
if (getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi engine');
}
?>
--INI--
phar.readonly = Off
--ARGS--
-M core -m Blogs,System,Non_existing -t DarkEnergy,CleverStyle
--FILE--
<?php
include __DIR__.'/../../code_coverage.php';
include __DIR__.'/../../../build.php';
$version = json_decode(file_get_contents(__DIR__.'/../../../modules/System/meta.json'), true)['version'];
var_dump(file_get_contents('phar://'.__DIR__."/../../../CleverStyle_Framework_$version.phar.php/modules.json"));
var_dump(file_get_contents('phar://'.__DIR__."/../../../CleverStyle_Framework_$version.phar.php/themes.json"));
?>
--EXPECTF--
Done! CleverStyle Framework %s+build-%d
string(9) "["Blogs"]"
string(14) "["DarkEnergy"]"
--CLEAN--
<?php
$version = json_decode(file_get_contents(__DIR__.'/../../../modules/System/meta.json'), true)['version'];
unlink(__DIR__."/../../../CleverStyle_Framework_$version.phar.php");
