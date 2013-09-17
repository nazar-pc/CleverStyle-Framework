<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
echo	h::{'form[method=post]'}(
	h::nav(
		'Build: '.
		h::{'input.build-mode[name=mode][type=radio]'}([
			'value'		=> ['core', 'module', 'plugin'],
			'in'		=> ['Core', 'Module', 'Plugin'],
			'onclick'	=> 'change_mode(this.value, this);'
		])
	).
	h::{'table tr| td'}(
		[
			'Modules',
			'Plugins'
		],
		[
			h::{'select#modules[name=modules[]][size=10][multiple] option'}(array_map(
				function ($module) {
					return [
						$module,
						file_exists(DIR."/components/modules/$module/meta.json") ? [] : [
							'title'		=> 'No meta.json file found',
							'disabled'
						]
					];
				},
				get_files_list(DIR.'/components/modules', '/[^System)]/', 'd')
			)),
			h::{'select#plugins[name=plugins[]][size=10][multiple] option'}(array_map(
				function ($plugin) {
					return [
						$plugin,
						file_exists(DIR."/components/plugins/$plugin/meta.json") ? false : [
							'title'		=> 'No meta.json file found',
							'disabled'
						]
					];
				},
				get_files_list(DIR.'/components/plugins', false, 'd')
			))
		]
	).
	h::{'button.license[type=button]'}(
		'License',
		[
			'onclick'	=> "window.open('license.txt', 'license', 'location=no')"
		]
	).
	h::{'button[type=submit]'}(
		'Build'
	)
);