<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
define('DIR',		__DIR__);
define('TEST',		DIR.'/test');
define('SUITES',	TEST.'/suites');
require_once DIR.'/core/upf.php';
require_once DIR.'/core/functions.php';
date_default_timezone_set('UTC');
ini_set('error_log', TEST.'/log');
if (isset($_GET['suite'], $_GET['test'], $_GET['key'])) {
	if (@file_get_contents(TEST.'/key' != $_GET['key'])) {
		exit('0');
	}
	exit((string)require SUITES."/$_GET[suite]/$_GET[test]");
}
/**
 * Ignore time limit
 */
time_limit_pause();
/**
 * Generate random key (for security reasons)
 */
file_put_contents(
	TEST.'/key',
	$key = hash('sha512', microtime(true).uniqid('test', true))
);
/**
 * If executed from command line
 */
if (PHP_SAPI == 'cli') {
	/**
	 * Start embedded php web-server in current directory
	 */
	$web_server_pid = exec('php -S localhost:8001 >/dev/null & echo $!');
	/**
	 * Wait 500 ms for web server starting
	 */
	usleep(500000);
	$base_url	= 'http://localhost:8001';
} else {
	$base_url	= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http')."://$_SERVER[HTTP_HOST]";
}
/**
 * Include testify library
 */
require_once TEST.'/testify/testify.class.php';
$tf			= new Testify('CleverStyle CMS Test Suite');
/**
 * Look through all suites, and run all tests of each suite
 */
foreach (get_files_list(SUITES, false, 'd') ?: [] as $suite) {
	/**
	 * Check for map file
	 */
	if (!file_exists(SUITES."/$suite/suite.json")) {
		continue;
	}
	$suite	= _json_decode(file_get_contents(SUITES."/$suite/suite.json"));
	$tf->test($suite['title'], function (Testify $tf) use ($base_url, $suite, $key) {
		foreach ($suite['tests'] as $test => $title) {
			$tf->assert(file_get_contents("$base_url/test.php?suite=$suite&test=$test&key=$key") === '1', $title);
		}
	});
}
/**
 * Run testing
 */
$tf->run();
if (isset($web_server_pid)) {
	/**
	 * Stop embedded php web server
	 */
	exec("kill $web_server_pid");
}
/**
 * Delete key
 */
@unlink(TEST.'/key');
exit(0);