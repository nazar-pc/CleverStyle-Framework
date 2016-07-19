--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
include __DIR__.'/../../../core/thirdparty/cli/cli.php';
function header (...$arguments) {
	var_dump('header() called with', $arguments);
}

function http_response_code (...$arguments) {
	var_dump('http_response_code() called with', $arguments);
}

function fopen (...$arguments) {
	static $stream;
	var_dump('fopen() called with ', ...$arguments);
	if (!isset($stream)) {
		$stream = \fopen('php://output', 'wb');
	}
	return $stream;
}

function fseek () {
}

$Request  = Request::instance_stub(
	[
		'protocol' => 'HTTP/1.1',
		'cli_path' => false
	]
);
$Response = Response::instance();

var_dump('Init with typical default settings');
$Response->init_with_typical_default_settings();
var_dump($Response->protocol, $Response->code, $Response->headers, $Response->body, $Response->body_stream);

var_dump('Default output');
$Response->body = "Some content\n";
$Response->output_default();

var_dump('Default output CLI');
$Request->cli_path = true;
$Response->output_default();

var_dump('Init with stream');
$stream = \fopen('php://temp', 'w+b');
$Response->init('', $stream);
fwrite($stream, "Some content\n");

var_dump('Default output (stream)');
$Request->cli_path = false;
$Response->output_default();

var_dump('Default output CLI (stream)');
$Request->cli_path = true;
$Response->output_default();

var_dump('Init with new stream');
$stream_new = \fopen('php://temp', 'w+b');
$Response->init('', $stream_new);
var_dump($stream, $stream_new);

var_dump('Headers setting');
$Response->init();
$Response->headers = [];
$Response
	->header('test-header', 'value #1')
	->header('test-header', 'value #2')
	->header('test-header-unset', 'value');
var_dump($Response->headers);
$Response
	->header('Test-HeadeR', 'value #3', false)
	->header('test-header-unset', '');
var_dump($Response->headers);
$Request->cli_path = false;
$Response->output_default();

var_dump('Redirect (no body during output)');
$Response->headers = [];
$Response->body    = "Some content\n";
$Response->redirect('wharever URL', 301);
var_dump($Response->headers);
$Response->output_default();

var_dump('Set cookie');
Config::instance_replace(False_class::instance());
$Request->cookie   = [];
$Response->headers = [];
$Response
	->cookie('c', 'value')
	->cookie('c-expire', 'value', time() + 100500)
	->cookie('c-httponly', 'value', 0, true);
$Request->secure = true;
$Response->cookie('c-secure', 'value');
$Request->secure = false;
$Response
	->cookie('c-unset', 'value')
	->cookie('c-unset', '');
$Request->mirror_index = 0;
Config::instance_stub(
	[
		'core' => [
			'cookie_prefix' => 'cp_',
			'cookie_domain' => [
				'cscms.travis'
			]
		]
	]
);
$Response->cookie('c-with-prefix', 'value');
var_dump($Request->cookie, $Response->headers);

var_dump('CLI error exit');
$Request->cli_path = true;
$Response->code    = 404;
$Response->output_default();
?>
--EXPECTF--
string(34) "Init with typical default settings"
string(8) "HTTP/1.1"
int(200)
array(3) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(24) "text/html; charset=utf-8"
  }
  ["vary"]=>
  array(1) {
    [0]=>
    string(33) "Accept-Language,User-Agent,Cookie"
  }
  ["x-ua-compatible"]=>
  array(1) {
    [0]=>
    string(7) "IE=edge"
  }
}
string(0) ""
NULL
string(14) "Default output"
string(20) "header() called with"
array(2) {
  [0]=>
  string(38) "Content-Type: text/html; charset=utf-8"
  [1]=>
  bool(false)
}
string(20) "header() called with"
array(2) {
  [0]=>
  string(39) "Vary: Accept-Language,User-Agent,Cookie"
  [1]=>
  bool(false)
}
string(20) "header() called with"
array(2) {
  [0]=>
  string(24) "X-Ua-Compatible: IE=edge"
  [1]=>
  bool(false)
}
string(32) "http_response_code() called with"
array(1) {
  [0]=>
  int(200)
}
Some content
string(18) "Default output CLI"
Some content
string(16) "Init with stream"
string(23) "Default output (stream)"
string(32) "http_response_code() called with"
array(1) {
  [0]=>
  int(200)
}
string(20) "fopen() called with "
string(12) "php://output"
string(2) "wb"
string(27) "Default output CLI (stream)"
string(20) "fopen() called with "
string(12) "php://stdout"
string(2) "wb"
string(20) "Init with new stream"
resource(%d) of type (Unknown)
resource(%d) of type (stream)
string(15) "Headers setting"
array(2) {
  ["test-header"]=>
  array(1) {
    [0]=>
    string(8) "value #2"
  }
  ["test-header-unset"]=>
  array(1) {
    [0]=>
    string(5) "value"
  }
}
array(1) {
  ["test-header"]=>
  array(2) {
    [0]=>
    string(8) "value #2"
    [1]=>
    string(8) "value #3"
  }
}
string(20) "header() called with"
array(2) {
  [0]=>
  string(21) "Test-Header: value #2"
  [1]=>
  bool(false)
}
string(20) "header() called with"
array(2) {
  [0]=>
  string(21) "Test-Header: value #3"
  [1]=>
  bool(false)
}
string(32) "http_response_code() called with"
array(1) {
  [0]=>
  int(200)
}
string(32) "Redirect (no body during output)"
array(1) {
  ["location"]=>
  array(1) {
    [0]=>
    string(12) "wharever URL"
  }
}
string(20) "header() called with"
array(2) {
  [0]=>
  string(22) "Location: wharever URL"
  [1]=>
  bool(false)
}
string(32) "http_response_code() called with"
array(1) {
  [0]=>
  int(301)
}
string(10) "Set cookie"
array(6) {
  ["c"]=>
  string(5) "value"
  ["c-expire"]=>
  string(5) "value"
  ["c-httponly"]=>
  string(5) "value"
  ["c-secure"]=>
  string(5) "value"
  ["c-with-prefix"]=>
  string(5) "value"
  ["cp_c-with-prefix"]=>
  string(5) "value"
}
array(1) {
  ["set-cookie"]=>
  array(7) {
    [0]=>
    string(15) "c=value; path=/"
    [1]=>
    string(%d) "c-expire=value; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT"
    [2]=>
    string(34) "c-httponly=value; path=/; HttpOnly"
    [3]=>
    string(30) "c-secure=value; path=/; secure"
    [4]=>
    string(21) "c-unset=value; path=/"
    [5]=>
    string(55) "c-unset=; path=/; expires=Thu, 01-Jan-1970 00:00:00 GMT"
    [6]=>
    string(51) "cp_c-with-prefix=value; path=/; domain=cscms.travis"
  }
}
string(14) "CLI error exit"
Some content
