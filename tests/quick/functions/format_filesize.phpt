--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';

$L = Language::instance_stub(
	[
		'system_filesize_TiB'   => 'TiB',
		'system_filesize_GiB'   => 'GiB',
		'system_filesize_MiB'   => 'MiB',
		'system_filesize_KiB'   => 'KiB',
		'system_filesize_Bytes' => 'Bytes'
	],
	[
		'get' => function ($item, $language, $prefix) use (&$L) {
			return $L->{$prefix.$item};
		}
	]
);

var_dump(format_filesize('not a number'));

var_dump('TiB', format_filesize(25 + 1024 * 2 + 1024 * 1024 * 3 + 1024 * 1024 * 1024 * 5 + 1024 * 1024 * 1024 * 1024 * 7, 3));
var_dump('GiB', format_filesize(25 + 1024 * 2 + 1024 * 1024 * 3 + 1024 * 1024 * 1024 * 5, 3));
var_dump('MiB', format_filesize(25 + 1024 * 2 + 1024 * 1024 * 3, 3));
var_dump('KiB', format_filesize(25 + 1024 * 2, 3));
var_dump('Bytes', format_filesize(25));
?>
--EXPECT--
string(12) "not a number"
string(3) "TiB"
string(9) "7.005 TiB"
string(3) "GiB"
string(9) "5.003 GiB"
string(3) "MiB"
string(9) "3.002 MiB"
string(3) "KiB"
string(9) "2.024 KiB"
string(5) "Bytes"
string(8) "25 Bytes"
