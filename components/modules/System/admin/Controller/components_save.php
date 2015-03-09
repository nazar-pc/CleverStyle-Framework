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
	cs\Cache,
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Event,
	cs\Group,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Permission,
	cs\Text,
	cs\User,
	h;

trait components_save {
	static function components_blocks_save () {
		$Cache      = Cache::instance();
		$Config     = Config::instance();
		$L          = Language::instance();
		$Text       = Text::instance();
		$Permission = Permission::instance();
		$a          = Index::instance();
		if (isset($_POST['mode'])) {
			switch ($_POST['mode']) {
				case 'add':
				case 'edit':
					$block_new = &$_POST['block'];
					if ($_POST['mode'] == 'add') {
						$block = [
							'position' => 'floating',
							'type'     => xap($block_new['type']),
							'index'    => substr(TIME, 3)
						];
					} else {
						$block = &$Config->components['blocks'][$block_new['id']];
					}
					$block['title']    = $Text->set(
						$Config->module('System')->db('texts'),
						'System/Config/blocks/title',
						$block['index'],
						$block_new['title']
					);
					$block['active']   = $block_new['active'];
					$block['template'] = $block_new['template'];
					$block['start']    = $block_new['start'];
					$start             = &$block_new['start'];
					$start             = explode('T', $start);
					$start[0]          = explode('-', $start[0]);
					$start[1]          = explode(':', $start[1]);
					$block['start']    = mktime($start[1][0], $start[1][1], 0, $start[0][1], $start[0][2], $start[0][0]);
					unset($start);
					if ($block_new['expire']['state']) {
						$expire          = &$block_new['expire']['date'];
						$expire          = explode('T', $expire);
						$expire[0]       = explode('-', $expire[0]);
						$expire[1]       = explode(':', $expire[1]);
						$block['expire'] = mktime($expire[1][0], $expire[1][1], 0, $expire[0][1], $expire[0][2], $expire[0][0]);
						unset($expire);
					} else {
						$block['expire'] = 0;
					}
					if ($block['type'] == 'html') {
						$block['content'] = $Text->set(
							$Config->module('System')->db('texts'),
							'System/Config/blocks/content',
							$block['index'],
							xap($block_new['html'], true)
						);
					} elseif ($block['type'] == 'raw_html') {
						$block['content'] = $Text->set(
							$Config->module('System')->db('texts'),
							'System/Config/blocks/content',
							$block['index'],
							$block_new['raw_html']
						);
					} elseif ($_POST['mode'] == 'add') {
						$block['content'] = '';
					}
					if ($_POST['mode'] == 'add') {
						$Config->components['blocks'][] = $block;
						$Permission->add('Block', $block['index']);
					} else {
						unset($Cache->{'blocks/'.$block['index'].'_'.$L->clang});
					}
					unset($block, $block_new);
					$a->save();
					break;
				case 'delete':
					if (isset($_POST['id'], $Config->components['blocks'][$_POST['id']])) {
						$block = &$Config->components['blocks'][$_POST['id']];
						$Permission->del(
							$Permission->get(
								null,
								'Block',
								$block['index']
							)[0]['id']
						);
						$Text->del(
							$Config->module('System')->db('texts'),
							'System/Config/blocks/title',
							$block['index']
						);
						$Text->del(
							$Config->module('System')->db('texts'),
							'System/Config/blocks/content',
							$block['index']
						);
						unset(
							$Cache->{'blocks/'.$block['index'].'_'.$L->clang},
							$block,
							$Config->components['blocks'][$_POST['id']]
						);
						$a->save();
					}
					break;
				case 'permissions':
					if (isset($_POST['block'], $_POST['block']['id'], $Config->components['blocks'][$_POST['block']['id']])) {
						$permission = $Permission->get(
							null,
							'Block',
							$Config->components['blocks'][$_POST['block']['id']]['index']
						)[0]['id'];
						$result     = true;
						if (isset($_POST['groups'])) {
							foreach ($_POST['groups'] as $group => $value) {
								$result = $result && Group::instance()->set_permissions([$permission => $value], $group);
							}
						}
						if (isset($_POST['users'])) {
							foreach ($_POST['users'] as $user => $value) {
								$result = $result && User::instance()->set_permissions([$permission => $value], $user);
							}
						}
						$a->save($result);
					}
					break;
			}
		} elseif (isset($_POST['apply']) || isset($_POST['save'])) {
			$_POST['position'] = _json_decode($_POST['position']);
			if (is_array($_POST['position'])) {
				$blocks_array = [];
				foreach ($_POST['position'] as $position => $items) {
					foreach ($items as $item) {
						switch ($position) {
							default:
								$position = 'floating';
								break;
							case 'top':
							case 'left':
							case 'floating':
							case 'right':
							case 'bottom':
								break;
						}
						$Config->components['blocks'][$item]['position'] = $position;
						$blocks_array[]                                  = $Config->components['blocks'][$item];
					}
				}
				$Config->components['blocks'] = [];
				$Config->components['blocks'] = $blocks_array;
				unset($blocks_array, $position, $items, $item);
				/** @noinspection NotOptimalIfConditionsInspection */
				if (isset($_POST['save'])) {
					$a->save();
				} else {
					$a->apply();
				}
			}
		} elseif (isset($_POST['cancel'])) {
			$a->cancel();
		}
	}
	static function components_databases_save () {
		if (!isset($_POST['mode'])) {
			return;
		}
		$Index  = Index::instance();
		$Config = Config::instance();
		$update = false;
		if ($_POST['mode'] == 'add') {
			$_POST['db']['mirrors'] = [];
			if ($_POST['db']['mirror'] == -1) {
				$Config->db[] = $_POST['db'];
			} else {
				$Config->db[$_POST['db']['mirror']]['mirrors'][] = $_POST['db'];
			}
			$update = true;
		} elseif ($_POST['mode'] == 'edit') {
			if (isset($_POST['mirror'])) {
				$cdb = &$Config->db[$_POST['database']]['mirrors'][$_POST['mirror']];
			} elseif ($_POST['database'] > 0) {
				$cdb = &$Config->db[$_POST['database']];
			}
			foreach ($_POST['db'] as $item => $value) {
				$cdb[$item] = $value;
			}
			unset($cdb, $item, $value);
			$update = true;
		} elseif ($_POST['mode'] == 'delete' && isset($_POST['database'])) {
			if (isset($_POST['mirror'])) {
				unset($Config->db[$_POST['database']]['mirrors'][$_POST['mirror']]);
				$update = true;
			} elseif ($_POST['database'] > 0) {
				unset($Config->db[$_POST['database']]);
				$update = true;
			}
		} elseif ($_POST['mode'] == 'config') {
			static::save();
		}
		if ($update) {
			$Index->save();
		}
		unset($update);
	}
	/**
	 * Provides next events:
	 *  admin/System/components/modules/install/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/uninstall/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/update/process/before
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/update/process/after
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/update_system/process/before
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/update_system/process/after
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/default_module/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/db/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/storage/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/enable/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/disable/process
	 *  ['name'    => module_name]
	 */
	static function components_modules_save () {
		$Cache      = Cache::instance();
		$Config     = Config::instance();
		$Core       = Core::instance();
		$db         = DB::instance();
		$L          = Language::instance();
		$Page       = Page::instance();
		$User       = User::instance();
		$Permission = Permission::instance();
		$a          = Index::instance();
		if (isset($_POST['update_modules_list'])) {
			/**
			 * List of currently presented modules in file system
			 */
			$modules_list = array_fill_keys(
				$new_modules = get_files_list(MODULES, false, 'd'),
				[
					'active'  => -1,
					'db'      => [],
					'storage' => []
				]
			);
			/**
			 * Already known modules
			 */
			$modules     = &$Config->components['modules'];
			$old_modules = array_keys($modules);
			/**
			 * Deletion of undefined modules permissions
			 */
			if ($new_modules != $old_modules) {
				$permissions_ids = [];
				foreach ($old_modules as $module_name) {
					if (!isset($modules_list[$module_name])) {
						/** @noinspection SlowArrayOperationsInLoopInspection */
						$permissions_ids = array_merge(
							$permissions_ids,
							(array)$Permission->get(null, $module_name),
							(array)$Permission->get(null, "admin/$module_name"),
							(array)$Permission->get(null, "api/$module_name")
						);
					}
				}
				unset($old_modules, $module_name);
				if (!empty($permissions_ids)) {
					foreach ($permissions_ids as &$id) {
						$id = $id['id'];
					}
					unset($id);
					$Permission->del($permissions_ids);
				}
				unset($permissions_ids);
			}
			unset($new_modules, $old_modules);
			$modules = array_merge($modules_list, array_intersect_key($modules, $modules_list));
			ksort($modules, SORT_STRING | SORT_FLAG_CASE);
			$a->save();
		} elseif (isset($_POST['mode'], $_POST['module'], $Config->components['modules'][$_POST['module']])) {
			$module_name = $_POST['module'];
			$module_data = &$Config->components['modules'][$module_name];
			switch ($_POST['mode']) {
				case 'install':
					if ($module_data['active'] != -1) {
						break;
					}
					unset($Cache->languages);
					if (!Event::instance()->fire(
						'admin/System/components/modules/install/process',
						[
							'name' => $module_name
						]
					)
					) {
						break;
					}
					$module_data['active'] = 0;
					if (isset($_POST['db']) && is_array($_POST['db']) && file_exists(MODULES."/$module_name/meta/db.json")) {
						$module_data['db'] = $_POST['db'];
						$db_json           = file_get_json(MODULES."/$module_name/meta/db.json");
						time_limit_pause();
						foreach ($db_json as $database) {
							if ($module_data['db'][$database] == 0) {
								$db_type = $Core->db_type;
							} else {
								$db_type = $Config->db[$module_data['db'][$database]]['type'];
							}
							$sql_file = MODULES."/$module_name/meta/install_db/$database/$db_type.sql";
							if (file_exists($sql_file)) {
								$db->{$module_data['db'][$database]}()->q(
									explode(';', file_get_contents($sql_file))
								);
							}
						}
						unset($db_json, $database, $db_type, $sql_file);
						time_limit_pause(false);
					}
					if (isset($_POST['storage']) && is_array($_POST['storage'])) {
						$module_data['storage'] = $_POST['storage'];
					}
					if ($a->save()) {
						$Page->notice(
							h::{'p.cs-center'}(
								"$L->module_installed_but_not_enabled ".
								h::{'a.uk-button'}(
									$L->enable_module($module_name),
									[
										'href' => "admin/System/components/modules/enable/$module_name"
									]
								)
							)
						);
					}
					clean_pcache();
					unset($Cache->functionality);
					clean_classes_cache();
					break;
				case 'uninstall':
					if ($module_name == 'System' || $module_data['active'] == -1 || $module_name == $Config->core['default_module']) {
						break;
					}
					unset($Cache->languages);
					if (!Event::instance()->fire(
						'admin/System/components/modules/uninstall/process',
						[
							'name' => $module_name
						]
					)
					) {
						break;
					}
					$module_data['active'] = -1;
					Event::instance()->fire(
						'admin/System/components/modules/disable',
						[
							'name' => $module_name
						]
					);
					$Config->save();
					if (isset($module_data['db']) && file_exists(MODULES."/$module_name/meta/db.json")) {
						$db_json = file_get_json(MODULES."/$module_name/meta/db.json");
						time_limit_pause();
						foreach ($db_json as $database) {
							if ($module_data['db'][$database] == 0) {
								$db_type = $Core->db_type;
							} else {
								$db_type = $Config->db[$module_data['db'][$database]]['type'];
							}
							$sql_file = MODULES."/$module_name/meta/uninstall_db/$database/$db_type.sql";
							if (file_exists($sql_file)) {
								$db->{$module_data['db'][$database]}()->q(
									explode(';', file_get_contents($sql_file))
								);
							}
						}
						unset($db_json, $database, $db_type, $sql_file);
						time_limit_pause(false);
					}
					$permissions_ids = array_merge(
						$Permission->get(null, $module_name),
						$Permission->get(null, "$module_name/admin"),
						$Permission->get(null, "$module_name/api")
					);
					if (!empty($permissions_ids)) {
						foreach ($permissions_ids as &$id) {
							$id = $id['id'];
						}
						$Permission->del($permissions_ids);
					}
					$module_data = ['active' => -1];
					$a->save();
					clean_pcache();
					unset($Cache->functionality);
					clean_classes_cache();
					break;
				case 'update':
					/**
					 * Temporary disable module
					 */
					$active = $module_data['active'];
					if ($active) {
						$module_data['active'] = 0;
						$Config->save();
						Event::instance()->fire(
							'admin/System/components/modules/disable',
							[
								'name' => $module_name
							]
						);
						Event::instance()->fire(
							'admin/System/components/modules/update/process/before',
							[
								'name' => $module_name
							]
						);
					}
					$module_dir = MODULES."/$module_name";
					/**
					 * Backing up some necessary information about current version
					 */
					copy("$module_dir/fs.json", "$module_dir/fs_old.json");
					copy("$module_dir/meta.json", "$module_dir/meta_old.json");
					/**
					 * Extracting new versions of files
					 */
					$tmp_file = TEMP.'/'.$User->get_session_id().'_module_update.phar';
					$tmp_dir  = "phar://$tmp_file";
					$fs       = file_get_json("$tmp_dir/fs.json");
					$extract  = array_product(
						array_map(
							function ($index, $file) use ($tmp_dir, $module_dir) {
								if (
									!file_exists(dirname("$module_dir/$file")) &&
									!mkdir(dirname("$module_dir/$file"), 0770, true)
								) {
									return 0;
								}
								return (int)copy("$tmp_dir/fs/$index", "$module_dir/$file");
							},
							$fs,
							array_keys($fs)
						)
					);
					unlink($tmp_file);
					unset($tmp_file, $tmp_dir);
					if (!$extract) {
						$Page->warning($L->module_files_unpacking_error);
						unlink("$module_dir/fs_old.json");
						unlink("$module_dir/meta_old.json");
						break;
					}
					unset($extract);
					file_put_json("$module_dir/fs.json", $fs = array_keys($fs));
					/**
					 * Removing of old unnecessary files and directories
					 */
					foreach (array_diff(file_get_json("$module_dir/fs_old.json"), $fs) as $file) {
						$file = "$module_dir/$file";
						if (file_exists($file) && is_writable($file)) {
							unlink($file);
							if (!get_files_list($dir = dirname($file))) {
								rmdir($dir);
							}
						}
					}
					unset($fs, $file, $dir);
					/**
					 * Updating of module
					 */
					if ($active && file_exists("$module_dir/versions.json")) {
						$old_version = file_get_json("$module_dir/meta_old.json")['version'];
						foreach (file_get_json("$module_dir/versions.json") as $version) {
							if (version_compare($old_version, $version, '<')) {
								/**
								 * PHP update script
								 */
								_include("$module_dir/meta/update/$version.php", true, false);
								/**
								 * Database update
								 */
								if (isset($module_data['db']) && file_exists("$module_dir/meta/db.json")) {
									$db_json = file_get_json("$module_dir/meta/db.json");
									time_limit_pause();
									foreach ($db_json as $database) {
										if ($module_data['db'][$database] == 0) {
											$db_type = $Core->db_type;
										} else {
											$db_type = $Config->db[$module_data['db'][$database]]['type'];
										}
										$sql_file = "$module_dir/meta/update_db/$database/$version/$db_type.sql";
										if (file_exists($sql_file)) {
											$db->{$module_data['db'][$database]}()->q(
												explode(';', file_get_contents($sql_file))
											);
										}
									}
									unset($db_json, $database, $db_type, $sql_file);
									time_limit_pause(false);
								}
							}
						}
						unset($old_version);
					}
					unlink("$module_dir/fs_old.json");
					unlink("$module_dir/meta_old.json");
					/**
					 * Restore previous module state
					 */
					if ($active) {
						$module_data['active'] = 1;
						$Config->save();
						clean_pcache();
						Event::instance()->fire(
							'admin/System/components/modules/enable',
							[
								'name' => $module_name
							]
						);
						Event::instance()->fire(
							'admin/System/components/modules/update/process/after',
							[
								'name' => $module_name
							]
						);
						unset($Cache->languages);
					}
					$a->save();
					unset($Cache->functionality);
					clean_classes_cache();
					break;
				case 'update_system':
					/**
					 * Temporary close site
					 */
					$site_mode = $Config->core['site_mode'];
					if ($site_mode) {
						$Config->core['site_mode'] = 0;
						$Config->save();
					}
					Event::instance()->fire(
						'admin/System/components/modules/update_system/process/before',
						[
							'name' => $module_name
						]
					);
					$module_dir = MODULES.'/System';
					/**
					 * Backing up some necessary information about current version
					 */
					copy(DIR.'/core/fs.json', DIR.'/core/fs_old.json');
					copy("$module_dir/meta.json", "$module_dir/meta_old.json");
					/**
					 * Extracting new versions of files
					 */
					$tmp_file = TEMP.'/'.$User->get_session_id().'_update_system.phar';
					$tmp_dir  = "phar://$tmp_file";
					$fs       = file_get_json("$tmp_dir/fs.json")['core/fs.json'];
					$fs       = file_get_json("$tmp_dir/fs/$fs");
					$extract  = array_product(
						array_map(
							function ($index, $file) use ($tmp_dir, $module_dir) {
								if (
									!file_exists(dirname(DIR."/$file")) &&
									!mkdir(dirname(DIR."/$file"), 0770, true)
								) {
									return 0;
								}
								return (int)copy("$tmp_dir/fs/$index", DIR."/$file");
							},
							$fs,
							array_keys($fs)
						)
					);
					unlink($tmp_file);
					unset($tmp_file, $tmp_dir);
					if (!$extract) {
						$Page->warning($L->system_files_unpacking_error);
						unlink(DIR.'/core/fs_old.json');
						unlink("$module_dir/meta_old.json");
						break;
					}
					unset($extract);
					file_put_json(DIR.'/core/fs.json', $fs = array_keys($fs));
					/**
					 * Removing of old unnecessary files and directories
					 */
					foreach (array_diff(file_get_json(DIR.'/core/fs_old.json'), $fs) as $file) {
						$file = DIR."/$file";
						if (file_exists($file) && is_writable($file)) {
							unlink($file);
							if (!get_files_list($dir = dirname($file))) {
								rmdir($dir);
							}
						}
					}
					unset($fs, $file, $dir);
					/**
					 * Updating of System
					 */
					if (file_exists("$module_dir/versions.json")) {
						$old_version = file_get_json("$module_dir/meta_old.json")['version'];
						foreach (file_get_json("$module_dir/versions.json") as $version) {
							if (version_compare($old_version, $version, '<')) {
								/**
								 * PHP update script
								 */
								_include("$module_dir/meta/update/$version.php", true, false);
								/**
								 * Database update
								 */
								if (isset($module_data['db']) && file_exists("$module_dir/meta/db.json")) {
									$db_json = file_get_json("$module_dir/meta/db.json");
									time_limit_pause();
									foreach ($db_json as $database) {
										if ($module_data['db'][$database] == 0) {
											$db_type = $Core->db_type;
										} else {
											$db_type = $Config->db[$module_data['db'][$database]]['type'];
										}
										$sql_file = "$module_dir/meta/update_db/$database/$version/$db_type.sql";
										if (file_exists($sql_file)) {
											$db->{$module_data['db'][$database]}()->q(
												explode(';', file_get_contents($sql_file))
											);
										}
									}
									unset($db_json, $database, $db_type, $sql_file);
									time_limit_pause(false);
								}
							}
						}
						unset($old_version);
					}
					unlink(DIR.'/core/fs_old.json');
					unlink("$module_dir/meta_old.json");
					/**
					 * Restore previous site mode
					 */
					if ($site_mode) {
						$Config->core['site_mode'] = 1;
					}
					$a->save();
					clean_pcache();
					clean_classes_cache();
					Event::instance()->fire(
						'admin/System/components/modules/update_system/process/after',
						[
							'name' => $module_name
						]
					);
					break;
				case 'default_module':
					if (
						$module_data['active'] != 1 ||
						$module_name == $Config->core['default_module'] ||
						!(
							file_exists(MODULES."/$module_name/index.php") ||
							file_exists(MODULES."/$module_name/index.html") ||
							file_exists(MODULES."/$module_name/index.json")
						)
					) {
						break;
					}
					if (Event::instance()->fire(
						'admin/System/components/modules/default_module/process',
						[
							'name' => $module_name
						]
					)
					) {
						$Config->core['default_module'] = $module_name;
						$a->save();
					}
					break;
				case 'db':
					/** @noinspection NotOptimalIfConditionsInspection */
					if (
						Event::instance()->fire(
							'admin/System/components/modules/db/process',
							[
								'name' => $module_name
							]
						) &&
						isset($_POST['db']) &&
						is_array($_POST['db']) &&
						count($Config->db) > 1
					) {
						$module_data['db'] = xap($_POST['db']);
						$a->save();
					}
					break;
				case 'storage':
					/** @noinspection NotOptimalIfConditionsInspection */
					if (
						Event::instance()->fire(
							'admin/System/components/modules/storage/process',
							[
								'name' => $module_name
							]
						) &&
						isset($_POST['storage']) &&
						is_array($_POST['storage']) &&
						count($Config->storage) > 1
					) {
						$module_data['storage'] = xap($_POST['storage']);
						$a->save();
					}
					break;
				case 'enable':
					$module_data['active'] = 1;
					$a->save();
					clean_pcache();
					//TODO remove in future versions
					Event::instance()->fire(
						'admin/System/components/modules/enable',
						[
							'name' => $module_name
						]
					);
					Event::instance()->fire(
						'admin/System/components/modules/enable/process',
						[
							'name' => $module_name
						]
					);
					unset(
						$Cache->functionality,
						$Cache->languages
					);
					clean_classes_cache();
					break;
				case 'disable':
					$module_data['active'] = 0;
					$a->save();
					clean_pcache();
					//TODO remove in future versions
					Event::instance()->fire(
						'admin/System/components/modules/disable',
						[
							'name' => $module_name
						]
					);
					Event::instance()->fire(
						'admin/System/components/modules/disable/process',
						[
							'name' => $module_name
						]
					);
					unset(
						$Cache->functionality,
						$Cache->languages
					);
					clean_classes_cache();
					break;
				case 'remove':
					if ($module_name == 'System' || $module_data['active'] != '-1') {
						break;
					}
					$ok = true;
					get_files_list(
						MODULES."/$module_name",
						false,
						'fd',
						true,
						true,
						false,
						false,
						true,
						function ($item) use (&$ok) {
							if (is_writable($item)) {
								is_dir($item) ? @rmdir($item) : @unlink($item);
							} else {
								$ok = false;
							}
						}
					);
					if ($ok && @rmdir(MODULES."/$module_name")) {
						unset($Config->components['modules'][$module_name]);
						$a->save();
					} else {
						$a->save(false);
					}
					break;
			}
		}
	}
	/**
	 * Provides next events:
	 *  admin/System/components/plugins/enable/process
	 *  ['name'    => plugin_name]
	 *
	 *  admin/System/components/plugins/disable/process
	 *  ['name'    => plugin_name]
	 *
	 *  admin/System/components/plugins/update/process/before
	 *  ['name'    => plugin_name]
	 *
	 *  admin/System/components/plugins/update/process/after
	 *  ['name'    => plugin_name]
	 *
	 *  admin/System/components/plugins/update/process/before
	 *  ['name'    => plugin_name]
	 *
	 *  admin/System/components/plugins/update/process/after
	 *  ['name'    => plugin_name]
	 */
	static function components_plugins_save () {
		$Cache   = Cache::instance();
		$Config  = Config::instance();
		$Index   = Index::instance();
		$L       = Language::instance();
		$Page    = Page::instance();
		$plugins = get_files_list(PLUGINS, false, 'd');
		if (isset($_POST['mode'], $_POST['plugin'])) {
			$plugin = $_POST['plugin'];
			switch ($_POST['mode']) {
				case 'enable':
					if (in_array($plugin, $plugins) && !in_array($plugin, $Config->components['plugins'])) {
						$Config->components['plugins'][] = $plugin;
						$Index->save();
						clean_pcache();
						Event::instance()->fire(
							'admin/System/components/plugins/enable',
							[
								'name' => $plugin
							]
						);
						Event::instance()->fire(
							'admin/System/components/plugins/enable/process',
							[
								'name' => $plugin
							]
						);
						unset($Cache->functionality);
					}
					clean_classes_cache();
					break;
				case 'disable':
					if (in_array($plugin, $Config->components['plugins'])) {
						unset($Config->components['plugins'][array_search($plugin, $Config->components['plugins'])]);
						$Index->save();
						clean_pcache();
						Event::instance()->fire(
							'admin/System/components/plugins/disable',
							[
								'name' => $plugin
							]
						);
						Event::instance()->fire(
							'admin/System/components/plugins/disable/process',
							[
								'name' => $plugin
							]
						);
						unset($Cache->functionality);
					}
					clean_classes_cache();
					break;
				case 'update':
					/**
					 * Temporary disable plugin
					 */
					$active = in_array($plugin, $Config->components['plugins']);
					if ($active) {
						unset($Config->components['plugins'][array_search($plugin, $Config->components['plugins'])]);
						$Config->save();
						Event::instance()->fire(
							'admin/System/components/plugins/disable',
							[
								'name' => $plugin
							]
						);
						Event::instance()->fire(
							'admin/System/components/plugins/update/process/before',
							[
								'name' => $plugin
							]
						);
					}
					$plugin_dir = PLUGINS."/$plugin";
					/**
					 * Backing up some necessary information about current version
					 */
					copy("$plugin_dir/fs.json", "$plugin_dir/fs_old.json");
					copy("$plugin_dir/meta.json", "$plugin_dir/meta_old.json");
					/**
					 * Extracting new versions of files
					 */
					$tmp_file = TEMP.'/'.User::instance()->get_session_id().'_plugin_update.phar';
					$tmp_dir  = "phar://$tmp_file";
					$fs       = file_get_json("$tmp_dir/fs.json");
					$extract  = array_product(
						array_map(
							function ($index, $file) use ($tmp_dir, $plugin_dir) {
								if (
									!file_exists(dirname("$plugin_dir/$file")) &&
									!mkdir(dirname("$plugin_dir/$file"), 0770, true)
								) {
									return 0;
								}
								return (int)copy("$tmp_dir/fs/$index", "$plugin_dir/$file");
							},
							$fs,
							array_keys($fs)
						)
					);
					unlink($tmp_file);
					unset($tmp_file, $tmp_dir);
					if (!$extract) {
						$Page->warning($L->plugin_files_unpacking_error);
						unlink("$plugin_dir/fs_old.json");
						unlink("$plugin_dir/meta_old.json");
						break;
					}
					unset($extract);
					file_put_json("$plugin_dir/fs.json", $fs = array_keys($fs));
					/**
					 * Removing of old unnecessary files and directories
					 */
					foreach (array_diff(file_get_json("$plugin_dir/fs_old.json"), $fs) as $file) {
						$file = "$plugin_dir/$file";
						if (file_exists($file) && is_writable($file)) {
							unlink($file);
							if (!get_files_list($dir = dirname($file))) {
								rmdir($dir);
							}
						}
					}
					unset($fs, $file, $dir);
					/**
					 * Updating of plugin
					 */
					if (file_exists("$plugin_dir/versions.json")) {
						$old_version = file_get_json("$plugin_dir/meta_old.json")['version'];
						foreach (file_get_json("$plugin_dir/versions.json") as $version) {
							if (version_compare($old_version, $version, '<')) {
								/**
								 * PHP update script
								 */
								_include("$plugin_dir/meta/update/$version.php", true, false);
							}
						}
						unset($old_version);
					}
					unlink("$plugin_dir/fs_old.json");
					unlink("$plugin_dir/meta_old.json");
					/**
					 * Restore previous plugin state
					 */
					if ($active) {
						$Config->components['plugins'][] = $plugin;
						$Config->save();
						clean_pcache();
						Event::instance()->fire(
							'admin/System/components/plugins/enable',
							[
								'name' => $plugin
							]
						);
						Event::instance()->fire(
							'admin/System/components/plugins/update/process/after',
							[
								'name' => $plugin
							]
						);
					}
					$Index->save();
					unset($Cache->functionality);
					clean_classes_cache();
					break;
				case 'remove':
					if (in_array($plugin, $Config->components['plugins'])) {
						break;
					}
					$ok = true;
					get_files_list(
						PLUGINS."/$plugin",
						false,
						'fd',
						true,
						true,
						false,
						false,
						true,
						function ($item) use (&$ok) {
							if (is_writable($item)) {
								is_dir($item) ? @rmdir($item) : @unlink($item);
							} else {
								$ok = false;
							}
						}
					);
					if ($ok && @rmdir(PLUGINS."/$plugin")) {
						$Index->save();
					} else {
						$Index->save(false);
					}
					break;
			}
		}
	}
	static function components_storages_save () {
		if (!isset($_POST['mode'])) {
			return;
		}
		$Config = Config::instance();
		$update = false;
		if ($_POST['mode'] == 'add') {
			$Config->storage[] = $_POST['storage'];
			$update            = true;
		} elseif ($_POST['mode'] == 'edit' && $_POST['storage_id'] > 0) {
			$current_storage = &$Config->storage[$_POST['storage_id']];
			foreach ($_POST['storage'] as $item => $value) {
				$current_storage[$item] = $value;
			}
			unset($current_storage, $item, $value);
			$update = true;
		} elseif ($_POST['mode'] == 'delete' && isset($_POST['storage']) && $_POST['storage'] > 0) {
			unset($Config->storage[$_POST['storage']]);
			$update = true;
		}
		if ($update) {
			Index::instance()->save();
		}
		unset($update);
	}
}
