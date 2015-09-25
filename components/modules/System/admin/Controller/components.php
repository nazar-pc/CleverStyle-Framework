<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\Session,
	h,
	cs\modules\System\Packages_manipulation;
trait components {
	static function components_blocks () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_blocks_list()
		);
	}
	static function components_databases () {
		$Config              = Config::instance();
		$L                   = Language::instance();
		$Index               = Index::instance();
		$Index->apply_button = true;
		$Index->content(
			h::cs_system_admin_databases_list().
			static::vertical_table(
				[
					[
						h::info('db_balance'),
						h::radio(
							[
								'name'    => 'core[db_balance]',
								'checked' => $Config->core['db_balance'],
								'value'   => [0, 1],
								'in'      => [$L->off, $L->on]
							]
						)
					],
					[
						h::info('db_mirror_mode'),
						h::radio(
							[
								'name'    => 'core[db_mirror_mode]',
								'checked' => $Config->core['db_mirror_mode'],
								'value'   => [DB::MIRROR_MODE_MASTER_MASTER, DB::MIRROR_MODE_MASTER_SLAVE],
								'in'      => [$L->master_master, $L->master_slave]
							]
						)
					]
				]
			)
		);
	}
	/**
	 * Provides next events:
	 *  admin/System/components/modules/install/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/update_system/prepare
	 *
	 *  admin/System/components/modules/db/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/storage/prepare
	 *  ['name' => module_name]
	 */
	static function components_modules () {
		$Config       = Config::instance();
		$L            = Language::instance();
		$Page         = Page::instance();
		$Session      = Session::instance();
		$a            = Index::instance();
		$rc           = Route::instance()->route;
		$a->buttons   = false;
		$show_modules = true;
		if (
			isset($rc[2]) &&
			!empty($rc[2]) &&
			(
				in_array($rc[2], ['update_system', 'remove']) ||
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
					$show_modules = false;
					$Page->title($L->installation_of_module($rc[3]));
					$a->content(
						h::{'h2.cs-text-center'}(
							$L->installation_of_module($rc[3])
						)
					);
					if (!Event::instance()->fire(
						'admin/System/components/modules/install/prepare',
						[
							'name' => $rc[3]
						]
					)
					) {
						break;
					}
					$check_dependencies = true;
					if (file_exists(MODULES."/$rc[3]/meta.json")) {
						$meta               = file_get_json(MODULES."/$rc[3]/meta.json");
						$check_dependencies = Packages_manipulation::get_dependencies($meta);
						if (!$check_dependencies && $Config->core['simple_admin_mode']) {
							break;
						}
						if (isset($meta['optional'])) {
							$Page->success(
								$L->for_complete_feature_set(
									implode(', ', (array)$meta['optional'])
								)
							);
						}
						if ($Config->core['simple_admin_mode']) {
							if (isset($meta['db'])) {
								foreach ($meta['db'] as $database) {
									$a->content(
										h::{'input[type=hidden]'}(
											[
												'name'  => "db[$database]",
												'value' => 0
											]
										)
									);
								}
								unset($database);
							}
							if (isset($meta['storage'])) {
								foreach ($meta['storage'] as $storage) {
									$a->content(
										h::{'input[type=hidden]'}(
											[
												'name'  => "storage[$storage]",
												'value' => 0
											]
										)
									);
								}
								unset($storage);
							}
						} else {
							goto module_db_settings;
							back_to_module_installation_1:
							goto module_storage_settings;
							back_to_module_installation_2:
						}
						unset($meta);
					}
					$a->cancel_button_back = true;
					$a->content(
						h::{'button[is=cs-button][type=submit]'}(
							$L->{$check_dependencies ? 'install' : 'force_install_not_recommended'}
						)
					);
					break;
				case 'update_system':
					$tmp_file = Packages_manipulation::move_uploaded_file_to_tmp('upload_system');
					if (!$tmp_file) {
						break;
					}
					$tmp_dir = "phar://$tmp_file";
					if (
						!file_exists("$tmp_dir/meta.json") ||
						!file_exists("$tmp_dir/modules.json") ||
						!file_exists("$tmp_dir/plugins.json") ||
						!file_exists("$tmp_dir/themes.json")
					) {
						$Page->warning($L->this_is_not_system_installer_file);
						unlink($tmp_file);
						break;
					}
					$meta            = file_get_json("$tmp_dir/meta.json");
					$current_version = file_get_json(MODULES.'/System/meta.json')['version'];
					if (!version_compare($current_version, $meta['version'], '<')) {
						$Page->warning($L->update_system_impossible_older_version);
						unlink($tmp_file);
						break;
					}
					if (isset($meta['update_from_version']) && version_compare($meta['update_from_version'], $current_version, '>')) {
						$Page->warning(
							$L->update_system_impossible_from_version_to($current_version, $meta['version'], $meta['update_from_version'])
						);
						unlink($tmp_file);
						break;
					}
					$rc[2]        = 'update_system';
					$show_modules = false;
					if (!Event::instance()->fire('admin/System/components/modules/update_system/prepare')) {
						break;
					}
					$Page->title($L->updating_of_system);
					rename($tmp_file, $tmp_file = TEMP.'/'.$Session->get_id().'_update_system.phar');
					$a->content(
						h::{'h2.cs-text-center'}(
							$L->update_system(
								$current_version,
								$meta['version']
							)
						).
						h::{'button[is=cs-button][type=submit]'}($L->yes)
					);
					unset($meta);
					$rc[3]                 = 'System';
					$a->cancel_button_back = true;
					break;
				case 'db':
					$show_modules = false;
					if (count($Config->db) > 1) {
						$Page->warning($L->changing_settings_warning);
						$Page->title($L->db_settings_for_module($rc[3]));
						$a->content(
							h::{'h2.cs-text-center'}(
								$L->db_settings_for_module($rc[3])
							)
						);
						if (!Event::instance()->fire(
							'admin/System/components/modules/db/prepare',
							[
								'name' => $rc[3]
							]
						)
						) {
							break;
						}
						$a->buttons            = true;
						$a->cancel_button_back = true;
						module_db_settings:
						$Core = Core::instance();
						$dbs  = [0 => "$L->core_db ($Core->db_type)"];
						foreach ($Config->db as $i => &$db_data) {
							if ($i) {
								$dbs[$i] = "$db_data[name] ($db_data[host] / $db_data[type])";
							}
						}
						unset($i, $db_data);
						$db_list = [];
						if (file_exists(MODULES."/$rc[3]/meta.json")) {
							$meta = file_get_json(MODULES."/$rc[3]/meta.json");
							foreach (isset($meta['db']) ? $meta['db'] : [] as $database) {
								$db_list[] = [
									$database,
									h::{'select[is=cs-select]'}(
										[
											'in'    => array_values($dbs),
											'value' => array_keys($dbs)
										],
										[
											'name'     => "db[$database]",
											'selected' => isset($Config->components['modules'][$rc[3]]['db'][$database]) ?
												$Config->components['modules'][$rc[3]]['db'][$database] : 0,
											'size'     => 5
										]
									)
								];
							}
							unset($dbs, $database);
						}
						if ($db_list) {
							$a->content(
								h::{'table.cs-table[right-left] tr| td'}(
									[
										h::info('appointment_of_db'),
										h::info('system_db')
									],
									$db_list
								)
							);
						}
						unset($db_list);
						if ($rc[2] == 'install') {
							goto back_to_module_installation_1;
						}
					}
					break;
				case 'storage':
					$show_modules = false;
					if (count($Config->storage) > 1) {
						$Page->warning($L->changing_settings_warning);
						$Page->title($L->storage_settings_for_module($rc[3]));
						$a->content(
							h::{'h2.cs-text-center'}(
								$L->storage_settings_for_module($rc[3])
							)
						);
						if (!Event::instance()->fire(
							'admin/System/components/modules/storage/prepare',
							[
								'name' => $rc[3]
							]
						)
						) {
							break;
						}
						$a->buttons            = true;
						$a->cancel_button_back = true;
						module_storage_settings:
						$storages = [0 => $L->core_storage];
						foreach ($Config->storage as $i => &$storage_data) {
							if ($i) {
								$storages[$i] = "$storage_data[host] ($storage_data[connection])";
							}
						}
						unset($i, $storage_data);
						$storage_list = [];
						if (file_exists(MODULES."/$rc[3]/meta.json")) {
							$meta = file_get_json(MODULES."/$rc[3]/meta.json");
							foreach (isset($meta['storage']) ? $meta['storage'] : [] as $storage) {
								$storage_list[] = [
									$storage,
									h::{'select[is=cs-select]'}(
										[
											'in'    => array_values($storages),
											'value' => array_keys($storages)
										],
										[
											'name'     => "storage[$storage]",
											'selected' => isset($Config->components['modules'][$rc[3]]['storage'][$storage]) ?
												$Config->components['modules'][$rc[3]]['storage'][$storage] : 0,
											'size'     => 5
										]
									)
								];
							}
							unset($storages, $storage);
						}
						if ($storage_list) {
							$a->content(
								h::{'table.cs-table[right-left] tr| td'}(
									[
										h::info('appointment_of_storage'),
										h::info('system_storage')
									],
									$storage_list
								)
							);
						}
						unset($storage_list);
						if ($rc[2] == 'install') {
							goto back_to_module_installation_2;
						}
					}
					break;
			}
			switch ($rc[2]) {
				case 'install':
				case 'update_system':
				case 'db':
				case 'storage':
					$a->content(
						h::{'input[type=hidden]'}(
							[
								'name'  => 'mode',
								'value' => $rc[2]
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'module',
								'value' => $rc[3]
							]
						)
					);
			}
		}
		unset($rc);
		if (!$show_modules) {
			return;
		}
		$a->file_upload = true;
		$a->content(
			h::cs_system_admin_modules_list().
			h::p(
				h::{'input[is=cs-input-text][compact][tight][type=file][name=upload_system]'}().
				h::{'button[is=cs-button][icon=upload][type=submit]'}(
					$L->upload_and_update_system,
					[
						'formaction' => "$a->action/update_system"
					]
				)
			).
			h::{'button[is=cs-button][icon=refresh][type=submit]'}(
				$L->update_modules_list,
				[
					'tooltip' => $L->update_modules_list_info,
					'name'    => 'update_modules_list'
				]
			)
		);
	}
	static function components_plugins () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_plugins_list()
		);
	}
	static function components_storages () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_storages_list()
		);
	}
}
