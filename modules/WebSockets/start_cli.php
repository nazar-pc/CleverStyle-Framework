<?php
/**
 * @package  WebSockets
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
list($protocol, $host) = explode('://', $argv[1], 2);
$host = explode('/', $host, 2)[0];
/**
 * Simulate headers of regular request
 */
$_SERVER = [
	'HTTP_HOST'              => $host,
	'HTTP_USER_AGENT'        => 'CleverStyle Framework WebSockets module',
	'SERVER_NAME'            => explode(':', $host)[0],
	'REMOTE_ADDR'            => '127.0.0.1',
	'SERVER_PROTOCOL'        => 'HTTP/1.1',
	'REQUEST_METHOD'         => 'GET',
	'QUERY_STRING'           => '',
	'REQUEST_URI'            => '/WebSockets',
	'HTTP_X_FORWARDED_PROTO' => $protocol
];
if (isset($argv[2])) {
	$_GET['address'] = $argv[2];
}
require __DIR__.'/../../index.php';
