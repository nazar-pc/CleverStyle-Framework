--SKIPIF--
<?php
if (getenv('DB') && getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi engine');
}
?>
--INI--
phar.readonly = Off
--FILE--
<?php
$arguments = '-sn Web-site -su http://cscms.travis -dh 127.0.0.1 -dn travis -du travis -dp "" -dr \'xyz_\' -ae admin@cscms.travis -ap 1111';
include __DIR__.'/_install.php';
?>
--EXPECT--
Congratulations! CleverStyle Framework has been installed successfully!

Login: admin
Password: 1111
