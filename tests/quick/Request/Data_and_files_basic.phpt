--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
stream_wrapper_register('request-file', Request\File_stream::class);
$Request = Request::instance();

$Request->init_data_and_files(
	[
		'd1' => 'v1',
		'd2' => [
			'd2-nested' => 'v2n'
		]
	],
	[
		'single_file'                        => [
			'name'     => 'upload1.html',
			'type'     => 'text/html',
			'size'     => 16,
			'tmp_name' => __DIR__.'/upload1.html',
			'error'    => UPLOAD_ERR_OK
		],
		'single_file_stream'                 => [
			'name'   => 'upload1.html',
			'type'   => 'text/html',
			'size'   => 16,
			'stream' => fopen(__DIR__.'/upload1.html', 'rb'),
			'error'  => UPLOAD_ERR_OK
		],
		'multiple_files'                     => [
			'name'     => [
				'upload1.html',
				'upload2.html'
			],
			'type'     => [
				'text/html',
				'text/html'
			],
			'size'     => [
				17,
				11
			],
			'tmp_name' => [
				__DIR__.'/upload1.html',
				__DIR__.'/upload2.html'
			],
			'error'    => [
				UPLOAD_ERR_OK,
				UPLOAD_ERR_OK
			]
		],
		'multiple_files_alternative'         => [
			[
				'name'     => 'upload1.html',
				'type'     => 'text/html',
				'size'     => 17,
				'tmp_name' => __DIR__.'/upload1.html',
				'error'    => UPLOAD_ERR_OK
			],
			[
				'name'     => 'upload2.html',
				'type'     => 'text/html',
				'size'     => 11,
				'tmp_name' => __DIR__.'/upload2.html',
				'error'    => UPLOAD_ERR_OK
			]
		],
		'multiple_files_streams'             => [
			'name'   => [
				'upload1.html',
				'upload2.html'
			],
			'type'   => [
				'text/html',
				'text/html'
			],
			'size'   => [
				17,
				11
			],
			'stream' => [
				fopen(__DIR__.'/upload1.html', 'rb'),
				fopen(__DIR__.'/upload2.html', 'rb')
			],
			'error'  => [
				UPLOAD_ERR_OK,
				UPLOAD_ERR_OK
			]
		],
		'multiple_files_streams_alternative' => [
			[
				'name'   => 'upload1.html',
				'type'   => 'text/html',
				'size'   => 17,
				'stream' => fopen(__DIR__.'/upload1.html', 'rb'),
				'error'  => UPLOAD_ERR_OK
			],
			[
				'name'   => 'upload2.html',
				'type'   => 'text/html',
				'size'   => 11,
				'stream' => fopen(__DIR__.'/upload2.html', 'rb'),
				'error'  => UPLOAD_ERR_OK
			]
		],
		'file_error'                         => [
			'name'  => 'upload1.html',
			'type'  => 'text/html',
			'size'  => 17,
			'error' => UPLOAD_ERR_OK
		]
	]
);

var_dump('Data basic');
var_dump($Request->data, $Request->data('d1'), $Request->data(['d1']), $Request->data('d3'));

var_dump('Data multiple');
var_dump($Request->data('d1', 'd2'));
var_dump($Request->data(['d1', 'd2']));
var_dump($Request->data('d1', 'd3'));
var_dump($Request->data(['d1', 'd3']));

var_dump('File basic');
var_dump($Request->files);
var_dump($Request->files('single_file'));
var_dump($Request->files('multiple_files'));

var_dump('Files read from fs');
var_dump(file_get_contents($Request->files['single_file']['tmp_name']));
var_dump(file_get_contents($Request->files['single_file_stream']['tmp_name']));

var_dump('Files read from stream');
rewind($Request->files['single_file']['stream']);
var_dump(stream_get_contents($Request->files['single_file']['stream']));
rewind($Request->files['single_file_stream']['stream']);
var_dump(stream_get_contents($Request->files['single_file_stream']['stream']));

var_dump('Files read from fs (multiple)');
var_dump(file_get_contents($Request->files['multiple_files'][0]['tmp_name']));
var_dump(file_get_contents($Request->files['multiple_files_streams'][0]['tmp_name']));
var_dump(file_get_contents($Request->files['multiple_files'][1]['tmp_name']));
var_dump(file_get_contents($Request->files['multiple_files_streams'][1]['tmp_name']));

var_dump('Files read from stream (multiple)');
rewind($Request->files['multiple_files'][0]['stream']);
var_dump(stream_get_contents($Request->files['multiple_files'][0]['stream']));
rewind($Request->files['multiple_files_streams'][0]['stream']);
var_dump(stream_get_contents($Request->files['multiple_files_streams'][0]['stream']));
rewind($Request->files['multiple_files'][1]['stream']);
var_dump(stream_get_contents($Request->files['multiple_files'][1]['stream']));
rewind($Request->files['multiple_files_streams'][1]['stream']);
var_dump(stream_get_contents($Request->files['multiple_files_streams'][1]['stream']));

