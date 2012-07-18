<?php
global $Config, $Index, $L, $Core;
$a				= $Index;
$rc				= $Config->routing['current'];
$plugins		= get_list(PLUGINS, false, 'd');
$show_plugins	= true;
$a->buttons		= false;
if (isset($rc[2], $rc[3]) && !empty($rc[2]) && !empty($rc[3])) {
	global $Page;
	switch ($rc[2]) {
		case 'enable':
			if (!in_array($rc[3], $Config->components['plugins']) && in_array($rc[3], $plugins)) {
				$Page->title($L->enabling_of_plugin($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->enabling_of_plugin($rc[3])
					)
				);
				$show_plugins			= false;
				$check_dependencies		= check_dependencies($rc[3], 'plugin');
				if (!$check_dependencies && $Config->core['simple_admin_mode']) {
					break;
				}
				$a->cancel_button_back	= true;
				$a->content(
					h::{'button[type=submit]'}(
						$L->{$check_dependencies ? 'enable' : 'force_enable_not_recommended'}
					).
					h::{'input[type=hidden]'}([
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'plugin',
						'value'	=> $rc[3]
					])

				);
			}
		break;
		case 'disable':
			if (in_array($rc[3], $Config->components['plugins'])) {
				$Page->title($L->disabling_of_plugin($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->disabling_of_plugin($rc[3])
					)
				);
				$show_plugins			= false;
				$check_dependencies		= check_backward_dependencies($rc[3], 'plugin');
				if (!$check_dependencies && $Config->core['simple_admin_mode']) {
					break;
				}
				$a->cancel_button_back	= true;
				$a->content(
					h::{'button[type=submit]'}(
						$L->{$check_dependencies ? 'disable' : 'force_disable_not_recommended'}
					).
					h::{'input[type=hidden]'}([
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'plugin',
						'value'	=> $rc[3]
					])

				);
			}
		break;
	}
}
unset($rc);
if (!$show_plugins) {
	return;
}
$a->form		= false;
$plugins_list = [h::{'th.ui-widget-header.ui-corner-all'}(
	$L->plugin_name,
	$L->state,
	$L->action
)];
foreach ($plugins as $plugin) {
	$addition_state = $action = '';
	/**
	 * Information about plugin
	 */
	if (file_exists($file = PLUGINS.'/'.$plugin.'/readme.txt') || file_exists($file = PLUGINS.'/'.$plugin.'/readme.html')) {
		if (substr($file, -3) == 'txt') {
			$tag = 'pre';
		} else {
			$tag = 'div';
		}
		$addition_state .= h::$tag(
			file_get_contents($file),
			[
				'id'			=> $plugin.'_readme',
				'class'			=> 'cs-dialog',
				'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
				'title'			=> $plugin.' -> '.$L->information_about_plugin
			]
		).
		h::{'icon.pointer'}(
			'notice',
			[
				'data-title'	=> $L->information_about_plugin.h::br().$L->click_to_view_details,
				'onClick'		=> "$('#".$plugin."_readme').dialog('open');"
			]
		);
	}
	unset($tag, $file);
	/**
	 * License
	 */
	if (file_exists($file = PLUGINS.'/'.$plugin.'/license.txt') || file_exists($file = PLUGINS.'/'.$plugin.'/license.html')) {
		if (substr($file, -3) == 'txt') {
			$tag = 'pre';
		} else {
			$tag = 'div';
		}
		$addition_state .= h::$tag(
			file_get_contents($file),
			[
				'id'			=> $plugin.'_license',
				'class'			=> 'cs-dialog',
				'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
				'title'			=> $plugin.' -> '.$L->license
			]
		).
		h::{'icon.pointer'}(
			'note',
			[
				'data-title'	=> $L->license.h::br().$L->click_to_view_details,
				'onClick'		=> "$('#".$plugin."_license').dialog('open');"
			]
		);
	}
	unset($tag, $file);
	$state = in_array($plugin, $Config->components['plugins']);
	$action .= h::a(
		h::{'button.cs-button-compact'}(
			h::icon($state ? 'minusthick' : 'check'),
			[
				'data-title'	=> $state ? $L->disable : $L->enable
			]
		),
		[
			'href'		=> $a->action.($state ? '/disable/' : '/enable/').$plugin
		]
	);
	$plugin_info	= false;
	if (file_exists(PLUGINS.'/'.$plugin.'/meta.json')) {
		$plugin_meta	= _json_decode(file_get_contents(PLUGINS.'/'.$plugin.'/meta.json'));
		$plugin_info	= $L->plugin_info(
			$plugin_meta['package'],
			$plugin_meta['version'],
			$plugin_meta['description'],
			$plugin_meta['author'],
			isset($plugin_meta['website']) ? $plugin_meta['website'] : $L->none,
			$plugin_meta['license'],
			isset($plugin_meta['provide']) ? implode(', ', $plugin_meta['provide']) : $L->none,
			isset($plugin_meta['require']) ? implode(', ', $plugin_meta['require']) : $L->none,
			isset($plugin_meta['conflict']) ? implode(', ', $plugin_meta['conflict']) : $L->none
		);
	}
	unset($plugin_meta);
	$plugins_list[]	= h::{'td.ui-widget-content.ui-corner-all'}(
		[
			$plugin,
			[
				'data-title'	=> $plugin_info
			]
		],
		h::icon(
			$state ? 'check' : 'minusthick',
			[
				'data-title'	=> $state ? $L->enabled : $L->disabled
			]
		).
		$addition_state,
		$action
	);
	unset($plugin_info);
}
unset($plugins, $plugin, $state, $addition_state, $action);
$a->content(
	h::{'table.cs-fullwidth-table.cs-center-all tr'}($plugins_list)
);