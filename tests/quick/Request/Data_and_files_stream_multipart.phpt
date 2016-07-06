--INI--
post_max_size = 1G
--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
stream_wrapper_register('request-file', Request\File_stream::class);
$Request = Request::instance();
$Request->init_server(
	[
		'REQUEST_METHOD' => 'POST',
		'CONTENT_TYPE'   => 'multipart/form-data; boundary=----8V0y5s0FruAd7840'
	]
);
var_dump('Multipart, copy stream');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body.bin', 'rb'));
var_dump($Request->data);
var_dump($Request->files);

var_dump('File read from fs');
var_dump(file_get_contents($Request->files['single_file']['tmp_name']));

var_dump('File read from stream');
rewind($Request->files['single_file']['stream']);
var_dump(stream_get_contents($Request->files['single_file']['stream']));

var_dump('Files read from fs (multiple)');
var_dump(file_get_contents($Request->files['multiple_files'][0]['tmp_name']));
var_dump(file_get_contents($Request->files['multiple_files'][1]['tmp_name']));

var_dump('Files read from stream (multiple)');
rewind($Request->files['multiple_files'][0]['stream']);
var_dump(stream_get_contents($Request->files['multiple_files'][0]['stream']));
rewind($Request->files['multiple_files'][1]['stream']);
var_dump(stream_get_contents($Request->files['multiple_files'][1]['stream']));
?>
--EXPECTF--
string(22) "Multipart, copy stream"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    [0]=>
    array(1) {
      ["d2-nested"]=>
      string(3) "v2n"
    }
  }
}
array(2) {
  ["single_file"]=>
  array(6) {
    ["name"]=>
    string(12) "upload1.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(17)
    ["stream"]=>
    resource(%d) of type (stream)
    ["error"]=>
    int(0)
    ["tmp_name"]=>
    string(27) "request-file:///single_file"
  }
  ["multiple_files"]=>
  array(2) {
    [0]=>
    array(6) {
      ["name"]=>
      string(12) "upload1.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(17)
      ["stream"]=>
      resource(%d) of type (stream)
      ["error"]=>
      int(0)
      ["tmp_name"]=>
      string(32) "request-file:///multiple_files/0"
    }
    [1]=>
    array(6) {
      ["name"]=>
      string(12) "upload2.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(11)
      ["stream"]=>
      resource(%d) of type (stream)
      ["error"]=>
      int(0)
      ["tmp_name"]=>
      string(32) "request-file:///multiple_files/1"
    }
  }
}
string(17) "File read from fs"
string(17) "0123456789abcdef
"
string(21) "File read from stream"
string(17) "0123456789abcdef
"
string(29) "Files read from fs (multiple)"
string(17) "0123456789abcdef
"
string(11) "0123456789
"
string(33) "Files read from stream (multiple)"
string(17) "0123456789abcdef
"
string(11) "0123456789
"
