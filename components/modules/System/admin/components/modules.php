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
global $Config, $Index, $L, $db, $Core;
$a					= &$Index;
$rc					= &$Config->routing['current'];
$a->buttons			= false;
$display_modules	= true;
if (isset($rc[2], $rc[3], $Config->components['modules'][$rc[3]]) && !empty($rc[2])) {
	switch ($rc[2]) {
		case 'install':
			$display_modules = false;
			$a->content(
				h::p(
					$L->installation_of_module.' '.h::b($rc[3])
				)
			);
			if ($Core->run_trigger(
				'admin/System/components/modules/install/prepare',
				[
					'name' => $rc[3]
				]
			)) {
				$a->cancel_button_back = true;
				$a->content(
					h::{'button[type=submit]'}($L->install).
					h::{'input[type=hidden]'}(
						[
							'name'		=> 'module',
							'value'		=> $rc[3]
						]
					).
					h::{'input[type=hidden]'}(
						[
							'name'	=> 'mode',
							'value'	=> $rc[2]
						]
					)
				);
			}
		break;
		case 'uninstall':
			$display_modules = false;
			$a->content(
				h::p(
					$L->uninstallation_of_module.' '.h::b($rc[3])
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
					h::{'button[type=submit]'}($L->uninstall).
					h::{'input[type=hidden]'}(
						[
							'name'		=> 'module',
							'value'		=> $rc[3]
						]
					).
					h::{'input[type=hidden]'}(
						[
							'name'	=> 'mode',
							'value'	=> $rc[2]
						]
					)
				);
			}
		break;
		case 'db':
			$display_modules = false;
			global $Page;
			$Page->warning($L->changing_settings_warning);
			if (count($Config->db) > 1) {
				if ($Core->run_trigger(
					'admin/System/components/modules/db/prepare',
					[
						'name' => $rc[3]
					]
				)) {
					$a->buttons				= true;
					$a->apply_button		= false;
					$a->cancel_button_back	= true;
					$dbs					= [0];
					$dbs_name				= [$L->core_db];
					foreach ($Config->db as $i => &$db_data) {
						if ($i) {
							$dbs[] = $i;
							$dbs_name[] = $db_data['name'].' ('.$db_data['host'].' / '.$db_data['type'].')';
						}
					}
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
									'in'		=> $dbs_name,
									'value'		=> $dbs
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
					$a->content(
						h::{'table.cs-admin-table'}(
							h::tr($db_list)
						).
						h::{'input[type=hidden]'}([
							'name'		=> 'module',
							'value'		=> $rc[3]
						]).
						h::{'input[type=hidden]'}([
							'name'	=> 'mode',
							'value'	=> $rc[2]
						])
					);
				}
			}
		break;
		case 'storage':
			$display_modules = false;
			global $Page;
			$Page->warning($L->changing_settings_warning);
			if (count($Config->storage) > 1) {
				if ($Core->run_trigger(
					'admin/System/components/modules/storage/prepare',
					[
						'name' => $rc[3]
					]
				)) {
					$a->buttons				= true;
					$a->apply_button		= false;
					$a->cancel_button_back	= true;
					$storages				= [0];
					$storages_name			= [$L->core_storage];
					foreach ($Config->storage as $i => &$storage_data) {
						if ($i) {
							$storages[] = $i;
							$storages_name[] = $storage_data['host'].'('.$storage_data['connection'].')';
						}
					}
					unset($i, $storage_data);
					$storage_list[] = h::{'th.ui-widget-header.ui-corner-all'}([
						h::info('storage_purpose'),//TODO  check processing of storage configuration
						h::info('system_storage')
					]);
					$storage_json = _json_decode(_file_get_contents(MODULES.DS.$rc[3].DS.'admin'.DS.'storage.json'));
					foreach ($storage_json as $storage) {
						$storage_list[] = h::{'td.ui-widget-content.ui-corner-all'}([
							$L->{$rc[3].'_storage_'.$storage},
							h::{'select.cs-form-element'}(
								[
									'in'		=> $storages_name,
									'value'		=> $storages
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
					$a->content(
						h::{'table.cs-admin-table'}(
							h::tr($storage_list)
						).
						h::{'input[type=hidden]'}([
							'name'		=> 'module',
							'value'		=> $rc[3]
						]).
						h::{'input[type=hidden]'}([
							'name'	=> 'mode',
							'value'	=> $rc[2]
						])
					);
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
			//DataBases tettings
			if (_file_exists(MODULES.DS.$module.DS.'admin'.DS.'db.json') && count($Config->db) > 1) {
				$action .= h::a(
					h::{'button.cs-button-compact'}(
						h::icon('gear'),
						[
							'data-title'	=> $L->databases
						]
					),
					[
						'href'		=> $a->action.'/db/'.$module
					]
				);
			}
			//Storages
			if (_file_exists(MODULES.DS.$module.DS.'admin'.DS.'storage.json') && count($Config->storage) > 1) {
				$action .= h::a(
					h::{'button.cs-button-compact'}(
						h::icon('disk'),
						[
							'data-title'	=> $L->storages
						]
					),
					[
						'href'		=> $a->action.'/storage/'.$module
					]
				);
			}
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
			if (mb_strtolower($module) != 'system') {
				if (
					_is_dir(MODULES.DS.$module.DS.'admin') &&
					(
						_file_exists(MODULES.DS.$module.DS.'admin'.DS.'index.php') ||
						_file_exists(MODULES.DS.$module.DS.'admin'.DS.'index.json')
					)
				) {
					$action .= h::a(
						h::{'button.cs-button-compact'}(
							h::icon('wrench'),
							[
								'data-title'	=> $L->settings
							]
						),
						[
							'href'		=> 'admin/'.$module
						]
					);
				}
				$action .= h::a(
					h::{'button.cs-button-compact'}(
						h::icon($mdata['active'] == 1 ? 'minusthick' : 'check'),
						[
							'data-title'	=> $mdata['active'] == 1 ? $L->disable : $L->enable
						]
					),
					[
						'href'		=> $a->action.($mdata['active'] == 1 ? '/disable/' : '/enable/').$module
					]
				).
				h::a(
					h::{'button.cs-button-compact'}(
						h::icon('trash'),
						[
							'data-title'	=> $L->uninstall
						]
					),
					[
						'href'		=> $a->action.'/uninstall/'.$module
					]
				);
			}
		//If module uninstalled or not installed yet
		} else {
			$action .= h::a(
				h::{'button.cs-button-compact'}(
					h::icon('arrowthickstop-1-s'),
					[
						'data-title'	=> $L->install
					]
				),
				[
					'href'		=> $a->action.'/install/'.$module
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
		h::{'table.cs-admin-table.cs-center-all'}($modules_list).
		h::{'button[type=submit]'}(
			$L->update_modules_list,
			[
				'data-title'	=> $L->update_modules_list_info,
				'name'			=> 'update_modules_list'
			]
		)
	);
}