<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
/**
 * Just replaces first "?" symbol by "#"
 *
 * @param string	$str
 *
 * @return string
 */
function uri_for_token ($str) {
	return implode(
		'#',
		explode(
			'?',
			$str,
			2
		)
	);
}
if (!function_exists('http_build_url')) {
	function http_build_url ($url, $parts) {
		$url	= explode('?', $url, 2);
		$params	= [];
		if (isset($url[1])) {
			foreach (explode('&', $url[1]) as $u) {
				$params[]	= $u;
			}
			unset($u, $url[1]);
		}
		$url	= $url[0];
		foreach ($parts as $name => $value) {
			$params[]	= $name.'='.urlencode($value);
		}
		unset($parts, $p);
		$params	= array_unique($params);
		return $url.'?'.implode('&', $params);
	}
}