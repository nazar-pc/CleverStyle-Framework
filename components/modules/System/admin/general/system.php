<?php
global $L, $Config, $Index;

$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('site_mode'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[site_mode]',
				'checked'		=> $Config->core['site_mode'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]),

		h::td([
			h::info('closed_title'),
			h::{'input.cs-form-element'}([
				'name'			=> 'core[closed_title]',
				'value'			=> $Config->core['closed_title']
			])
		]),

		h::td([
			h::info('closed_text'),
			h::{'textarea#closed_text.EDITORH.cs-form-element'}(
				$Config->core['closed_text'],
				[
					'name'		=> 'core[closed_text]'
				]
			)
		]),

		h::td([
			h::info('title_delimiter'),
			h::{'input.cs-form-element'}([
				'name'			=> 'core[title_delimiter]',
				'value'			=> $Config->core['title_delimiter']
			])
		]),

		h::td([
			h::info('title_reverse'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[title_reverse]',
				'checked'		=> $Config->core['title_reverse'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]),

		h::td([
			$L->show_tooltips,
			h::{'input[type=radio]'}([
				'name'			=> 'core[show_tooltips]',
				'checked'		=> $Config->core['show_tooltips'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]),

		h::td([
			h::info('simple_admin_mode'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[simple_admin_mode]',
				'checked'		=> $Config->core['simple_admin_mode'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('debug'),
				h::{'input[type=radio]'}([
				'name'			=> 'core[debug]',
				'checked'		=> $Config->core['debug'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'OnClick'		=> ['$(\'#debug_form\').hide();', '$(\'#debug_form\').show();']
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td().
		h::{'td#debug_form.cs-padding-left'}(
			h::{'table tr'}([
				h::td([
					$L->show_objects_data,
					h::{'input[type=radio]'}([
						'name'			=> 'core[show_objects_data]',
						'checked'		=> $Config->core['show_objects_data'],
						'value'			=> [0, 1],
						'in'			=> [$L->off, $L->on]
					])
				]),

				h::td([
					$L->show_user_data,
					h::{'input[type=radio]'}([
						'name'			=> 'core[show_user_data]',
						'checked'		=> $Config->core['show_user_data'],
						'value'			=> [0, 1],
						'in'			=> [$L->off, $L->on]
					])
				]),

				h::td([
					$L->show_queries,
					h::{'input[type=radio]'}([
						'name'			=> 'core[show_queries]',
						'checked'		=> $Config->core['show_queries'],
						'value'			=> [0, 1],
						'in'			=> [$L->off, $L->on]
					])
				]),

				h::td([
					$L->show_cookies,
					h::{'input[type=radio]'}([
							'name'			=> 'core[show_cookies]',
							'checked'		=> $Config->core['show_cookies'],
							'value'			=> [0, 1],
							'in'			=> [$L->off, $L->on]
					])
				])
			]),
			[
				'style' => ($Config->core['debug'] == 0 ? 'display: none;' : '')
			]
		) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('routing'),
			h::{'table#system_config_routing tr'}([
				h::{'td info'}('routing_in').
				h::{'td info'}('routing_out'),

				h::{'td textarea.cs-form-element.cs-wide-textarea'}(
					$Config->routing['in'],
					[
						'name'				=> 'routing[in]'
					]
				).
				h::{'td textarea.cs-form-element.cs-wide-textarea'}(
					$Config->routing['out'],
					[
						'name'				=> 'routing[out]'
					]
				)
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('replace'),
			h::{'table#system_config_replace tr'}([
					h::{'td info'}('replace_in').
					h::{'td info'}('replace_out'),

					h::{'td textarea.cs-form-element.cs-wide-textarea'}(
						$Config->replace['in'],
						[
							'name'			=> 'replace[in]'
						]
					).
					h::{'td textarea.cs-form-element.cs-wide-textarea'}(
						$Config->replace['out'],
						[
							'name'			=> 'replace[out]'
						]
					)
			])
		]) : false)
	])
);