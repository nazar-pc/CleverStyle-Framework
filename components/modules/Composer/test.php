<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	Composer\Console\Application,
	Symfony\Component\Console\Input\ArrayInput,
	Symfony\Component\Console\Output\BufferedOutput;
$DIR = __DIR__;
require_once "phar://$DIR/composer.phar/src/bootstrap.php";
require_once "$DIR/ansispan.php";
@mkdir("$DIR/home");
putenv("COMPOSER_HOME=$DIR/home");
time_limit_pause();
@ini_set('display_errors', 1);
@ini_set('memory_limit', '512M');
$application = new Application;
//TODO
$input       = new ArrayInput([
	'command'       => 'require',
	'--working-dir' => $DIR,
	'packages'      => [],
	'--ansi',
	'--no-interaction'
]);
$output      = new BufferedOutput;
$application->setAutoExit(false);
$application->run($input, $output);
echo ansispan($output->fetch());
time_limit_pause(false);
$dirs_to_rm = [];
get_files_list(
	"$DIR/home",
	false,
	'fd',
	true,
	true,
	false,
	false,
	true,
	function ($item) {
		if (is_dir($item)) {
			@rmdir($item);
		} else {
			@unlink($item);
		}
	}
);
@rmdir("$DIR/home");
