<?php
/**
 * @package    CleverStyle Framework
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
					'value' => ['core', 'module', 'theme'],
					'in'    => ['Core', 'Module', 'Theme']
				]
			)
		).
		h::{'table tr| td'}(
			[
				'Modules',
				'Themes'
			],
			[
				h::{'select[name=modules[]][size=20][multiple] option'}(
					get_list_for_form(DIR.'/modules', 'System')
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
 * @param string $exclude_component
 *
 * @return array[]
 */
function get_list_for_form ($dir, $exclude_component = '') {
	$components = [];
	foreach (array_map('basename', glob("$dir/*", GLOB_ONLYDIR)) as $component) {
		if ($component != $exclude_component) {
			$components[] = [
				$component,
				file_exists("$dir/$component/meta.json") ? [
					'title' => 'Version: '.file_get_json("$dir/$component/meta.json")['version']
				] : [
					'title' => 'No meta.json file found',
					'disabled'
				]
			];
		}
	}
	return $components;
}
