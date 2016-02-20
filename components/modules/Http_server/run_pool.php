<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Http_server;
/**
 * Running Http server in background on any platform
 *
 * @param int  $port
 */
function cross_platform_server_in_background ($port) {
	$exec       = defined('HHVM_VERSION') ? 'hhvm' : 'php';
	$supervisor = 'php '.__DIR__.'/supervisor.php';
	$cmd        = "$exec ".__DIR__."/run_server.php -p $port";
	if (substr(PHP_OS, 0, 3) != 'WIN') {
		exec("$supervisor '$cmd' > /dev/null &");
	} else {
		pclose(popen("start /B $supervisor '$cmd'", 'r'));
	}
}

for ($i = 1; isset($argv[$i]); ++$i) {
	switch ($argv[$i]) {
		case '-p':
			$port = $argv[++$i];
			break;
	}
}
if (!isset($port)) {
	echo 'Http server for CleverStyle CMS
Usage: php components/modules/Http_server/run_server.php -p <port> [-a]
  -p - Is used to specify on which port server should listen for incoming connections, can be number, range or
       coma-separated number or range (8080. 8080-8081 or 8080,8081,9000-9005)
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
	cross_platform_server_in_background($p);
}
echo "Pool of Http servers started!\n";
