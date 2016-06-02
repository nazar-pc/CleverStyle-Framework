<?php
$_SERVER = [
	'HTTP_HOST'            => 'cscms.travis',
	'HTTP_ACCEPT_LANGUAGE' => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'          => 'cscms.travis',
	'SERVER_PROTOCOL'      => 'HTTP/1.1',
	'REQUEST_METHOD'       => 'GET',
	'QUERY_STRING'         => '',
	'REQUEST_URI'          => '/web.php'
];
require 'phar://'.__DIR__.'/../../cscms.travis/distributive.phar.php/web.php';
