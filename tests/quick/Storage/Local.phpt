--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('PUBLIC_STORAGE', make_tmp_dir());
define('DIR', dirname(make_tmp_dir()));
Config::instance_stub(
	[],
	[
		'core_url' => 'http://cscms.travis'
	]
);

$storage = new Storage\Local('', '');
if (!$storage->connected()) {
	die('Connection failed:(');
}

var_dump('Base url');
var_dump($storage->base_url());

var_dump('Copy file from local FS to storage');
var_dump($storage->file_exists('file.txt'));
var_dump($storage->copy(__DIR__.'/file.txt', 'file.txt'));
var_dump($storage->file_exists('file.txt'));

var_dump('Create directory');
var_dump($storage->is_dir('dir'));
var_dump($storage->mkdir('dir'));
var_dump($storage->is_dir('dir'));

var_dump('Create directory (recursive)');
var_dump($storage->is_dir('dir2/dir3'));
var_dump($storage->mkdir('dir2/dir3', 0777, true));
var_dump($storage->is_dir('dir2/dir3'));
var_dump($storage->is_file('dir2/dir3'));

var_dump('Remove directory');
var_dump($storage->rmdir('dir2/dir3'));
var_dump($storage->is_dir('dir2/dir3'));

var_dump('Copy file withing storage');
var_dump($storage->copy('file.txt', 'dir/file.txt'));
var_dump($storage->is_file('dir/file.txt'));
var_dump($storage->is_dir('dir/file.txt'));

var_dump('Read file as array');
var_dump($storage->file('file.txt'));

var_dump('Put file contents');
var_dump($storage->file_put_contents('file2.txt', 'abc'));

var_dump('Get file contents');
var_dump($storage->file_get_contents('file2.txt'));

var_dump('Remove file');
var_dump($storage->unlink('dir/file.txt'));
var_dump($storage->file_exists('dir/file.txt'));

var_dump('Rename file');
var_dump($storage->rename('file2.txt', 'file3.txt'));
var_dump($storage->file_exists('file2.txt'));
var_dump($storage->file_exists('file3.txt'));

var_dump('List files');
var_dump($storage->get_files_list(''));

var_dump('List files and directories');
var_dump($storage->get_files_list('', false, 'fd'));

var_dump('File url by source');
$url = $storage->url_by_source('file.txt');
var_dump($url);

var_dump('File source by url');
var_dump($storage->source_by_url($url));
?>
--EXPECTF--
string(8) "Base url"
string(47) "http://cscms.travis/%s"
string(34) "Copy file from local FS to storage"
bool(false)
bool(true)
bool(true)
string(16) "Create directory"
bool(false)
bool(true)
bool(true)
string(28) "Create directory (recursive)"
bool(false)
bool(true)
bool(true)
bool(false)
string(16) "Remove directory"
bool(true)
bool(false)
string(25) "Copy file withing storage"
bool(true)
bool(true)
bool(false)
string(18) "Read file as array"
array(2) {
  [0]=>
  string(11) "0123456789
"
  [1]=>
  string(7) "abcdef
"
}
string(17) "Put file contents"
int(3)
string(17) "Get file contents"
string(3) "abc"
string(11) "Remove file"
bool(true)
bool(false)
string(11) "Rename file"
bool(true)
bool(false)
bool(true)
string(10) "List files"
array(2) {
  [0]=>
  string(8) "file.txt"
  [1]=>
  string(9) "file3.txt"
}
string(26) "List files and directories"
array(4) {
  [0]=>
  string(3) "dir"
  [1]=>
  string(4) "dir2"
  [2]=>
  string(8) "file.txt"
  [3]=>
  string(9) "file3.txt"
}
string(18) "File url by source"
string(%d) "http://cscms.travis/%s/file.txt"
string(18) "File source by url"
string(%d) "%s/tests/%s/file.txt"
