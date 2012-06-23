<?php
global $Config, $Index, $L, $LANGUAGE;
$Config->reload_languages();
$a = &$Index;
$translate_engines			= _mb_substr(get_list(ENGINES, '/^translate\..*?\.php$/i', 'f'), 10, -4);
$translate_engines_settings	= [];
$current_engine_settings	= '';
foreach ($translate_engines as $engine) {
	$parameters					= _json_decode(_file_get_contents(ENGINES.DS.'translate.'.$engine.'.json'));
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
$a->content(
	(FIXED_LANGUAGE ? h::{'p.ui-priority-primary.cs-state-messages'}(
		$L->language_fixed_as.' '.$LANGUAGE
	) : null).
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('current_language'),
			h::{'select#change_language.cs-form-element'}(
				$Config->core['active_languages'],
				[
					'name'		=> 'core[language]',
					'selected'	=> $Config->core['language'],
					'size'		=> 5
				]
			)
		]),
		h::td([
			h::info('active_languages'),
			h::{'select#change_active_languages.cs-form-element'}(
				$Config->core['languages'],
				[
					'name'		=> 'core[active_languages][]',
					'selected'	=> $Config->core['active_languages'],
					'size'		=> 5,
					'multiple'
				]
			)
		]),
		h::td([
			h::info('multilanguage'),
			h::{'input[type=radio]'}([
					'name'			=> 'core[multilanguage]',
					'checked'		=> $Config->core['multilanguage'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
					'OnClick'		=> [
						'$(\'.cs-multilanguage\').hide(); if (!$(\'#auto_translation :checked\').val()) $(\'.cs-auto-translation\').hide();',
						'$(\'.cs-multilanguage\').show(); if (!$(\'#auto_translation :checked\').val()) $(\'.cs-auto-translation\').show();'
					]
			])
		]),
		h::{'td#auto_translation.cs-multilanguage'}(
			[
				h::info('auto_translation'),
				h::{'input[type=radio]'}([
					'name'			=> 'core[auto_translation]',
					'checked'		=> $Config->core['auto_translation'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
					'OnClick'		=> ['$(\'.cs-auto-translation\').hide();', '$(\'.cs-auto-translation\').show();']
				])
			],
			[
				'style' => !$Config->core['multilanguage'] ? 'display: none; ' : ''
			]
		),
		h::{'td#auto_translation_engine.cs-auto-translation'}(
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
				'style' => !$Config->core['multilanguage'] || !$Config->core['auto_translation'] ? 'display: none; ' : ''
			]
		),
		h::{'td.cs-auto-translation'}(
			$L->auto_translation_engine_settings,
			[
				'style' => !$Config->core['multilanguage'] || !$Config->core['auto_translation'] ? 'display: none; ' : ''
			]
		).
		h::{'td#auto_translation_engine_settings.cs-auto-translation'}(
			$current_engine_settings,
			[
				'style' => !$Config->core['multilanguage'] || !$Config->core['auto_translation'] ? 'display: none; ' : ''
			]
		)
	])
);