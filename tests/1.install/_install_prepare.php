<?php
$root   = __DIR__.'/../..';
$target = __DIR__.'/../../cscms.travis';
require_once "$root/build/Builder.php";
require_once "$root/core/thirdparty/nazarpc/BananaHTML.php";
require_once "$root/core/classes/h/Base.php";
require_once "$root/core/classes/h.php";
require_once "$root/core/thirdparty/upf.php";
if (is_dir($target)) {
	exec("rm -r $target");
}
$version = json_decode(file_get_contents(__DIR__.'/../../components/modules/System/meta.json'), true)['version'];
(new \cs\Builder($root, $target))->core();
rename("$target/CleverStyle_Framework_$version.phar.php", "$target/distributive.phar.php");
chdir($target);
