<?php
/**
 * @package		ClevereStyle CMS
 * @subpackage	Tenebris theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/msie|trident/i',$_SERVER['HTTP_USER_AGENT'])) {
	Page::instance()->Head	.= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
}
$Index	= Index::instance();
$Page	= Page::instance();
if ($Index->main_sub_menu) {
	$Page->main_sub_menu = h::{'li.uk-nav-header'}();
	foreach ($Index->main_sub_menu as $item) {
		if (isset($item[1], $item[1]['class']) && $item[1]['class'] == 'uk-active') {
			if ($Index->main_menu_more) {
				$Page->main_sub_menu .= h::{'li.uk-parent.uk-active.uk-closed'}(
					"<a href='#'>$item[0]</a>".
					h::{'ul.uk-nav-sub li| a'}($Index->main_menu_more)
				);
			} else {
				$Page->main_sub_menu .= h::{'li.uk-active[data-uk-dropdown=] a'}($item);
			}
		} else {
			$Page->main_sub_menu .= h::{'li a'}($item);
		}
	}
} elseif ($Index->main_menu_more) {
	$Page->main_sub_menu = h::{'li| a'}($Index->main_menu_more);
}
