--SKIPIF--
<?php
if (getenv('DB') != 'SQLite') {
	exit('skip only running for database SQLite driver');
}
?>
--INI--
phar.readonly = Off
--FILE--
<?php
include __DIR__.'/_install_prepare.php';
system(PHP_BINARY." -d variables_order=EGPCS -d xdebug.default_enable=0 distributive.phar.php -sn Web-site -su http://cscms.travis -dh storage/sqlite.db -dn \"\" -du \"\" -dp \"\" -dr \"xyz_\" -ae admin@cscms.travis -ap 1111 -de $_ENV[DB]");
?>
--CLEAN--
<?php
// Remove php config because its parameters anyway will be declared by custom loader or tests or not used at all
file_put_contents(__DIR__.'/../../cscms.travis/config/main.php', '');
?>
--EXPECT--
Congratulations! CleverStyle Framework has been installed successfully!

Login: admin
Password: 1111
