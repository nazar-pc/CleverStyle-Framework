<?php
global $Config, $Index, $L;
$Config->reload_themes();
$a = &$Index;
$a->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('current_theme'),
			h::{'select#change_theme.cs-form-element'}(
				$Config->core['active_themes'],
				[
					'name'		=> 'core[theme]',
					'selected'	=> $Config->core['theme'],
					'size'		=> 5
				]
			)
		]),

		h::td([
			h::info('active_themes'),
			h::{'select#change_active_themes.cs-form-element'}(
				$Config->core['themes'],
				[
					'name'		=> 'core[active_themes][]',
					'selected'	=> $Config->core['active_themes'],
					'size'		=> 5,
					'multiple'
				]
			)
		]),

		h::td([
			h::info('color_scheme'),
			h::{'select#change_color_scheme.cs-form-element'}(
				$Config->core['color_schemes'][$Config->core['theme']],
				[
					'name'		=> 'core[color_scheme]',
					'selected'	=> $Config->core['color_scheme'],
					'size'		=> 5
				]
			)
		]),

		h::td([
			h::info('allow_change_theme'),
			h::{'input[type=radio]'}(
				[
					'name'		=> 'core[allow_change_theme]',
					'checked'	=> $Config->core['allow_change_theme'],
					'value'		=> [0, 1],
					'in'		=> [$L->off, $L->on]
				]
			)
		])
	])
);