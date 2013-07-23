<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
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
use			h,
			cs\Config,
			cs\Core,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\Trigger,
			cs\User;
$Config				= Config::instance();
$Core				= Core::instance();
$L					= Language::instance();
$Page				= Page::instance();
$User				= User::instance();
$a					= Index::instance();
$rc					= $Config->route;
$a->buttons			= false;
$show_modules		= true;
if (
	isset($rc[2]) &&
	!empty($rc[2]) &&
	(
		$rc[2] == 'update_system' ||
		(
			isset($rc[3], $Config->components['modules'][$rc[3]]) ||
			(
				isset($rc[3]) && $rc[2] == 'install' && $rc[3] == 'upload'
			)
		)
	)
) {
	switch ($rc[2]) {
		case 'install':
			if ($rc[3] == 'upload' && isset($_FILES['upload_module']) && $_FILES['upload_module']['tmp_name']) {
				switch ($_FILES['upload_module']['error']) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$Page->warning($L->file_too_large);
					break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$Page->warning($L->temporary_folder_is_missing);
					break;
					case UPLOAD_ERR_CANT_WRITE:
						$Page->warning($L->cant_write_file_to_disk);
					break;
					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_NO_FILE:
					break;
				}
				if ($_FILES['upload_module']['error'] != UPLOAD_ERR_OK) {
					break;
				}
				move_uploaded_file(
					$_FILES['upload_module']['tmp_name'],
					$tmp_file = TEMP.'/'.md5($_FILES['upload_module']['tmp_name'].MICROTIME).'.phar.php'
				);
				$tmp_dir								= 'phar://'.$tmp_file;
				if (!($module = file_get_contents($tmp_dir.'/dir'))) {
					unlink($tmp_file);
					break;
				}
				$rc[3]									= $module;
				if (!file_exists($tmp_dir.'/meta.json') || _json_decode(file_get_contents($tmp_dir.'/meta.json'))['category'] != 'modules') {
					$Page->warning($L->this_is_not_module_installer_file);
					unlink($tmp_file);
					break;
				}
				if (isset($Config->components['modules'][$module])) {
					$current_version		= _json_decode(file_get_contents(MODULES.'/'.$module.'/meta.json'))['version'];
					$new_version			= _json_decode(file_get_contents($tmp_dir.'/meta.json'))['version'];
					if (!version_compare($current_version, $new_version, '<')) {
						$Page->warning($L->update_module_impossible_older_version($module));
						unlink($tmp_file);
						break;
					}
					$check_dependencies		= check_dependencies($module, 'module', $tmp_dir, 'update');
					if (!$check_dependencies && $Config->core['simple_admin_mode']) {
						break;
					}
					$rc[2]					= 'update';
					$show_modules			= false;
					$Page->title($L->updating_of_module($module));
					rename($tmp_file, $tmp_file = TEMP.'/'.$User->get_session().'_module_update.phar.php');
					$a->content(
						h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
							$L->update_module(
								$module,
								$current_version,
								$new_version
							)
						)
					);
					$a->cancel_button_back	= true;
					$a->content(
						h::{'button[type=submit]'}($L->{$check_dependencies ? $L->yes : 'force_update_not_recommended'})
					);
					break;
				}
				if (!file_exists(MODULES.'/'.$module) && !mkdir(MODULES.'/'.$module, 0700)) {
					$Page->warning($L->cant_unpack_module_no_write_permissions);
					unlink($tmp_file);
					break;
				}
				$fs										= _json_decode(file_get_contents($tmp_dir.'/fs.json'));
				$extract								= array_product(
					array_map(
						function ($index, $file) use ($tmp_dir, $module) {
							if (
								!file_exists(pathinfo(MODULES.'/'.$module.'/'.$file, PATHINFO_DIRNAME)) &&
								!mkdir(pathinfo(MODULES.'/'.$module.'/'.$file, PATHINFO_DIRNAME), 0700, true)
							) {
								return 0;
							}
							return (int)copy($tmp_dir.'/fs/'.$index, MODULES.'/'.$module.'/'.$file);
						},
						$fs,
						array_keys($fs)
					)
				);
				file_put_contents(MODULES.'/'.$module.'/fs.json', _json_encode(array_keys($fs)));
				unset($tmp_dir);
				if (!$extract) {
					$Page->warning($L->module_files_unpacking_error);
					break;
				}
				rename($tmp_file, $tmp_file = mb_substr($tmp_file, 0, -9));
				$api_request							= $Core->api_request(
					'System/admin/upload_module',
					[
						'package'	=> str_replace(DIR, $Config->base_url(), $tmp_file)
					]
				);
				if ($api_request) {
					$success	= true;
					foreach ($api_request as $mirror => $result) {
						if ($result == 1) {
							$success	= false;
							$Page->warning($L->cant_unpack_module_on_mirror($mirror));
						}
					}
					if (!$success) {
						$Page->warning($L->module_files_unpacking_error);
						break;
					}
					unset($success, $mirror, $result);
				}
				unset($api_request);
				unlink($tmp_file);
				$Config->components['modules'][$module]	= [
					'active'	=> -1,
					'db'		=> [],
					'storage'	=> []
				];
				unset($tmp_file, $module);
				$Config->save();
			} elseif ($rc[3] == 'upload') {
				break;
			}
			$show_modules	= false;
			$Page->title($L->installation_of_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->installation_of_module($rc[3])
				)
			);
			if (!Trigger::instance()->run(
				'admin/System/components/modules/install/prepare',
				[
					'name'	=> $rc[3]
				]
			)) {
				break;
			}
			$check_dependencies		= check_dependencies($rc[3], 'module', null, 'install');
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
			$show_modules			= false;
			$Page->title($L->uninstallation_of_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->uninstallation_of_module($rc[3])
				)
			);
			if (!Trigger::instance()->run(
				'admin/System/components/modules/uninstall/prepare',
				[
					'name'	=> $rc[3]
				]
			)) {
				break;
			}
			$check_dependencies		= check_backward_dependencies($rc[3], 'module', 'uninstall');
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
		case 'update_system':
			if (!isset($_FILES['upload_system']) || !$_FILES['upload_system']['tmp_name']) {
				break;
			}
			switch ($_FILES['upload_system']['error']) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$Page->warning($L->file_too_large);
				break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$Page->warning($L->temporary_folder_is_missing);
				break;
				case UPLOAD_ERR_CANT_WRITE:
					$Page->warning($L->cant_write_file_to_disk);
				break;
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
				break;
			}
			if ($_FILES['upload_system']['error'] != UPLOAD_ERR_OK) {
				break;
			}
			move_uploaded_file(
				$_FILES['upload_system']['tmp_name'],
				$tmp_file = TEMP.'/'.md5($_FILES['upload_system']['tmp_name'].MICROTIME).'.phar.php'
			);
			$tmp_dir								= 'phar://'.$tmp_file;
			if (!file_exists($tmp_dir.'/version') || !file_exists($tmp_dir.'/themes.json')) {
				$Page->warning($L->this_is_not_system_installer_file);
				unlink($tmp_file);
				break;
			}
			$current_version		= _json_decode(file_get_contents(MODULES.'/System/meta.json'))['version'];
			$new_version			= _json_decode(file_get_contents($tmp_dir.'/version'));
			if (!version_compare($current_version, $new_version, '<')) {
				$Page->warning($L->update_system_impossible_older_version);
				unlink($tmp_file);
				break;
			}
			$new_meta				= _json_decode(file_get_contents("$tmp_dir/fs.json"))['components/modules/System/meta.json'];
			$new_meta				= _json_decode(file_get_contents("$tmp_dir/fs/$new_meta"));
			if (isset($new_meta['update_from_version']) && version_compare($new_meta['update_from_version'], $current_version, '>')) {
				$Page->warning(
					$L->update_system_impossible_from_version_to($current_version, $new_version, $new_meta['update_from_version'])
				);
				unlink($tmp_file);
				break;
			}
			unset($new_meta);
			$rc[2]					= 'update_system';
			$show_modules			= false;
			$Page->title($L->updating_of_system);
			rename($tmp_file, $tmp_file = TEMP.'/'.$User->get_session().'_update_system.phar.php');
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->update_system(
						$current_version,
						$new_version
					)
				).
				h::{'button[type=submit]'}($L->yes)
			);
			$rc[3]					= 'System';
			$a->cancel_button_back	= true;
			break;
		break;
		case 'default_module':
			$show_modules			= false;
			$Page->title($L->setting_default_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->setting_default_module($rc[3])
				)
			);
			if (!Trigger::instance()->run(
				'admin/System/components/modules/default_module/prepare',
				[
					'name'	=> $rc[3]
				]
			)) {
				break;
			}
			$a->cancel_button_back	= true;
			$a->content(
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'db':
			$show_modules			= false;
			if (count($Config->db) > 1) {
				$Page->warning($L->changing_settings_warning);
				$Page->title($L->db_settings_for_module($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
						$L->db_settings_for_module($rc[3])
					)
				);
				if (!Trigger::instance()->run(
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
					$dbs					= [0 => $L->core_db.' ('.$Core->db_type.')'];
					foreach ($Config->db as $i => &$db_data) {
						if ($i) {
							$dbs[$i] = $db_data['name'].' ('.$db_data['host'].' / '.$db_data['type'].')';
						}
					}
					unset($i, $db_data);
					$db_list[] = h::{'th.ui-widget-header.ui-corner-all'}([
						h::info('appointment_of_db'),
						h::info('system_db')
					]);
					$db_json = _json_decode(file_get_contents(MODULES.'/'.$rc[3].'/meta/db.json'));
					foreach ($db_json as $database) {
						$db_list[] = h::{'td.ui-widget-content.ui-corner-all'}([
							$L->{$rc[3].'_db_'.$database},
							h::select(
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
				$Page->warning($L->changing_settings_warning);
				$Page->title($L->storage_settings_for_module($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
						$L->storage_settings_for_module($rc[3])
					)
				);
				if (!Trigger::instance()->run(
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
						h::info('appointment_of_storage'),
						h::info('system_storage')
					]);
					$storage_json = _json_decode(file_get_contents(MODULES.'/'.$rc[3].'/meta/storage.json'));
					foreach ($storage_json as $storage) {
						$storage_list[] = h::{'td.ui-widget-content.ui-corner-all'}([
							$L->{$rc[3].'_storage_'.$storage},
							h::select(
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
			$show_modules			= false;
			$Page->title($L->enabling_of_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->enable_module($rc[3])
				)
			);
			$a->cancel_button_back	= true;
			$a->content(
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'disable':
			$show_modules			= false;
			$Page->title($L->disabling_of_module($rc[3]));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->disable_module($rc[3])
				)
			);
			$a->cancel_button_back	= true;
			$a->content(
				h::{'button[type=submit]'}($L->yes)
			);
		break;
	}
	switch ($rc[2]) {
		case 'install':
		case 'uninstall':
		case 'update':
		case 'update_system':
		case 'default_module':
		case 'db':
		case 'storage':
		case 'enable':
		case 'disable':
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
$a->file_upload		= true;
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
	$admin_link		= false;
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
					$tag == 'pre' ? filter(file_get_contents($file)) : file_get_contents($file),
					[
						'id'			=> $module.'_api',
						'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
						'title'			=> $module.' » '.$L->api
					]
				);
			}
			$addition_state .= h::{'icon.cs-pointer'}(
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
				$tag		= 'pre';
			} else {
				$tag = 'div';
			}
			$uniqid			= uniqid('module_info_');
			$Page->replace($uniqid, $tag == 'pre' ? filter(file_get_contents($file)) : file_get_contents($file));
			$addition_state .= h::$tag(
				$uniqid,
				[
					'id'			=> $module.'_readme',
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $module.' » '.$L->information_about_module
				]
			).
			h::{'icon.cs-pointer'}(
				'notice',
				[
					'data-title'	=> $L->information_about_module.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#".$module."_readme').dialog('open');"
				]
			);
			unset($uniqid);
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
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $module.' » '.$L->license
				]
			).
			h::{'icon.cs-pointer'}(
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
		if (
			$mdata['active'] == 1 &&
			$module != $Config->core['default_module'] &&
			(
				file_exists(MODULES.'/'.$module.'/index.php') ||
				file_exists(MODULES.'/'.$module.'/index.html') ||
				file_exists(MODULES.'/'.$module.'/index.json')
			)
		) {
			$action .= h::{'a.cs-button-compact'}(
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
			$action .= h::{'a.cs-button-compact'}(
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
			$action .= h::{'a.cs-button-compact'}(
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
				$action		.= h::{'a.cs-button-compact'}(
					h::icon('wrench'),
					[
						'href'			=> 'admin/'.$module,
						'data-title'	=> $L->module_admin_page
					]
				);
				$admin_link	= true;
			}
			if ($module != $Config->core['default_module']) {
				$action		.= h::{'a.cs-button-compact'}(
					h::icon($mdata['active'] == 1 ? 'minusthick' : 'check'),
					[
						'href'			=> $a->action.($mdata['active'] == 1 ? '/disable/' : '/enable/').$module,
						'data-title'	=> $mdata['active'] == 1 ? $L->disable : $L->enable
					]
				).
				h::{'a.cs-button-compact'}(
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
		$action .= h::{'a.cs-button-compact'}(
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
			isset($module_meta['storage_support']) ? implode(', ', $module_meta['storage_support']) : $L->none,
			isset($module_meta['provide']) ? implode(', ', (array)$module_meta['provide']) : $L->none,
			isset($module_meta['require']) ? implode(', ', (array)$module_meta['require']) : $L->none,
			isset($module_meta['conflict']) ? implode(', ', (array)$module_meta['conflict']) : $L->none,
			isset($module_meta['multilingual']) && in_array('interface', $module_meta['multilingual']) ? $L->yes : $L->no,
			isset($module_meta['multilingual']) && in_array('content', $module_meta['multilingual']) ? $L->yes : $L->no,
			isset($module_meta['languages']) ? implode(', ', $module_meta['languages']) : $L->none
		);
	}
	unset($module_meta);
	$modules_list[]	= h::{'td.ui-widget-content.ui-corner-all'}(
		[
			h::a(
				$module,
				[
					'href'	=> $admin_link ? 'admin/'.$module : false
				]
			),
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
				'class'	=> 'cs-pointer cs-left-all'
			]
		]
	);
	unset($module_info);
}
$a->content(
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		$modules_list,
		h::{'td.cs-left-all[colspan=3]'}(
			h::{'input[type=file][name=upload_module]'}([
				'style'		=> 'position: relative;'
			]).
			h::{'button[type=submit]'}(
				$L->upload_and_install_update_module,
				[
					'formaction'	=>  $a->action.'/install/upload'
				]
			)
		),
		h::{'td.cs-left-all[colspan=3]'}(
			h::{'input[type=file][name=upload_system]'}([
				'style'		=> 'position: relative;'
			]).
			h::{'button[type=submit]'}(
				$L->upload_and_update_system,
				[
					'formaction'	=>  $a->action.'/update_system'
				]
			)
		)
	).
	h::{'button[type=submit]'}(
		$L->update_modules_list,
		[
			'data-title'	=> $L->update_modules_list_info,
			'name'			=> 'update_modules_list'
		]
	)
);