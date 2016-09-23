--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
Core::instance_stub(['cache_driver' => 'FileSystem']);
define('CACHE', make_tmp_dir());
require __DIR__.'/_test.php';

$Cache = Cache::instance();

var_dump('Bad key (set)');
var_dump($Cache->set('test/../../../etc', 5));

var_dump('Bad key (get)');
var_dump($Cache->get('test/../../../etc'));

var_dump('Bad key (del)');
var_dump($Cache->del('test/../../../etc'));

var_dump('Bad cache file contents on getting');
file_put_contents(CACHE.'/bad_item', 'xyz');
var_dump($Cache->bad_item);
var_dump(file_exists(CACHE.'/bad_item'));
?>
--EXPECT--
<?php
require __DIR__.'/_test.expect';
?>
string(13) "Bad key (set)"
bool(false)
string(13) "Bad key (get)"
bool(false)
string(13) "Bad key (del)"
bool(false)
string(34) "Bad cache file contents on getting"
bool(false)
bool(false)
