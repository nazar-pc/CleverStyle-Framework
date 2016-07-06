--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
stream_wrapper_register('request-file', Request\File_stream::class);
$Request = Request::instance();
$Request->init_server(
	[
		'REQUEST_METHOD' => 'POST',
		'CONTENT_TYPE'   => 'application/json'
	]
);
$json = json_encode(
	[
		'd1' => 'v1',
		'd2' => [
			'd2-nested' => 'v2n'
		]
	],
	JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
var_dump('JSON, copy stream');
$stream = fopen('php://temp', 'w+b');
fwrite($stream, $json);
$Request->init_data_and_files([], [], $stream);
var_dump($Request->data);

var_dump('JSON, do not copy stream');
$stream = fopen('php://temp', 'w+b');
fwrite($stream, $json);
$Request->init_data_and_files([], [], $stream, false);
var_dump($Request->data);
?>
--EXPECT--
string(17) "JSON, copy stream"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
string(24) "JSON, do not copy stream"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
