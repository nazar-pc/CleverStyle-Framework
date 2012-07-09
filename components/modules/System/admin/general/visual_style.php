<?php
global $Config, $Index, $L;
$Config->reload_themes();
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		system_select_core($Config->core['active_themes'],							'theme',			'change_theme',			'current_theme'),
		system_select_core($Config->core['themes'],									'active_themes',	'change_active_themes',	null, true),
		system_select_core($Config->core['color_schemes'][$Config->core['theme']],	'color_scheme',		'change_color_scheme'),
		system_input_core('allow_change_theme', 'radio')
	)
);