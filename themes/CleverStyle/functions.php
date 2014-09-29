<?php
/**
 * @package		ClevereStyle CMS
 * @subpackage	CleverStyle theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\themes\CleverStyle;
use
	cs\Config,
	cs\Language,
	cs\User,
	h;
/**
 * Returns array with `a` items
 *
 * @return string[]
 */
function get_main_menu () {
	$Config				= Config::instance();
	$L					= Language::instance();
	$User				= User::instance();
	$main_menu_items	= [];
	/**
	 * Administration item if allowed
	 */
	if ($User->admin() || ($Config->can_be_admin && $Config->core['ip_admin_list_only'])) {
		$main_menu_items[] = h::a(
			$L->administration,
			[
				'href' => 'admin'
			]
		);
	}
	/**
	 * Home item
	 */
	$main_menu_items[] = h::a(
		$L->home,
		[
			'href' => '/'
		]
	);
	/**
	 * All other active modules if permissions allow to visit
	 */
	foreach ($Config->components['modules'] as $module => $module_data) {
		if (
			$module_data['active'] == 1 &&
			$module != $Config->core['default_module'] &&
			$module != 'System' &&
			$User->get_permission($module, 'index') &&
			(
				file_exists(MODULES."/$module/index.php") ||
				file_exists(MODULES."/$module/index.html") ||
				file_exists(MODULES."/$module/index.json")
			)
		) {
			$main_menu_items[] = h::a(
				$L->$module,
				[
					'href' => path($L->$module)
				]
			);
		}
	}
	return $main_menu_items;
}
