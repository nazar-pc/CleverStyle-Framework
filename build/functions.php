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
			h::{'radio.build-mode[name=mode]'}(
				[
					'value'   => ['core', 'module', 'plugin', 'theme'],
					'in'      => ['Core', 'Module', 'Plugin', 'Theme'],
					'onclick' => 'change_mode(this.value, this);'
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
				h::{'select#modules[name=modules[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/components/modules', 'System')
				),
				h::{'select#plugins[name=plugins[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/components/plugins')
				),
				h::{'select#themes[name=themes[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/themes', 'CleverStyle')
				)
			]
		).
		h::{'input[name=suffix]'}(
			[
				'placeholder' => 'Package file suffix'
			]
		).
		h::{'button.license'}(
			'License',
			[
				'onclick' => "window.open('license.txt', 'license', 'location=no')"
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
