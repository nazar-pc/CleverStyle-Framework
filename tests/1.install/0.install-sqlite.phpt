--SKIPIF--
<?php
if (getenv('DB') != 'SQLite') {
	exit('skip only running for database SQLite engine');
}
?>
--INI--
phar.readonly = Off
--FILE--
<?php
$arguments = '-sn Web-site -su http://cscms.travis -de SQLite -dh storage/sqlite.db -dn "" -du "" -dp "" -dr "xyz_" -ae admin@cscms.travis -ap 1111';
include __DIR__.'/_install.php';
?>
--EXPECT--
Congratulations! CleverStyle CMS has been installed successfully!

Login: admin
Password: 1111
