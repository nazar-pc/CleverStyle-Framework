--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
stream_wrapper_register('request-file', Request\File_stream::class);
$data          = '0123456789abcdef';
$stream_direct = fopen('php://temp', 'w+b');
fwrite($stream_direct, $data);
rewind($stream_direct);
$stream_nested = fopen('php://temp', 'w+b');
fwrite($stream_nested, $data);
rewind($stream_nested);
Request::instance_stub(
	[
		'files' => [
			'direct' => [
				'stream' => $stream_direct
			],
			'nested' => [
				0 => [
					'stream' => $stream_direct
				]
			]
		]
	]
);

var_dump('Direct');
var_dump(file_get_contents('request-file:///direct'));
$stream = fopen('request-file:///direct', 'rb');
var_dump(fread($stream, 3));
var_dump(ftell($stream));
var_dump(fseek($stream, 2, SEEK_SET)); // TODO: Seeking fails because of https://bugs.php.net/bug.php?id=72561, should return 0 here and 4 two lines below
var_dump(fread($stream, 2));
var_dump(ftell($stream));
fclose($stream);

var_dump('Nested');
var_dump(file_get_contents('request-file:///nested/0'));
$stream = fopen('request-file:///nested/0', 'rb');
var_dump(fread($stream, 3));
var_dump(ftell($stream));
var_dump(fseek($stream, 2, SEEK_SET)); // TODO: Seeking fails because of https://bugs.php.net/bug.php?id=72561, should return 0 here and 4 two lines below
var_dump(fread($stream, 2));
var_dump(ftell($stream));
fclose($stream);

var_dump('Bad mode');
var_dump(file_put_contents('request-file:///direct', 1));
var_dump(fopen('request-file:///direct', 'wb'));

var_dump('Non-existing file');
var_dump(file_put_contents('request-file:///non_existing', 1));
var_dump(fopen('request-file:///non_existing', 'r'));
?>
--EXPECTF--
string(6) "Direct"
string(16) "0123456789abcdef"
string(3) "012"
int(3)
int(-1)
string(2) "23"
int(5)
string(6) "Nested"
string(16) "0123456789abcdef"
string(3) "012"
int(3)
int(-1)
string(2) "23"
int(5)
string(8) "Bad mode"
PHP Warning:  file_put_contents(request-file:///direct): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d

Warning: file_put_contents(request-file:///direct): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d
bool(false)
PHP Warning:  fopen(request-file:///direct): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d

Warning: fopen(request-file:///direct): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d
bool(false)
string(17) "Non-existing file"
PHP Warning:  file_put_contents(request-file:///non_existing): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d

Warning: file_put_contents(request-file:///non_existing): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d
bool(false)
PHP Warning:  fopen(request-file:///non_existing): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d

Warning: fopen(request-file:///non_existing): failed to open stream: "cs\Request\File_stream::stream_open" call failed in %s/tests/quick/Request/__code.php on line %d
bool(false)
