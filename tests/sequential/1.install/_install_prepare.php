<?php
namespace cs;
use
	Phar;

$root   = __DIR__.'/../../..';
$target = __DIR__.'/../../cscms.travis';

require_once "$root/tests/code_coverage.php";

require_once "$root/build/Builder.php";
require_once "$root/core/thirdparty/nazarpc/BananaHTML.php";
require_once "$root/core/classes/h/Base.php";
require_once "$root/core/classes/h.php";
require_once "$root/core/thirdparty/upf.php";

if (is_dir($target)) {
	exec("rm -r $target");
}
$version = json_decode(file_get_contents(__DIR__.'/../../../modules/System/meta.json'), true)['version'];
(new Builder($root, $target))->core();
rename("$target/CleverStyle_Framework_$version.phar.php", "$target/distributive.phar.php");
/**
 * Hack distributive to use source files instead of files from distributive and inject code coverage analysis
 */
$phar = new Phar("$target/distributive.phar.php");
$phar->addFromString(
	'cli.php',
	/** @lang PHP */
	<<<PHP
<?php
require '$target/../code_coverage.php';
require '$root/install/cli.php';
PHP
);
$phar->addFromString(
	'web.php',
	/** @lang PHP */
	<<<PHP
<?php
require '$target/../code_coverage.php';
require '$root/install/web.php';
PHP
);
unset($phar);

chdir($target);
