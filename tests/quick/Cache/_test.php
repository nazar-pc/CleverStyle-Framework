<?php
namespace cs;

$Cache = Cache::instance();

var_dump('initial state', $Cache->cache_state());
var_dump('simple set', $Cache->set('test', 'blablabla'));
var_dump('simple get', $Cache->test);
var_dump('simple del', $Cache->del('test'), $Cache->get('test'));
var_dump('set the same key', $Cache->set('test', 5));
var_dump(
	'get non-existent key with callback',
	$Cache->get(
		'who_is_that',
		function () {
			return 'me';
		}
	)
);
var_dump('get non-existent key again', $Cache->who_is_that);
var_dump('namespaced key set', $Cache->set('posts/1', 'foo'));
var_dump('namespaced key get', $Cache->get('posts/1'));
var_dump('namespaced key del', $Cache->del('posts/1'), $Cache->get('posts/1'));
var_dump('namespaced key del parent', $Cache->set('posts/1', 'bar'), $Cache->del('posts'), $Cache->get('posts/1'));
$Cache->disable();
var_dump('state after disable', $Cache->cache_state());
var_dump('get after disable', $Cache->test);
var_dump(
	'get with callback after disable',
	$Cache->get(
		'xuz',
		function () {
			return 5;
		}
	)
);
