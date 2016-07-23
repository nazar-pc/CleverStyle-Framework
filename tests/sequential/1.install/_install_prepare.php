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
 * Inject code coverage into distributive
 */
$phar                       = new Phar("$target/distributive.phar.php");
$replace_with_code_coverage = <<<PHP
namespace cs;
require '$target/../code_coverage.php';
// Keep distributive file until php-code-coverage finishes its job
function unlink (\$filename) {
	register_shutdown_function(function () use (\$filename) {
		\unlink(\$filename);
	});
	return true;
}
PHP;
$phar->addFromString(
	'cli.php',
	str_replace(
		'namespace cs;',
		$replace_with_code_coverage,
		file_get_contents("phar://$target/distributive.phar.php/cli.php")
	)
);
$phar->addFromString(
	'web.php',
	str_replace(
		'namespace cs;',
		$replace_with_code_coverage,
		file_get_contents("phar://$target/distributive.phar.php/web.php")
	)
);
unset($phar);

chdir($target);
