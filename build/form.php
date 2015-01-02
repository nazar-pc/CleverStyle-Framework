<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
echo	h::{'form[method=post]'}(
	h::nav(
		'Build: '.
		h::{'radio.build-mode[name=mode]'}([
			'value'		=> ['core', 'module', 'plugin', 'theme'],
			'in'		=> ['Core', 'Module', 'Plugin', 'Theme'],
			'onclick'	=> 'change_mode(this.value, this);'
		])
	).
	h::{'table tr| td'}(
		[
			'Modules',
			'Plugins',
			'Themes'
		],
		[
			h::{'select#modules[name=modules[]][size=15][multiple] option'}(array_map(
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
			h::{'select#plugins[name=plugins[]][size=15][multiple] option'}(array_map(
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
			)),
			h::{'select#themes[name=themes[]][size=15][multiple] option'}(array_map(
				function ($theme) {
					return [
						$theme,
						file_exists(DIR."/themes/$theme/meta.json") ? false : [
							'title'		=> 'No meta.json file found',
							'disabled'
						]
					];
				},
				get_files_list(DIR.'/themes', '/[^CleverStyle)]/', 'd')
			))
		]
	).
	h::{'input[name=suffix]'}([
		'placeholder'	=> 'Package file suffix'
	]).
	h::{'button.uk-button.license'}(
		'License',
		[
			'onclick'	=> "window.open('license.txt', 'license', 'location=no')"
		]
	).
	h::{'button.uk-button[type=submit]'}(
		'Build'
	)
);
