<?php
/**
 * @package  CleverStyle Framework
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs;

file_put_contents(
	DIR.'/.htaccess',
	str_replace(
		'Header set Cache-Control "max-age=2592000, public"',
		'Header set Cache-Control "max-age=2592000, immutable"',
		file_get_contents(DIR.'/.htaccess')
	)
);
