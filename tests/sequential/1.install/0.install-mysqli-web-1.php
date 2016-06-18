<?php
$_SERVER = [
	'HTTP_HOST'            => 'cscms.travis',
	'HTTP_ACCEPT_LANGUAGE' => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'          => 'cscms.travis',
	'SERVER_PROTOCOL'      => 'HTTP/1.1',
	'REQUEST_METHOD'       => 'POST',
	'QUERY_STRING'         => '',
	'REQUEST_URI'          => '/web.php'
];
$_POST   = [
	'site_name'      => 'Web-site',
	'timezone'       => 'UTC',
	'db_host'        => '127.0.0.1',
	'db_engine'      => 'MySQLi',
	'db_name'        => 'travis',
	'db_user'        => 'travis',
	'db_password'    => '',
	'db_prefix'      => 'xyz_',
	'language'       => 'English',
	'admin_email'    => 'admin@cscms.travis',
	'admin_password' => '1111',
	'mode'           => 1
];
$target  = realpath(__DIR__.'/../../cscms.travis');
require "phar://$target/distributive.phar.php/web.php";
