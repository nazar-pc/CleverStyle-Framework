--INI--
post_max_size = 5K
upload_max_filesize = 2K
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

var_dump('Bad file ending (no --)');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_bad_ending.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Bad content-disposition (absent or incorrect)');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_bad_content_disposition.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('No headers');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_no_headers.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Empty name');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_empty_name.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Empty file name');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_empty_filename.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Empty data');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_empty_data.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Too large body');
try {
	$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_too_large.bin', 'rb'));
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}
var_dump($Request->data, $Request->files);

var_dump('Too large file');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_too_large_file.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Bad header');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_bad_header.bin', 'rb'));
var_dump($Request->data, $Request->files);

var_dump('Bad content');
$Request->init_data_and_files([], [], fopen(__DIR__.'/multipart_body_bad_content.bin', 'rb'));
var_dump($Request->data, $Request->files);
?>
--EXPECTF--
string(23) "Bad file ending (no --)"
array(0) {
}
array(0) {
}
string(45) "Bad content-disposition (absent or incorrect)"
array(1) {
  ["d1"]=>
  string(2) "v1"
}
array(1) {
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
}
string(10) "No headers"
array(0) {
}
array(0) {
}
string(10) "Empty name"
array(0) {
}
array(0) {
}
string(15) "Empty file name"
array(0) {
}
array(1) {
  ["single_file"]=>
  array(6) {
    ["name"]=>
    string(0) ""
    ["type"]=>
    string(0) ""
    ["size"]=>
    int(17)
    ["stream"]=>
    NULL
    ["error"]=>
    int(4)
    ["tmp_name"]=>
    NULL
  }
}
string(10) "Empty data"
array(1) {
  ["empty_data"]=>
  string(0) ""
}
array(0) {
}
string(14) "Too large body"
int(413)
string(0) ""
array(0) {
}
array(0) {
}
string(14) "Too large file"
array(0) {
}
array(1) {
  ["single_file"]=>
  array(6) {
    ["name"]=>
    string(12) "upload1.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(4689)
    ["stream"]=>
    NULL
    ["error"]=>
    int(4)
    ["tmp_name"]=>
    NULL
  }
}
string(10) "Bad header"
array(0) {
}
array(0) {
}
string(11) "Bad content"
array(0) {
}
array(0) {
}
