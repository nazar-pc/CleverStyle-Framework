<?php
require_once __DIR__.'/../../build/Builder.php';
require_once __DIR__.'/../../core/thirdparty/nazarpc/BananaHTML.php';
require_once __DIR__.'/../../core/classes/h/Base.php';
require_once __DIR__.'/../../core/classes/h.php';
require_once __DIR__.'/../../core/thirdparty/upf.php';
$root   = __DIR__.'/../..';
$target = __DIR__.'/../../cscms.travis';
if (is_dir($target)) {
	exec("rm -r $target");
}
$version = json_decode(file_get_contents(__DIR__.'/../../components/modules/System/meta.json'), true)['version'];
(new \cs\Builder($root, $target))->core();
rename(__DIR__."/../../cscms.travis/CleverStyle_CMS_$version.phar.php", __DIR__.'/../../cscms.travis/distributive.phar.php');
chdir($target);
system(PHP_BINARY." distributive.phar.php $arguments");
// Remove php config because its parameters anyway will be declared by custom loader or tests or not used at all
file_put_contents("$target/config/main.php", '');
