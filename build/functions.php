<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
/**
 * @return string
 */
function form () {
	return h::{'form[method=post]'}(
		h::nav(
			'Build: '.
			h::{'radio[name=mode]'}(
				[
					'value' => ['core', 'module', 'plugin', 'theme'],
					'in'    => ['Core', 'Module', 'Plugin', 'Theme']
				]
			)
		).
		h::{'table tr| td'}(
			[
				'Modules',
				'Plugins',
				'Themes'
			],
			[
				h::{'select[name=modules[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/components/modules', 'System')
				),
				h::{'select[name=plugins[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/components/plugins')
				),
				h::{'select[name=themes[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/themes', 'CleverStyle')
				)
			]
		).
		h::{'input[name=suffix]'}(
			[
				'placeholder' => 'Package file suffix'
			]
		).
		h::{'button[type=submit]'}(
			'Build'
		)
	);
}

/**
 * @param string $dir
 * @param string $exclude_dir
 *
 * @return array[]
 */
function get_list_for_form ($dir, $exclude_dir = '') {
	$components = array_values(
		array_filter(
			get_files_list($dir, false, 'd'),
			function ($module) use ($exclude_dir) {
				return $module != $exclude_dir;
			}
		)
	);
	foreach ($components as &$component) {
		$component = [
			$component,
			file_exists("$dir/$component/meta.json") ? [
				'title' => 'Version: '.file_get_json("$dir/$component/meta.json")['version']
			] : [
				'title' => 'No meta.json file found',
				'disabled'
			]
		];
	}
	return $components;
}
