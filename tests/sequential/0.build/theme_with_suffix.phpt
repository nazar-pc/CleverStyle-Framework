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
-M theme -t DarkEnergy -s Suffix
--FILE--
<?php
include __DIR__.'/../../code_coverage.php';
include __DIR__.'/../../../build.php';
?>
--EXPECTF--
Done! Theme DarkEnergy %s+build-%d
--CLEAN--
<?php
$version = json_decode(file_get_contents(__DIR__.'/../../../themes/DarkEnergy/meta.json'), true)['version'];
unlink(__DIR__."/../../../theme_DarkEnergy_{$version}_Suffix.phar.php");
