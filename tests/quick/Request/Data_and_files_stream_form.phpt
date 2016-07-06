--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
stream_wrapper_register('request-file', Request\File_stream::class);
$Request = Request::instance();
$Request->init_server(
	[
		'CONTENT_TYPE' => 'application/x-www-form-urlencoded'
	]
);
$json = http_build_query(
	[
		'd1' => 'v1',
		'd2' => [
			'd2-nested' => 'v2n'
		]
	]
);
var_dump('Form, copy stream');
$stream = fopen('php://temp', 'w+b');
fwrite($stream, $json);
$Request->init_data_and_files([], [], $stream);
var_dump($Request->data);

var_dump('Form, do not copy stream');
$stream = fopen('php://temp', 'w+b');
fwrite($stream, $json);
$Request->init_data_and_files([], [], $stream, false);
var_dump($Request->data);
?>
--EXPECT--
string(17) "Form, copy stream"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
string(24) "Form, do not copy stream"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
