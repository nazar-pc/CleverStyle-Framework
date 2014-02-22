<?php
/**
 * @package		ClevereStyle CMS
 * @subpackage	CleverStyle theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/msie|trident/i',$_SERVER['HTTP_USER_AGENT'])) {
	Page::instance()->Head	.= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
}
