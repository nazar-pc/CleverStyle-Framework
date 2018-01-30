<?php
/**
 * @package  CleverStyle Framework
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs;

file_put_contents(
	DIR.'/config/main.json',
	str_replace(
		['//Cache engine', 'cache_engine', '//Settings of Memcached cache engine'],
		['//Cache driver', 'cache_driver', '//Settings of Memcached cache driver'],
		file_get_contents(DIR.'/config/main.json')
	)
);
