<?php
/**
 * @package    CleverStyle CMS
 * @subpackage CleverStyle theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\themes\CleverStyle;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\Request,
	cs\User,
	h;

/**
 * Returns array with `a` items
 *
 * @return string[]
 */
function get_main_menu () {
	$Config          = Config::instance();
	$L               = Language::instance();
	$User            = User::instance();
	$main_menu_items = [];
	/**
	 * Administration item
	 */
	if ($User->admin()) {
		$main_menu_items[] = h::a(
			$L->system_admin_administration,
			[
				'href' => 'admin'
			]
		);
	}
	/**
	 * Home item
	 */
	$main_menu_items[] = h::a(
		$L->system_home,
		[
			'href' => '/'
		]
	);
	/**
	 * All other active modules if permissions allow to visit
	 */
	foreach (array_keys($Config->components['modules']) as $module) {
		if (
			$module != Config::SYSTEM_MODULE &&
			$module != $Config->core['default_module'] &&
			$User->get_permission($module, 'index') &&
			file_exists_with_extension(MODULES."/$module/index", ['php', 'html', 'json']) &&
			!@file_get_json(MODULES."/$module/meta.json")['hide_in_menu'] &&
			$Config->module($module)->enabled()
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

/**
 * Getting footer information
 *
 * @return string
 */
function get_footer () {
	$db = class_exists('cs\\DB', false) ? DB::instance() : null;
	/**
	 * Some useful details about page execution process, will be called directly before output
	 */
	Event::instance()->on(
		'System/Page/render/after',
		function () {
			$Page       = Page::instance();
			$Page->Html = str_replace(
				[
					'<!--generate time-->',
					'<!--memory usage-->',
					'<!--peak memory usage-->'
				],
				[
					round(microtime(true) - Request::instance()->started, 5),
					round(memory_get_usage() / 1024 / 1024, 5),
					round(memory_get_peak_usage() / 1024 / 1024, 5)
				],
				$Page->Html
			);
		}
	);
	return h::div(
		sprintf(
			'Page generated in %s s; %d queries to DB in %f s; memory consumption %s MiB (peak %s MiB)',
			'<!--generate time-->',
			$db ? $db->queries() : 0,
			$db ? round($db->time(), 5) : 0,
			'<!--memory usage-->',
			'<!--peak memory usage-->'
		),
		'© Powered by <a target="_blank" href="http://cleverstyle.org/cms" title="CleverStyle CMS">CleverStyle CMS</a>'
	);
}

function level ($in, $level) {
	return trim(h::level($in, $level))."\n";
}