var_dump('Upload error');
var_dump($Request->files['file_error'] !== UPLOAD_ERR_OK);
?>
--EXPECTF--
string(10) "Data basic"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
string(2) "v1"
array(1) {
  ["d1"]=>
  string(2) "v1"
}
NULL
string(13) "Data multiple"
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
array(2) {
  ["d1"]=>
  string(2) "v1"
  ["d2"]=>
  array(1) {
    ["d2-nested"]=>
    string(3) "v2n"
  }
}
NULL
NULL
string(10) "File basic"
array(7) {
  ["single_file"]=>
  array(6) {
    ["name"]=>
    string(12) "upload1.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(16)
    ["tmp_name"]=>
    string(%d) "%s/tests/quick/Request/upload1.html"
    ["error"]=>
    int(0)
    ["stream"]=>
    resource(%d) of type (stream)
  }
  ["single_file_stream"]=>
  array(6) {
    ["name"]=>
    string(12) "upload1.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(16)
    ["stream"]=>
    resource(%d) of type (stream)
    ["error"]=>
    int(0)
    ["tmp_name"]=>
    string(34) "request-file:///single_file_stream"
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
      ["tmp_name"]=>
      string(%d) "%s/tests/quick/Request/upload1.html"
      ["stream"]=>
      resource(%d) of type (stream)
      ["error"]=>
      int(0)
    }
    [1]=>
    array(6) {
      ["name"]=>
      string(12) "upload2.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(11)
      ["tmp_name"]=>
      string(%d) "%s/tests/quick/Request/upload2.html"
      ["stream"]=>
      resource(%d) of type (stream)
      ["error"]=>
      int(0)
    }
  }
  ["multiple_files_alternative"]=>
  array(2) {
    [0]=>
    array(6) {
      ["name"]=>
      string(12) "upload1.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(17)
      ["tmp_name"]=>
      string(%d) "%s/tests/quick/Request/upload1.html"
      ["error"]=>
      int(0)
      ["stream"]=>
      resource(%d) of type (stream)
    }
    [1]=>
    array(6) {
      ["name"]=>
      string(12) "upload2.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(11)
      ["tmp_name"]=>
      string(%d) "%s/tests/quick/Request/upload2.html"
      ["error"]=>
      int(0)
      ["stream"]=>
      resource(%d) of type (stream)
    }
  }
  ["multiple_files_streams"]=>
  array(2) {
    [0]=>
    array(6) {
      ["name"]=>
      string(12) "upload1.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(17)
      ["tmp_name"]=>
      string(40) "request-file:///multiple_files_streams/0"
      ["stream"]=>
      resource(%d) of type (stream)
      ["error"]=>
      int(0)
    }
    [1]=>
    array(6) {
      ["name"]=>
      string(12) "upload2.html"
      ["type"]=>
      string(9) "text/html"
      ["size"]=>
      int(11)
      ["tmp_name"]=>
      string(40) "request-file:///multiple_files_streams/1"
      ["stream"]=>
      resource(%d) of type (stream)
      ["error"]=>
      int(0)
    }
  }
  ["multiple_files_streams_alternative"]=>
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
      string(52) "request-file:///multiple_files_streams_alternative/0"
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
      string(52) "request-file:///multiple_files_streams_alternative/1"
    }
  }
  ["file_error"]=>
  array(6) {
    ["name"]=>
    string(12) "upload1.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(17)
    ["error"]=>
    int(4)
    ["tmp_name"]=>
    NULL
    ["stream"]=>
    NULL
  }
}
array(6) {
  ["name"]=>
  string(12) "upload1.html"
  ["type"]=>
  string(9) "text/html"
  ["size"]=>
  int(16)
  ["tmp_name"]=>
  string(%d) "%s/tests/quick/Request/upload1.html"
  ["error"]=>
  int(0)
  ["stream"]=>
  resource(%d) of type (stream)
}
array(2) {
  [0]=>
  array(6) {
    ["name"]=>
    string(12) "upload1.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(17)
    ["tmp_name"]=>
    string(%d) "%s/tests/quick/Request/upload1.html"
    ["stream"]=>
    resource(%d) of type (stream)
    ["error"]=>
    int(0)
  }
  [1]=>
  array(6) {
    ["name"]=>
    string(12) "upload2.html"
    ["type"]=>
    string(9) "text/html"
    ["size"]=>
    int(11)
    ["tmp_name"]=>
    string(%d) "%s/tests/quick/Request/upload2.html"
    ["stream"]=>
    resource(%d) of type (stream)
    ["error"]=>
    int(0)
  }
}
string(18) "Files read from fs"
string(17) "0123456789abcdef
"
string(17) "0123456789abcdef
"
string(22) "Files read from stream"
string(17) "0123456789abcdef
"
string(17) "0123456789abcdef
"
string(29) "Files read from fs (multiple)"
string(17) "0123456789abcdef
"
string(17) "0123456789abcdef
"
string(11) "0123456789
"
string(11) "0123456789
"
string(33) "Files read from stream (multiple)"
string(17) "0123456789abcdef
"
string(17) "0123456789abcdef
"
string(11) "0123456789
"
string(11) "0123456789
"
string(12) "Upload error"
bool(true)
