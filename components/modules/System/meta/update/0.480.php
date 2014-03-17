<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config					= Config::instance();
$core					= &$Config->core;
$core['url']			= (array)$core['url'];
foreach ($core['mirrors_url'] as $url) {
	$core['url'][]	= $url;
}
unset($url);
$core['cookie_domain']	= (array)$core['cookie_domain'];
foreach ($core['mirrors_cookie_domain'] as $domain) {
	$core['cookie_domain'][]	= $domain;
}
unset($domain);
$core['cookie_path']	= (array)$core['cookie_path'];
foreach ($core['mirrors_cookie_path'] as $path) {
	$core['cookie_path'][]	= $path;
}
unset($path);
unset(
	$core['mirrors_url'],
	$core['mirrors_cookie_domain'],
	$core['mirrors_cookie_path'],
	$core['cookie_sync']
);
$Config->save();
