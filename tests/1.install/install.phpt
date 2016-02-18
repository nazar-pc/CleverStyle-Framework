--TEST--
Installation of distributive
--ARGS--
-sn Web-site -su http://cscms.travis -dh 127.0.0.1 -dn cscms.travis -du travis -dp "" -dr 'xyz_' -ae admin@cscms.travis -ap 1111
--FILE--
<?php
$root		= __DIR__.'/../..';
$version	= json_decode(file_get_contents("$root/components/modules/System/meta.json"), true)['version'];
if (is_dir("$root/cscms.travis")) {
	exec("rm -r $root/cscms.travis");
}
mkdir("$root/cscms.travis");
rename("$root/CleverStyle_CMS_$version.phar.php", "$root/cscms.travis/distributive.phar.php");
// For correct self-removing of distributive
$argv[0] = 'distributive.phar.php';
chdir("$root/cscms.travis");
include "$root/cscms.travis/distributive.phar.php";
// Remove php config because its parameters anyway will be declared by custom loader or tests or not used at all
unlink("$root/cscms.travis/config/main.php");
?>
--EXPECT--
Congratulations! CleverStyle CMS has been installed successfully!

Login: admin
Password: 1111
