<?php
/**
 * Provides next triggers:<br>
 *  admin/System/components/plugins/enable<br>
 *  ['name'	=> <i>plugin_name</i>]<br>
 *  admin/System/components/plugins/disable<br>
 *  ['name'	=> <i>plugin_name</i>]
 */
global $Config, $Index, $L, $Core;
$a = &$Index;
$rc = $Config->routing['current'];
$a->form = false;
$plugins = get_list(PLUGINS, false, 'd');
if (isset($rc[2], $rc[3]) && !empty($rc[2]) && !empty($rc[3])) {
	switch ($rc[2]) {
		case 'enable':
			if (!in_array($rc[3], $Config->components['plugins']) && in_array($rc[3], $plugins)) {
				$Config->components['plugins'][] = $rc[3];
				$a->save('components');
				$Core->run_trigger(
					'admin/System/components/plugins/enable',
					[
						'name' => $rc[3]
					]
				);
			}
		break;
		case 'disable':
			if (in_array($rc[3], $Config->components['plugins'])) {
				foreach ($Config->components['plugins'] as $i => $plugin) {
					if ($plugin == $rc[3] || !in_array($rc[3], $plugins)) {
						unset($Config->components['plugins'][$i], $i, $plugin);
						break;
					}
				}
				unset($i, $plugin);
				$a->save('components');
				$Core->run_trigger(
					'admin/System/components/plugins/disable',
					[
						'name' => $rc[3]
					]
				);
			}
		break;
	}
}
unset($rc);
$plugins_list = h::tr(
	h::{'th.ui-widget-header.ui-corner-all'}(
		[
			$L->plugin_name,
			$L->state,
			$L->action
		]
	)
);
foreach ($plugins as $plugin) {
	$addition_state = $action = '';
	//Information about plugin
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
			'note',
			[
				'data-title'	=> $L->information_about_plugin.h::br().$L->click_to_view_details,
				'onClick'		=> '$(\'#'.$plugin.'_readme\').dialog(\'open\');'
			]
		);
	}
	unset($tag, $file);
	//License
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
			'info',
			[
				'data-title'	=> $L->license.h::br().$L->click_to_view_details,
				'onClick'		=> '$(\'#'.$plugin.'_license\').dialog(\'open\');'
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
	$plugins_list .= h::tr(
		h::{'td.ui-widget-content.ui-corner-all'}($plugin).
		h::{'td.ui-widget-content.ui-corner-all'}(
			h::icon(
				$state ? 'check' : 'minusthick',
				[
					'data-title'	=> $state ? $L->enabled : $L->disabled
				]
			).
			$addition_state
		).
		h::{'td.ui-widget-content.ui-corner-all'}($action)
	);
}
unset($plugins, $plugin, $state, $addition_state, $action);
$a->content(
	h::{'table.cs-fullwidth-table.cs-center-all'}($plugins_list)
);