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
		h::{'input[name=mode][type=radio]'}([
			'value'		=> ['core', 'module', 'plugin'],
			'in'		=> ['Core', 'Module', 'Plugin'],
			'onclick'	=> 'change_mode(this.value);'
		])
	).
	h::{'table tr| td'}(
		[
			'Modules',
			'Plugins'
		],
		[
			h::{'select#modules[name=modules[]][size=10][multiple]'}(
				get_files_list(DIR.'/components/modules', '/[^System)]/', 'd'),
				[
					'selected'	=> 'none'
				]
			),
			h::{'select#plugins[name=plugins[]][size=10][multiple]'}(
				get_files_list(DIR.'/components/plugins', false, 'd'),
				[
					'selected'	=> 'none'
				]
			)
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