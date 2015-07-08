<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
list($protocol, $host) = explode('://', $argv[1], 2);
$host = explode('/', $host, 2)[0];
$ROOT = realpath(__DIR__.'/../../..');
/**
 * Simulate headers of regular request
 */
$_SERVER = [
	'HTTP_HOST'              => $host,
	'HTTP_USER_AGENT'        => 'CleverStyle CMS WebSockets module',
	'SERVER_NAME'            => explode(':', $host)[0],
	'REMOTE_ADDR'            => '127.0.0.1',
	'DOCUMENT_ROOT'          => $ROOT,
	'SERVER_PROTOCOL'        => 'HTTP/1.1',
	'REQUEST_METHOD'         => 'GET',
	'QUERY_STRING'           => '',
	'REQUEST_URI'            => '/WebSockets',
	'HTTP_X_FORWARDED_PROTO' => $protocol
];
if (isset($argv[2])) {
	$_GET['address'] = $argv[2];
}
require "$ROOT/index.php";
