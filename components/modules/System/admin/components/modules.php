<?php
/**
 * Provides next triggers:<br>
 *  admin/System/components/modules/install/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/uninstall/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/default_module/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/db/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/storage/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/enable<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/disable<br>
 *  ['name'	=> <i>module_name</i>]
 */
namespace	cs\modules\System;
use			\h;
global $Config, $Index, $L, $Core;
$a					= $Index;
$rc					= $Config->routing['current'];
$a->buttons			= false;
$show_modules		= true;
if (isset($rc[2], $rc[3], $Config->components['modules'][$rc[3]]) && !empty($rc[2])) {
	global $Page;
	switch ($rc[2]) {
		case 'install':
			$show_modules	= false;
			$Page->title($L->installation_of_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->installation_of_module($rc[3])
				)
			);
			if (!$Core->run_trigger(
				'admin/System/components/modules/install/prepare',
				[
					'name'	=> $rc[3]
				]
			)) {
				break;
			}
			$check_dependencies		= check_dependencies($rc[3], 'module');
			if (!$check_dependencies && $Config->core['simple_admin_mode']) {
				break;
			}
			$a->cancel_button_back	= true;
			if ($Config->core['simple_admin_mode']) {
				if (file_exists(MODULES.'/'.$rc[3].'/meta/db.json')) {
					$db_json = _json_decode(file_get_contents(MODULES.'/'.$rc[3].'/meta/db.json'));
					foreach ($db_json as $database) {
						$a->content(
							h::{'input[type=hidden]'}([
								'name'		=> 'db['.$database.']',
								'value'		=> 0
							])
						);
					}
					unset($db_json, $database);
				}
				if (file_exists(MODULES.'/'.$rc[3].'/meta/storage.json')) {
					$storage_json = _json_decode(file_get_contents(MODULES.'/'.$rc[3].'/meta/storage.json'));
					foreach ($storage_json as $storage) {
						$a->content(
							h::{'input[type=hidden]'}([
								'name'		=> 'storage['.$storage.']',
								'value'		=> 0
							])
						);
					}
					unset($storage_json, $storage);
				}
			} else {
				goto module_db_settings;
				back_to_module_installation_1:
				goto module_storage_settings;
				back_to_module_installation_2:
			}
			$a->content(
				h::{'button[type=submit]'}(
					$L->{$check_dependencies ? 'install' : 'force_install_not_recommended'}
				)
			);
		break;
		case 'uninstall':
			$show_modules	= false;
			$Page->title($L->uninstallation_of_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->uninstallation_of_module($rc[3])
				)
			);
			if (!$Core->run_trigger(
				'admin/System/components/modules/uninstall/prepare',
				[
					'name'	=> $rc[3]
				]
			)) {
				break;
			}
			$check_dependencies		= check_backward_dependencies($rc[3], 'module');
			if (!$check_dependencies && $Config->core['simple_admin_mode']) {
				break;
			}
			$a->cancel_button_back	= true;
			$a->content(
				h::{'button[type=submit]'}(
					$L->{$check_dependencies ? 'uninstall' : 'force_uninstall_not_recommended'}
				)
			);
		break;
		case 'default_module':
			$show_modules	= false;
			$Page->title($L->setting_default_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->setting_default_module($rc[3])
				)
			);
			if (!$Core->run_trigger(
				'admin/System/components/modules/default_module/prepare',
				[
					'name'	=> $rc[3]
				]
			)) {
				break;
			}
			$a->cancel_button_back = true;
			$a->content(
				h::{'button[type=submit]'}($L->uninstall)
			);
		break;
		case 'db':
			$show_modules	= false;
			if (count($Config->db) > 1) {
				global $Page;
				$Page->warning($L->changing_settings_warning);
				$Page->title($L->db_settings_for_module($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->db_settings_for_module($rc[3])
					)
				);
				if (!$Core->run_trigger(
					'admin/System/components/modules/db/prepare',
					[
						'name' => $rc[3]
					]
				)) {
					break;
				}
				$a->buttons				= true;
				$a->apply_button		= false;
				$a->cancel_button_back	= true;
				module_db_settings:
				if (file_exists(MODULES.'/'.$rc[3].'/meta/db.json')) {
					$dbs					= [0 => $L->core_db];
					foreach ($Config->db as $i => &$db_data) {
						if ($i) {
							$dbs[$i] = $db_data['name'].' ('.$db_data['host'].' / '.$db_data['type'].')';
						}
					}
					unset($i, $db_data);
					$db_list[] = h::{'th.ui-widget-header.ui-corner-all'}([
						h::info('db_purpose'),
						h::info('system_db')
					]);
					$db_json = _json_decode(file_get_contents(MODULES.'/'.$rc[3].'/meta/db.json'));
					foreach ($db_json as $database) {
						$db_list[] = h::{'td.ui-widget-content.ui-corner-all'}([
							$L->{$rc[3].'_db_'.$database},
							h::{'select.cs-form-element'}(
								[
									'in'		=> array_values($dbs),
									'value'		=> array_keys($dbs)
								],
								[
									'name'		=> 'db['.$database.']',
									'selected'	=> isset($Config->components['modules'][$rc[3]]['db'][$database]) ?
										$Config->components['modules'][$rc[3]]['db'][$database] : 0,
									'size'		=> 5
								]
							)
						]);
					}
					unset($db_json, $dbs, $database);
					$a->content(
						h::{'table.cs-fullwidth-table tr'}(
							$db_list
						)
					);
					unset($db_list);
				}
				if ($rc[2] == 'install') {
					goto back_to_module_installation_1;
				}
			}
		break;
		case 'storage':
			$show_modules	= false;
			if (count($Config->storage) > 1) {
				global $Page;
				$Page->warning($L->changing_settings_warning);
				$Page->title($L->storage_settings_for_module($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->storage_settings_for_module($rc[3])
					)
				);
				if (!$Core->run_trigger(
					'admin/System/components/modules/storage/prepare',
					[
						'name'	=> $rc[3]
					]
				)) {
					break;
				}
				$a->buttons				= true;
				$a->apply_button		= false;
				$a->cancel_button_back	= true;
				module_storage_settings:
				if (file_exists(MODULES.'/'.$rc[3].'/meta/storage.json')) {
					$storages				= [0 => $L->core_storage];
					foreach ($Config->storage as $i => &$storage_data) {
						if ($i) {
							$storages[$i] = $storage_data['host'].'('.$storage_data['connection'].')';
						}
					}
					unset($i, $storage_data);
					$storage_list[] = h::{'th.ui-widget-header.ui-corner-all'}([
						h::info('storage_purpose'),
						h::info('system_storage')
					]);
					$storage_json = _json_decode(file_get_contents(MODULES.'/'.$rc[3].'/meta/storage.json'));
					foreach ($storage_json as $storage) {
						$storage_list[] = h::{'td.ui-widget-content.ui-corner-all'}([
							$L->{$rc[3].'_storage_'.$storage},
							h::{'select.cs-form-element'}(
								[
									'in'		=> array_values($storages),
									'value'		=> array_keys($storages)
								],
								[
									'name'		=> 'storage['.$storage.']',
									'selected'	=> isset($Config->components['modules'][$rc[3]]['storage'][$storage]) ?
										$Config->components['modules'][$rc[3]]['storage'][$storage] : 0,
									'size'		=> 5
								]
							)
						]);
					}
					unset($storage_json, $storages, $storage);
					$a->content(
						h::{'table.cs-fullwidth-table'}(
							h::tr($storage_list)
						)
					);
					unset($storage_list);
				}
				if ($rc[2] == 'install') {
					goto back_to_module_installation_2;
				}
			}
		break;
		case 'enable':
			$Config->components['modules'][$rc[3]]['active'] = 1;
			$a->save('components');
			$Core->run_trigger(
				'admin/System/components/modules/enable',
				[
					'name'	=> $rc[3]
				]
			);
		break;
		case 'disable':
			$Config->components['modules'][$rc[3]]['active'] = 0;
			$a->save('components');
			$Core->run_trigger(
				'admin/System/components/modules/disable',
				[
					'name'	=> $rc[3]
				]
			);
		break;
	}
	switch ($rc[2]) {
		case 'install':
		case 'uninstall':
		case 'db':
		case 'storage':
			$a->content(
				h::{'input[type=hidden]'}([
					'name'	=> 'mode',
					'value'	=> $rc[2]
				]).
				h::{'input[type=hidden]'}([
					'name'	=> 'module',
					'value'	=> $rc[3]
				])
			);
	}
}
unset($rc);
if (!$show_modules) {
	return;
}
$modules_list = [h::{'th.ui-widget-header.ui-corner-all'}(
	$L->module_name,
	$L->state,
	$L->action
)];
foreach ($Config->components['modules'] as $module => &$mdata) {
	/**
	 * If module if enabled or disabled
	 */
	$addition_state = $action = '';
	if ($mdata['active'] != -1) {
		/**
		 * Notice about API existence
		 */
		if (is_dir(MODULES.'/'.$module.'/api')) {
			if (
				file_exists($file = MODULES.'/'.$module.'/api/readme.txt') ||
				file_exists($file = MODULES.'/'.$module.'/api/readme.html')
			) {
				if (substr($file, -3) == 'txt') {
					$tag = 'pre';
				} else {
					$tag = 'div';
				}
				$addition_state .= h::$tag(
					file_get_contents($file),
					[
						'id'			=> $module.'_api',
						'class'			=> 'cs-dialog',
						'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
						'title'			=> $module.' -> '.$L->api
					]
				);
			}
			$addition_state .= h::{'icon.pointer'}(
				'link',
				[
					'data-title'	=> $L->api_exists.h::br().(file_exists($file) ? $L->click_to_view_details : ''),
					'onClick'		=> "$('#".$module."_api').dialog('open');"
				]
			);
			unset($tag, $file);
		}
		/**
		 * Information about module
		 */
		if (file_exists($file = MODULES.'/'.$module.'/readme.txt') || file_exists($file = MODULES.'/'.$module.'/readme.html')) {
			if (substr($file, -3) == 'txt') {
				$tag = 'pre';
			} else {
				$tag = 'div';
			}
			$addition_state .= h::$tag(
				file_get_contents($file),
				[
					'id'			=> $module.'_readme',
					'class'			=> 'cs-dialog',
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $module.' -> '.$L->information_about_module
				]
			).
			h::{'icon.pointer'}(
				'notice',
				[
					'data-title'	=> $L->information_about_module.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#".$module."_readme').dialog('open');"
				]
			);
		}
		unset($tag, $file);
		/**
		 * License
		 */
		if (file_exists($file = MODULES.'/'.$module.'/license.txt') || file_exists($file = MODULES.'/'.$module.'/license.html')) {
			if (substr($file, -3) == 'txt') {
				$tag = 'pre';
			} else {
				$tag = 'div';
			}
			$addition_state .= h::$tag(
				file_get_contents($file),
				[
					'id'			=> $module.'_license',
					'class'			=> 'cs-dialog',
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $module.' -> '.$L->license
				]
			).
			h::{'icon.pointer'}(
				'note',
				[
					'data-title'	=> $L->license.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#".$module."_license').dialog('open');"
				]
			);
		}
		unset($tag, $file);
		/**
		 * Setting default module
		 */
		if ($mdata['active'] == 1 && $module != 'System' && $module != $Config->core['default_module']) {
			$action .= h::{'a.cs-button.cs-button-compact'}(
				h::icon('home'),
				[
				'href'			=> $a->action.'/default_module/'.$module,
				'data-title'	=> $L->make_default_module
				]
			);
		}
		/**
		 * DataBases settings
		 */
		if (!$Config->core['simple_admin_mode'] && file_exists(MODULES.'/'.$module.'/meta/db.json') && count($Config->db) > 1) {
			$action .= h::{'a.cs-button.cs-button-compact'}(
				h::icon('gear'),
				[
					'href'			=> $a->action.'/db/'.$module,
					'data-title'	=> $L->databases
				]
			);
		}
		/**
		 * Storages settings
		 */
		if (!$Config->core['simple_admin_mode'] && file_exists(MODULES.'/'.$module.'/meta/storage.json') && count($Config->storage) > 1) {
			$action .= h::{'a.cs-button.cs-button-compact'}(
				h::icon('disk'),
				[
					'href'			=> $a->action.'/storage/'.$module,
					'data-title'	=> $L->storages
				]
			);
		}
		if ($module != 'System') {
			/**
			 * Link to the module admin page
			 */
			if (file_exists(MODULES.'/'.$module.'/admin/index.php') || file_exists(MODULES.'/'.$module.'/admin/index.json')) {
				$action .= h::{'a.cs-button.cs-button-compact'}(
					h::icon('wrench'),
					[
						'href'			=> 'admin/'.$module,
						'data-title'	=> $L->module_admin_page
					]
				);
			}
			if ($module != $Config->core['default_module']) {
				$action .= h::{'a.cs-button.cs-button-compact'}(
					h::icon($mdata['active'] == 1 ? 'minusthick' : 'check'),
					[
						'href'			=> $a->action.($mdata['active'] == 1 ? '/disable/' : '/enable/').$module,
						'data-title'	=> $mdata['active'] == 1 ? $L->disable : $L->enable
					]
				).
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('trash'),
					[
						'href'			=> $a->action.'/uninstall/'.$module,
						'data-title'	=> $L->uninstall
					]
				);
			}
		}
	/**
	 * If module uninstalled or not installed yet
	 */
	} else {
		$action .= h::{'a.cs-button.cs-button-compact'}(
			h::icon('arrowthickstop-1-s'),
			[
				'href'			=> $a->action.'/install/'.$module,
				'data-title'	=> $L->install
			]
		);
	}
	$module_info	= false;
	if (file_exists(MODULES.'/'.$module.'/meta.json')) {
		$module_meta	= _json_decode(file_get_contents(MODULES.'/'.$module.'/meta.json'));
		$module_info	= $L->module_info(
			$module_meta['package'],
			$module_meta['version'],
			$module_meta['description'],
			$module_meta['author'],
			isset($module_meta['website']) ? $module_meta['website'] : $L->none,
			$module_meta['license'],
			isset($module_meta['db_support']) ? implode(', ', $module_meta['db_support']) : $L->none,
			isset($module_meta['provide']) ? implode(', ', $module_meta['provide']) : $L->none,
			isset($module_meta['require']) ? implode(', ', $module_meta['require']) : $L->none,
			isset($module_meta['conflict']) ? implode(', ', $module_meta['conflict']) : $L->none
		);
	}
	unset($module_meta);
	$modules_list[]	= h::{'td.ui-widget-content.ui-corner-all'}(
		[
			$module,
			[
				'data-title'	=> $module_info
			]
		],
		h::icon(
			$mdata['active'] == 1 ? (
				$module == $Config->core['default_module'] ? 'home' : 'check'
			) : (
				$mdata['active'] == 0 ? 'minusthick' : 'closethick'
			),
			[
				'data-title'	=> $mdata['active'] == 1 ? (
					$module == $Config->core['default_module'] ? $L->default_module : $L->enabled
				) : (
					$mdata['active'] == 0 ? $L->disabled : $L->uninstalled.' ('.$L->not_installed.')'
				)
			]
		).
		$addition_state,
		[
			$action,
			[
				'class'	=> 'cs-modules-config-buttons'
			]
		]
	);
	unset($module_info);
}
$a->content(
	h::{'table.cs-fullwidth-table.cs-center-all tr'}($modules_list).
	h::{'button[type=submit]'}(
		$L->update_modules_list,
		[
			'data-title'	=> $L->update_modules_list_info,
			'name'			=> 'update_modules_list'
		]
	)
);