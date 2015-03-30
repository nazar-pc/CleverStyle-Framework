<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Http_server;
if (version_compare(PHP_VERSION, '5.4', '<')) {
	echo 'CleverStyle CMS require PHP 5.4 or higher';
	return;
}

/**
 * Running Http server in background on any platform
 *
 * @param int  $port
 * @param bool $async
 */
function cross_platform_server_in_background ($port, $async) {
	$exec       = defined('HHVM_VERSION') ? 'hhvm' : 'php';
	$async      = $async ? '-a' : '';
	$supervisor = 'php '.__DIR__.'/supervisor.php';
	$cmd        = "$exec ".__DIR__."/run_server.php -p $port $async";
	if (substr(PHP_OS, 0, 3) != 'WIN') {
		exec("$supervisor '$cmd' > /dev/null &");
	} else {
		pclose(popen("start /B $supervisor '$cmd'", 'r'));
	}
}

$async = false;
for ($i = 1; isset($argv[$i]); ++$i) {
	switch ($argv[$i]) {
		case '-p':
			$port = $argv[++$i];
			break;
		case '-a':
			$async = true;
	}
}
if (!isset($port)) {
	echo 'Http server for CleverStyle CMS
Usage: php components/modules/Http_server/run_server.php -p <port> [-a]
  -p - Is used to specify on which port server should listen for incoming connections, can be number, range or
       coma-separated number or range (8080. 8080-8081 or 8080,8081,9000-9005)
  -a - Prepare server for asynchronous processing (decrease system optimizations, but might
       be useful if other code will benefit from this), using asynchronous code without this
       option will result in unpredictable behavior
';
	return;
}

$ports = [];
foreach (explode(',', $port) as $p) {
	if (strpos($p, '-') !== false) {
		/** @noinspection SlowArrayOperationsInLoopInspection */
		$ports = array_merge($ports, call_user_func_array('range', explode('-', $p)));
	} else {
		$ports[] = $p;
	}
}
unset($p, $port);
sort($ports);
foreach ($ports as $p) {
	cross_platform_server_in_background($p, $async);
}
echo "Pool of Http servers started!\n";
