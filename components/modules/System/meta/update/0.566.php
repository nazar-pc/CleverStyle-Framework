<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
file_put_contents(
	DIR.'/config/main.php',
	str_replace(
		"/**
* If true - language can't be changed, it can be useful if there are several domains,
* every of which must work with fixed language (en.domain.com, ru.domain.com, de.domain.com)
*
* Usually, system care about this automatically and there is no need to change this
*/
//define('FIXED_LANGUAGE',	false);
",
		'',
		file_get_contents(DIR.'/config/main.php')
	)
);
