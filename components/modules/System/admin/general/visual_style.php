<?php
global $Config, $Index, $L;
$Config->reload_themes();
$a = &$Index;
$a->content(
	h::{'table.cs-admin-table.cs-left-even.cs-right-odd'}(
		h::tr(
			h::td(h::info('current_theme')).
			h::td(
				h::{'select#change_theme.cs-form-element'}(
					$Config->core['active_themes'],
					array(
						'name'		=> 'core[theme]',
						'selected'	=> $Config->core['theme'],
						'size'		=> 5
					)
				)
			)
		).
		h::tr(
			h::td(h::info('active_themes')).
			h::td(
				h::{'select#change_active_themes.cs-form-element'}(
					$Config->core['themes'],
					array(
						'name'		=> 'core[active_themes][]',
						'selected'	=> $Config->core['active_themes'],
						'size'		=> 5,
						'multiple'
					)
				)
			)
		).
		h::tr(
			h::td(h::info('color_scheme')).
			h::td(
				h::{'select#change_color_scheme.cs-form-element'}(
					$Config->core['color_schemes'][$Config->core['theme']],
					array(
						'name'		=> 'core[color_scheme]',
						'selected'	=> $Config->core['color_scheme'],
						'size'		=> 5
					)
				)
			)
		).
		h::tr(
			h::td(h::info('allow_change_theme')).
			h::td(
				h::{'input[type=radio]'}(
					array(
						'name'		=> 'core[allow_change_theme]',
						'checked'	=> $Config->core['allow_change_theme'],
						'value'		=> array(0, 1),
						'in'		=> array($L->off, $L->on)
					)
				)
			)
		)
	)
);
unset($a);
?>