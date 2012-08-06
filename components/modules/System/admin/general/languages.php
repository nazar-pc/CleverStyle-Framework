<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			\h;
global $Config, $Index, $L, $Core;
$Config->reload_languages();
$translate_engines			= _mb_substr(get_files_list(ENGINES.'/Text', '/^[^_].*?\.php$/i', 'f'), 0, -4);
$translate_engines_settings	= [];
$current_engine_settings	= '';
foreach ($translate_engines as $engine) {
	$parameters					= _json_decode(file_get_contents(ENGINES.'/Text/'.$engine.'.json'));
	if (is_array($parameters) && !empty($parameters)) {
		$table							= '';
		foreach ($parameters as $paremeter => $description) {
			$table .= h::{'tr td'}([
				$description,
				h::{'input.cs-form-element'}([
					'name'	=> 'core[auto_translation_engine]['.$paremeter.']',
					'value' => isset($Config->core['auto_translation_engine'][$paremeter]) ? $Config->core['auto_translation_engine'][$paremeter] : ''
				])
			]);
		}
		$translate_engines_settings[]	= base64_encode(h::table($table));
	} else {
		$translate_engines_settings[]	= base64_encode($parameters ?: $L->no_settings_found);
	}
	if ($engine == $Config->core['auto_translation_engine']['name']) {
		$current_engine_settings		= base64_decode($translate_engines_settings[count($translate_engines_settings) - 1]);
	}
}
unset($engine, $parameters, $paremeter, $description, $table);
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages'}(
		FIXED_LANGUAGE ? $L->language_fixed_as.' '.$Core->config('language') : false
	).
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		system_select_core($Config->core['active_languages'],	'language',			'change_language',	'current_language'),
		system_select_core($Config->core['languages'],			'active_languages',	'cache_active_languages',	null, true),
		[
			h::info('multilingual'),
			h::{'input[type=radio]'}([
					'name'			=> 'core[multilingual]',
					'checked'		=> $Config->core['multilingual'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
					'OnClick'		=> [
						"$('.cs-multilingual').hide(); $('.cs-auto-translation').hide();",
						"$('.cs-multilingual').show(); if ($('#auto_translation [value=1]').prop('checked')) $('.cs-auto-translation').show();"
					]
			])
		],
		[
			[
				h::info('auto_translation'),
				h::{'input[type=radio]'}([
					'name'			=> 'core[auto_translation]',
					'checked'		=> $Config->core['auto_translation'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
					'OnClick'		=> ["$('.cs-auto-translation').hide();", "$('.cs-auto-translation').show();"]
				])
			],
			[
				'style' => !$Config->core['multilingual'] ? 'display: none; ' : '',
				'id'	=> 'auto_translation',
				'class'	=> 'cs-multilingual'
			]
		],
		[
			[
				h::info('auto_translation_engine'),
				h::{'select.cs-form-element'}(
					$translate_engines,
					[
						'name'			=> 'core[auto_translation_engine][name]',
						'selected'		=> $Config->core['auto_translation_engine']['name'],
						'data-settings'	=> $translate_engines_settings,
						'size'			=> 5
					]
				)
			],
			[
				'style' => !$Config->core['multilingual'] || !$Config->core['auto_translation'] ? 'display: none; ' : '',
				'id'	=> 'auto_translation_engine',
				'class'	=> 'cs-auto-translation'
			]
		],
		[
			[
				$L->auto_translation_engine_settings,
				[
					'style' => !$Config->core['multilingual'] || !$Config->core['auto_translation'] ? 'display: none; ' : '',
					'class'	=> 'cs-auto-translation cs-multilingual'
				]
			],
			[
				$current_engine_settings,
				[
					'style' => !$Config->core['multilingual'] || !$Config->core['auto_translation'] ? 'display: none; ' : '',
					'id'	=> 'auto_translation_engine_settings',
					'class'	=> 'cs-auto-translation'
				]
			]
		]
	)
);