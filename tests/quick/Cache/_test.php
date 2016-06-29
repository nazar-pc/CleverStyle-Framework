<?php
namespace cs;
$Cache = Cache::instance();

var_dump('Initial state');
var_dump($Cache->cache_state());
var_dump('Set');
var_dump($Cache->set('test', 'blablabla'));
var_dump('Get');
var_dump($Cache->test);
var_dump('Del');
var_dump($Cache->del('test'));
var_dump($Cache->get('test'));
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
var_dump('Get after clean');
$Cache->set('key', 1);
var_dump($Cache->clean(), $Cache->get('key'));
$Cache->disable();
var_dump('State after disable');
var_dump($Cache->cache_state());
var_dump('Get after disable');
var_dump($Cache->test);
var_dump('Get with callback after disable');
var_dump(
	$Cache->get(
		'xuz',
		function () {
			return 5;
		}
	)
);
unset($Cache);
Cache::instance_reset();
