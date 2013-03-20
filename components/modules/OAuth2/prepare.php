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