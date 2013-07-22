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
define('TEMP',		TEST.'/temp');
define('SUITES',	TEST.'/suites');
define('CLI',		PHP_SAPI == 'cli');
require_once DIR.'/core/classes/h/_Abstract.php';
require_once DIR.'/core/classes/h.php';
require_once DIR.'/core/upf.php';
date_default_timezone_set('UTC');
ini_set('error_log', TEST.'/error.log');
if (!is_dir(TEMP)) {
	mkdir(TEMP);
}
if (isset($_GET['suite'], $_GET['test'], $_GET['key'])) {
	if (@file_get_contents(TEST.'/key' != $_GET['key'])) {
		exit('Wrong key');
	}
	_require_once(SUITES."/$_GET[suite]/prepare.php", false);
	exit((string)require SUITES."/$_GET[suite]/$_GET[test].php");
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
if (CLI) {
	/**
	 * Start embedded php web-server in current directory
	 */
	$web_server_pid = exec('php -S localhost:8001 >/dev/null 2>/dev/null & echo $!');
	/**
	 * Wait 500 ms for web server starting
	 */
	usleep(500000);
	$base_url	= 'http://localhost:8001';
} else {
	$base_url	= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http')."://$_SERVER[HTTP_HOST]";
}
$test_suites	= [];
$tests_success	= 0;
$tests_failed	= 0;
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
	$suite_data		= _json_decode(file_get_contents(SUITES."/$suite/suite.json"));
	$suite			= urlencode($suite);
	$local_success	= 0;
	$local_failed	= 0;
	$test_suites[]	= [
		'title'		=> $suite_data['title'],
		'tests'		=> array_map(
			function ($test, $title) use ($base_url, $suite, $key, &$tests_success, &$tests_failed, &$local_success, &$local_failed) {
				/**
				 * Clear temp directory before every test
				 */
				$test			= urlencode($test);
				get_files_list(
					TEMP,
					false,
					'fd',
					true,
					true,
					false,
					false,
					true,
					function ($item) {
						if (is_writable($item)) {
							is_dir($item) ? @rmdir($item) : @unlink($item);
						}
					}
				);
				/**
				 * Run single test
				 */
				$result			= file_get_contents("$base_url/test.php?suite=$suite&test=$test&key=$key");
				if ($result === '0') {
					++$tests_success;
					++$local_success;
				} else {
					++$tests_failed;
					++$local_failed;
				}
				return	[
					'title'			=> $title,
					'result'		=> $result === '0',
					'result_text'	=> $result
				];
			},
			array_keys($suite_data['tests']),
			$suite_data['tests']
		),
		'success'	=> $local_success,
		'failed'	=> $local_failed
	];
}
unset($suite, $suite_data, $local_success, $local_failed);
/**
 * Clear temp directory after all tests
 */
get_files_list(
	TEMP,
	false,
	'fd',
	true,
	true,
	false,
	false,
	true,
	function ($item) {
		if (is_writable($item)) {
			is_dir($item) ? @rmdir($item) : @unlink($item);
		}
	}
);
/**
 * Stop embedded php web server
 */
if (isset($web_server_pid)) {
	exec("kill $web_server_pid");
}
unset($web_server_pid);
/**
 * Show results
 */
if (CLI) {
	require TEST.'/result/cli.php';
} else {
	require TEST.'/result/html.php';
}
/**
 * Delete key
 */
@unlink(TEST.'/key');
exit($tests_failed);