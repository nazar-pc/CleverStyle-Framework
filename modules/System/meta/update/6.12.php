<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
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
