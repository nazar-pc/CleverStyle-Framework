<?php
global $Config, $Index, $L;
$Config->reload_languages();
$a = &$Index;
$a->content(
	h::{'table.admin_table.left_even.right_odd'}(
		h::tr(
			h::td(h::info('current_language')).
			h::td(
				h::{'select#change_language.form_element'}(
					$Config->core['active_languages'],
					array(
						'name'		=> 'core[language]',
						'selected'	=> $Config->core['language'],
						'size'		=> 5
					)
				)
			)
		).
		h::tr(
			h::td(h::info('active_languages')).
			h::td(
				h::{'select#change_active_languages.form_element'}(
					$Config->core['languages'],
					array(
						'name'		=> 'core[active_languages][]',
						'selected'	=> $Config->core['active_languages'],
						'size'		=> 5,
						'multiple'
					)
				)
			)
		).
		h::tr(
			h::td(h::info('multilanguage')).
			h::td(
				h::{'input[type=radio]'}(
					array(
						'name'			=> 'core[multilanguage]',
						'checked'		=> $Config->core['multilanguage'],
						'value'			=> array(0, 1),
						'in'			=> array($L->off, $L->on),
					)
				)
			)
		)
	)
);
unset($a);
?>