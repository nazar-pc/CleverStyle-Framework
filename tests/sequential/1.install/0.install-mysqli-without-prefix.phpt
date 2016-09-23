--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
if (getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi driver');
}
?>
--INI--
phar.readonly = Off
--FILE--
<?php
include __DIR__.'/_install_prepare.php';
system(PHP_BINARY." -d variables_order=EGPCS -d xdebug.default_enable=0 distributive.phar.php -sn Web-site -su http://cscms.travis -dh 127.0.0.1 -dn travis -du travis -dp \"\" -ae admin@cscms.travis -ap 1111 -de $_ENV[DB]");
?>
--CLEAN--
<?php
include __DIR__.'/../_clean.php';
?>
--EXPECT--
Congratulations! CleverStyle Framework has been installed successfully!

Login: admin
Password: 1111
