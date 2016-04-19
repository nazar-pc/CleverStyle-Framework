<?php
namespace cs;

$Cache = Cache::instance();

var_dump('initial state', $Cache->cache_state());
var_dump('simple set', $Cache->set('test', 'blablabla'));
var_dump('simple get', $Cache->test);
var_dump('simple del', $Cache->del('test'));
var_dump('get after del', $Cache->get('test'));
var_dump('set the same key', $Cache->set('test', 5));
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
