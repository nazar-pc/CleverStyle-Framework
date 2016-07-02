<?php
namespace cs;
$Cache = Cache::instance();

var_dump('Initial state');
var_dump($Cache->cache_state());

var_dump('Set');
var_dump($Cache->set('test', 'blablabla'));
var_dump('Get');
var_dump($Cache->get('test'));
var_dump('Del');
var_dump($Cache->del('test'));
var_dump($Cache->get('test'));

var_dump('Set (as property)');
$Cache->test = 'blablabla';
var_dump('Get (as property)');
var_dump($Cache->test);
var_dump('Del (as property)');
unset($Cache->test);
var_dump($Cache->test);

var_dump('Set the same key');
var_dump($Cache->set('test', 5));
var_dump('Get non-existent key with callback');
var_dump(
	$Cache->get(
		'who_is_that',
		function () {
			return 'me';
		}
	)
);
var_dump('Get non-existent key again');
var_dump($Cache->who_is_that);

var_dump('Namespaced key set');
var_dump($Cache->set('posts/1', 'foo'));
var_dump('Namespaced key get');
var_dump($Cache->get('posts/1'));
var_dump('Namespaced key del');
var_dump($Cache->del('posts/1'));
var_dump($Cache->get('posts/1'));
var_dump('Namespaced key del parent');
var_dump($Cache->set('posts/1', 'bar'));
var_dump($Cache->del('posts'));
var_dump($Cache->get('posts/1'));

$Cache_prefix = Cache::prefix('posts');
var_dump('Namespaced (using prefix) key set');
var_dump($Cache_prefix->set('1', 'foo'));
var_dump('Namespaced (using prefix) key get');
var_dump($Cache_prefix->get('1'));
var_dump('Namespaced (using prefix) key del');
var_dump($Cache_prefix->del('1'));
var_dump($Cache_prefix->get('1'));
var_dump('Namespaced (using prefix) key del parent');
var_dump($Cache_prefix->set('1', 'bar'));
var_dump($Cache_prefix->del('/'));
var_dump($Cache_prefix->get('1'));

var_dump('Namespaced (using prefix, as property) key set');
$Cache_prefix->one = 'foo';
var_dump('Namespaced (using prefix, as property) key get');
var_dump($Cache_prefix->one);
var_dump('Namespaced (using prefix, as property) key del');
unset($Cache_prefix->one);
var_dump($Cache_prefix->one);
var_dump('Namespaced (using prefix, as property) key del parent');
$Cache_prefix->one = 'bar';
unset($Cache_prefix->{'/'});
var_dump($Cache_prefix->one);

var_dump('Get after clean');
$Cache->set('key', 1);
var_dump($Cache->clean());
var_dump($Cache->get('key'));

var_dump('/ path is equivalent to cleaning');
$Cache->set('key', 1);
var_dump($Cache->del('/'));
var_dump($Cache->get('key'));

$Cache->set('key', 1);
$Cache->disable();
var_dump('State after disable');
var_dump($Cache->cache_state());
var_dump('Get after disable');
var_dump($Cache->key);
var_dump('Get with callback after disable');
var_dump(
	$Cache->get(
		'xuz',
		function () {
			return 5;
		}
	)
);

var_dump('Set after disable');
var_dump($Cache->set('key', 1));
var_dump($Cache->key);

Cache::instance_reset();
$Cache = Cache::instance();
var_dump('Delete empty key');
var_dump($Cache->del(''));

unset($Cache);
Cache::instance_reset();
