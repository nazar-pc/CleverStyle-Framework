<?php
/**
 * Provides next triggers:<br>
 *  admin/System/components/modules/install/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *  admin/System/components/modules/uninstall/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *  admin/System/components/modules/db/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *  admin/System/components/modules/storage/prepare<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *  admin/System/components/modules/enable<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *  admin/System/components/modules/disable<br>
 *  ['name'	=> <i>module_name</i>]
 */
global $Config, $Index, $L, $db, $Core, $Page;
$a					= &$Index;
$rc					= &$Config->routing['current'];
$a->buttons			= false;
$display_modules	= true;
if (isset($rc[2], $rc[3], $Config->components['modules'][$rc[3]]) && !empty($rc[2])) {
	switch ($rc[2]) {
		case 'install':
			$display_modules = false;
			$Page->title($L->installation_of_module.' '.$rc[3]);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->installation_of_module.' '.$rc[3]
				)
			);
			if ($Core->run_trigger(
				'admin/System/components/modules/install/prepare',
				[
					'name' => $rc[3]
				]
			)) {
				$a->cancel_button_back = true;
				if ($Config->core['simple_admin_mode']) {
					if (_file_exists(MODULES.DS.$rc[3].DS.'admin'.DS.'db.json')) {
						$db_json = _json_decode(_file_get_contents(MODULES.DS.$rc[3].DS.'admin'.DS.'db.json'));
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
					if (_file_exists(MODULES.DS.$rc[3].DS.'admin'.DS.'storage.json')) {
						$storage_json = _json_decode(_file_get_contents(MODULES.DS.$rc[3].DS.'admin'.DS.'storage.json'));
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
					h::{'button[type=submit]'}($L->install)
				);
			}
		break;
		case 'uninstall':
			$display_modules = false;
			$Page->title($L->uninstallation_of_module.' '.$rc[3]);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->uninstallation_of_module.' '.$rc[3]
				)
			);
			if ($Core->run_trigger(
				'admin/System/components/modules/uninstall/prepare',
				[
					'name' => $rc[3]
				]
			)) {
				$a->cancel_button_back = true;
				$a->content(
					h::{'button[type=submit]'}($L->uninstall)
				);
			}
		break;
		case 'db':
			$display_modules = false;
			if (count($Config->db) > 1) {
				global $Page;
				$Page->warning($L->changing_settings_warning);
				$Page->title($L->db_settings_for_module.' '.$rc[3]);
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->db_settings_for_module.' '.$rc[3]
					)
				);
				if ($Core->run_trigger(
					'admin/System/components/modules/db/prepare',
					[
						'name' => $rc[3]
					]
				)) {
					$a->buttons				= true;
					$a->apply_button		= false;
					$a->cancel_button_back	= true;
					module_db_settings:
					if (_file_exists(MODULES.DS.$rc[3].DS.'admin'.DS.'db.json')) {
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
						$db_json = _json_decode(_file_get_contents(MODULES.DS.$rc[3].DS.'admin'.DS.'db.json'));
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
							h::{'table.cs-fullwidth-table'}(
								h::tr($db_list)
							)
						);
						unset($db_list);
					}
					if ($rc[2] == 'install') {
						goto back_to_module_installation_1;
					}
				}
			}
		break;
		case 'storage':
			$display_modules = false;
			if (count($Config->storage) > 1) {
				global $Page;
				$Page->warning($L->changing_settings_warning);
				$Page->title($L->storage_settings_for_module.' '.$rc[3]);
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->storage_settings_for_module.' '.$rc[3]
					)
				);
				if ($Core->run_trigger(
					'admin/System/components/modules/storage/prepare',
					[
						'name' => $rc[3]
					]
				)) {
					$a->buttons				= true;
					$a->apply_button		= false;
					$a->cancel_button_back	= true;
					module_storage_settings:
					if (_file_exists(MODULES.DS.$rc[3].DS.'admin'.DS.'storage.json')) {
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
						$storage_json = _json_decode(_file_get_contents(MODULES.DS.$rc[3].DS.'admin'.DS.'storage.json'));
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
			}
		break;
		case 'enable':
			$Config->components['modules'][$rc[3]]['active'] = 1;
			$a->save('components');
			$Core->run_trigger(
				'admin/System/components/modules/enable',
				[
					'name' => $rc[3]
				]
			);
		break;
		case 'disable':
			$Config->components['modules'][$rc[3]]['active'] = 0;
			$a->save('components');
			$Core->run_trigger(
				'admin/System/components/modules/disable',
				[
					'name' => $rc[3]
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
if ($display_modules) {
	unset($rc);
	global $User;
	$db_users_items = $User->get_users_columns();
	$modules_list = h::tr(
		h::{'th.ui-widget-header.ui-corner-all'}([
			$L->module_name,
			$L->state,
			$L->action
		])
	);
	foreach ($Config->components['modules'] as $module => &$mdata) {
		//If module if enabled or disabled
		$addition_state = $action = '';
		if ($mdata['active'] == 1 || $mdata['active'] == 0) {
			//Notice about API existence
			if (_is_dir(MODULES.DS.$module.DS.'api')) {
				if (
					_file_exists($file = MODULES.DS.$module.DS.'api'.DS.'readme.txt') ||
					_file_exists($file = MODULES.DS.$module.DS.'api'.DS.'readme.html')
				) {
					if (substr($file, -3) == 'txt') {
						$tag = 'pre';
					} else {
						$tag = 'div';
					}
					$addition_state .= h::$tag(
						_file_get_contents($file),
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
						'data-title'	=> $L->api_exists.h::br().(_file_exists($file) ? $L->click_to_view_details : ''),
						'onClick'		=> '$(\'#'.$module.'_api\').dialog(\'open\');'
					]
				);
				unset($tag, $file);
			}
			//Information about module
			if (_file_exists($file = MODULES.DS.$module.DS.'readme.txt') || _file_exists($file = MODULES.DS.$module.DS.'readme.html')) {
				if (substr($file, -3) == 'txt') {
					$tag = 'pre';
				} else {
					$tag = 'div';
				}
				$addition_state .= h::$tag(
					_file_get_contents($file),
					[
						'id'			=> $module.'_readme',
						'class'			=> 'cs-dialog',
						'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
						'title'			=> $module.' -> '.$L->information_about_module
					]
				).
				h::{'icon.pointer'}(
					'note',
					[
						'data-title'	=> $L->information_about_module.h::br().$L->click_to_view_details,
						'onClick'		=> '$(\'#'.$module.'_readme\').dialog(\'open\');'
					]
				);
			}
			unset($tag, $file);
			//License
			if (_file_exists($file = MODULES.DS.$module.DS.'license.txt') || _file_exists($file = MODULES.DS.$module.DS.'license.html')) {
				if (substr($file, -3) == 'txt') {
					$tag = 'pre';
				} else {
					$tag = 'div';
				}
				$addition_state .= h::$tag(
					_file_get_contents($file),
					[
						'id'			=> $module.'_license',
						'class'			=> 'cs-dialog',
						'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
						'title'			=> $module.' -> '.$L->license
					]
				).
				h::{'icon.pointer'}(
					'info',
					[
						'data-title'	=> $L->license.h::br().$L->click_to_view_details,
						'onClick'		=> '$(\'#'.$module.'_license\').dialog(\'open\');'
					]
				);
			}
			unset($tag, $file);
			//DataBases tettings
			if (!$Config->core['simple_admin_mode'] && _file_exists(MODULES.DS.$module.DS.'admin'.DS.'db.json') && count($Config->db) > 1) {
				$action .= h::{'a.cs-button.cs-button-compact'}(
					h::icon('gear'),
					[
						'href'			=> $a->action.'/db/'.$module,
						'data-title'	=> $L->databases
					]
				);
			}
			//Storages
			if (!$Config->core['simple_admin_mode'] && _file_exists(MODULES.DS.$module.DS.'admin'.DS.'storage.json') && count($Config->storage) > 1) {
				$action .= h::{'a.cs-button.cs-button-compact'}(
					h::icon('disk'),
					[
						'href'			=> $a->action.'/storage/'.$module,
						'data-title'	=> $L->storages
					]
				);
			}
			if (mb_strtolower($module) != 'system') {
				if (
					_is_dir(MODULES.DS.$module.DS.'admin') &&
					(
						_file_exists(MODULES.DS.$module.DS.'admin'.DS.'index.php') ||
						_file_exists(MODULES.DS.$module.DS.'admin'.DS.'index.json')
					)
				) {
					$action .= h::{'a.cs-button.cs-button-compact'}(
						h::icon('wrench'),
						[
							'href'			=> 'admin/'.$module,
							'data-title'	=> $L->settings
						]
					);
				}
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
		//If module uninstalled or not installed yet
		} else {
			$action .= h::{'a.cs-button.cs-button-compact'}(
				h::icon('arrowthickstop-1-s'),
				[
					'href'			=> $a->action.'/install/'.$module,
					'data-title'	=> $L->install
				]
			);
		}
		$modules_list .= h::tr(
			h::{'td.ui-widget-content.ui-corner-all'}($module).
			h::{'td.ui-widget-content.ui-corner-all'}(
				h::icon(
					$mdata['active'] == 1 ? 'check' : ($mdata['active'] == 0 ? 'minusthick' : 'closethick'),
					[
						'data-title'	=> $mdata['active'] == 1 ? $L->enabled :
							($mdata['active'] == 2 ? $L->disabled : $L->uninstalled.' ('.$L->not_installed.')')
					]
				).
				$addition_state
			).
			h::{'td.ui-widget-content.ui-corner-all.cs-modules-config-buttons'}($action)
		);
	}
	$a->content(
		h::{'table.cs-fullwidth-table.cs-center-all'}($modules_list).
		h::{'button[type=submit]'}(
			$L->update_modules_list,
			[
				'data-title'	=> $L->update_modules_list_info,
				'name'			=> 'update_modules_list'
			]
		)
	);
}