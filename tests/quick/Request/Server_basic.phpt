--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
$server = [
	'HTTP_HOST'            => 'cscms.travis',
	'HTTP_ACCEPT_LANGUAGE' => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'          => 'cscms.travis',
	'SERVER_PROTOCOL'      => 'HTTP/1.1',
	'REQUEST_METHOD'       => 'GET',
	'QUERY_STRING'         => '',
	'REQUEST_URI'          => '/',
	'CONTENT_TYPE'         => 'text/html'
];
var_dump('Basic');
$Request = Request::instance();
$Request->init_server($server);
var_dump(
	$Request->method,
	$Request->host,
	$Request->scheme,
	$Request->secure,
	$Request->protocol,
	$Request->path,
	$Request->query_string,
	$Request->remote_addr,
	$Request->ip,
	$Request->headers,
	$Request->header('Accept-Language'),
	$Request->header('Content-Type'),
	$Request->header('xyz')
);

var_dump('Various host configurations (http)');
$Request->init_server(['HTTP_HOST' => 'cscms.travis:80'] + $server);
var_dump($Request->host);
$Request->init_server(['HTTP_HOST' => 'cscms.travis:8080'] + $server);
var_dump($Request->host);
$Request->init_server(['SERVER_NAME' => '',] + $server);
var_dump($Request->host);
$Request->init_server(
	[
		'SERVER_NAME'           => '',
		'HTTP_X_FORWARDED_HOST' => 'foo.bar'
	] + $server
);
var_dump($Request->host);
$Request->init_server(
	[
		'SERVER_NAME'           => '',
		'HTTP_X_FORWARDED_HOST' => 'foo.bar',
		'HTTP_X_FORWARDED_PORT' => 80
	] + $server
);
var_dump($Request->host);
$Request->init_server(
	[
		'SERVER_NAME'           => '',
		'HTTP_X_FORWARDED_HOST' => 'foo.bar',
		'HTTP_X_FORWARDED_PORT' => 8080
	] + $server
);
var_dump($Request->host);

var_dump('Bad characters stripped from host');
$Request->init_server(['HTTP_HOST' => 'cscms.travis&%$'] + $server);
var_dump($Request->host);
$Request->init_server(['HTTP_HOST' => '&%$'] + $server);
var_dump($Request->host);
$Request->init_server(['SERVER_NAME' => '&%$'] + $server);
var_dump($Request->host);

var_dump('Https');
$Request->init_server(['https' => 'on'] + $server);
var_dump($Request->scheme, $Request->secure);
$Request->init_server(['https' => 'off'] + $server);
var_dump($Request->scheme, $Request->secure);
$Request->init_server(['REQUEST_SCHEME' => 'https'] + $server);
var_dump($Request->scheme, $Request->secure);
$Request->init_server(['HTTP_X_FORWARDED_PROTO' => 'https'] + $server);
var_dump($Request->scheme, $Request->secure);
$Request->init_server(['HTTP_FORWARDED' => 'proto=https'] + $server);
var_dump($Request->scheme, $Request->secure);

var_dump('/index.php prefix');
$Request->init_server(['REQUEST_URI' => '/index.php/Hello'] + $server);

var_dump('IP');
$Request->init_server(['HTTP_X_FORWARDED_FOR' => '99.99.99.99'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_CLIENT_IP' => '99.99.99.99'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_X_FORWARDED' => '99.99.99.99'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_X_CLUSTER_CLIENT_IP' => '99.99.99.99'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_FORWARDED_FOR' => '99.99.99.99'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_FORWARDED' => 'for=99.99.99.99'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_X_FORWARDED_FOR' => '99.99.99.99, 88.88.88.88, 77.77.77.77'] + $server);
var_dump($Request->ip);
$Request->init_server(['HTTP_X_FORWARDED_FOR' => '999.99.99.99, 88.88.88.88, 77.77.77.77'] + $server);
var_dump($Request->ip);

$Request->init_server(
	[
		'HTTP_X_FORWARDED_FOR' => '999.99.99.99',
		'HTTP_CLIENT_IP'       => '99.99.99.99'
	] + $server
);
var_dump($Request->ip);
?>
--EXPECT--
string(5) "Basic"
string(3) "GET"
string(12) "cscms.travis"
string(4) "http"
bool(false)
string(8) "HTTP/1.1"
string(1) "/"
string(0) ""
string(9) "127.0.0.1"
string(9) "127.0.0.1"
array(3) {
  ["host"]=>
  string(12) "cscms.travis"
  ["accept-language"]=>
  string(20) "en-us;q=0.5,en;q=0.3"
  ["content-type"]=>
  string(9) "text/html"
}
string(20) "en-us;q=0.5,en;q=0.3"
string(9) "text/html"
string(0) ""
string(34) "Various host configurations (http)"
string(12) "cscms.travis"
string(17) "cscms.travis:8080"
string(12) "cscms.travis"
string(7) "foo.bar"
string(7) "foo.bar"
string(12) "foo.bar:8080"
string(33) "Bad characters stripped from host"
string(12) "cscms.travis"
string(12) "cscms.travis"
string(0) ""
string(5) "Https"
string(4) "http"
bool(false)
string(4) "http"
bool(false)
string(5) "https"
bool(true)
string(5) "https"
bool(true)
string(5) "https"
bool(true)
string(17) "/index.php prefix"
string(2) "IP"
string(11) "99.99.99.99"
string(11) "99.99.99.99"
string(11) "99.99.99.99"
string(11) "99.99.99.99"
string(11) "99.99.99.99"
string(11) "99.99.99.99"
string(11) "99.99.99.99"
string(9) "127.0.0.1"
string(11) "99.99.99.99"
