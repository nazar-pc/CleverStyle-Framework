<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  admin/System/components/modules/install/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/uninstall/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/default_module/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/db/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/storage/process<br>
 *  ['name'	=> <i>module_name</i>]
 */
namespace	cs;
use h;
$Cache		= Cache::instance();
$Config		= Config::instance();
$Core		= Core::instance();
$db			= DB::instance();
$L			= Language::instance();
$Page		= Page::instance();
$User		= User::instance();
$Permission	= Permission::instance();
$a			= Index::instance();
if (isset($_POST['update_modules_list'])) {
	/**
	 * List of currently presented modules in file system
	 */
	$modules_list	= array_fill_keys(
		$new_modules = get_files_list(MODULES, false, 'd'),
		[
			'active'	=> -1,
			'db'		=> [],
			'storage'	=> []
		]
	);
	/**
	 * Already known modules
	 */
	$modules		= &$Config->components['modules'];
	$old_modules	= array_keys($modules);
	/**
	 * Deletion of undefined modules permissions
	 */
	if ($new_modules != $old_modules) {
		$permissions_ids = [];
		foreach ($old_modules as $module_name) {
			if (!isset($modules_list[$module_name])) {
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
	$modules			= array_merge($modules_list, array_intersect_key($modules, $modules_list));
	ksort($modules, SORT_STRING | SORT_FLAG_CASE);
	$a->save();
} elseif (isset($_POST['mode'], $_POST['module'], $Config->components['modules'][$_POST['module']])) {
	$module_name	= $_POST['module'];
	$module_data	= &$Config->components['modules'][$module_name];
	switch ($_POST['mode']) {
		case 'install':
			if ($module_data['active'] != -1) {
				break;
			}
			unset($Cache->languages);
			if (!Trigger::instance()->run(
				'admin/System/components/modules/install/process',
				[
					'name' => $module_name
				]
			)) {
				break;
			}
			$module_data['active'] = 0;
			if (isset($_POST['db']) && is_array($_POST['db']) && file_exists(MODULES."/$module_name/meta/db.json")) {
				$module_data['db'] = $_POST['db'];
				$db_json = file_get_json(MODULES."/$module_name/meta/db.json");
				time_limit_pause();
				foreach ($db_json as $database) {
					if ($module_data['db'][$database] == 0) {
						$db_type	= $Core->db_type;
					} else {
						$db_type	= $Config->db[$module_data['db'][$database]]['type'];
					}
					$sql_file	= MODULES."/$module_name/meta/install_db/$database/$db_type.sql";
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
								'href'	=> "admin/System/components/modules/enable/$module_name"
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
			if ($module_data['active'] == -1 || $module_name == 'System' || $module_name == $Config->core['default_module']) {
				break;
			}
			unset($Cache->languages);
			if (!Trigger::instance()->run(
				'admin/System/components/modules/uninstall/process',
				[
					'name' => $module_name
				]
			)) {
				break;
			}
			$module_data['active']	= -1;
			Trigger::instance()->run(
				'admin/System/components/modules/disable',
				[
					'name'	=> $module_name
				]
			);
			$Config->save();
			if (isset($module_data['db']) && file_exists(MODULES."/$module_name/meta/db.json")) {
				$db_json = file_get_json(MODULES."/$module_name/meta/db.json");
				time_limit_pause();
				foreach ($db_json as $database) {
					if ($module_data['db'][$database] == 0) {
						$db_type	= $Core->db_type;
					} else {
						$db_type	= $Config->db[$module_data['db'][$database]]['type'];
					}
					$sql_file	= MODULES."/$module_name/meta/uninstall_db/$database/$db_type.sql";
					if (file_exists($sql_file)) {
						$db->{$module_data['db'][$database]}()->q(
							explode(';', file_get_contents($sql_file))
						);
					}
				}
				unset($db_json, $database, $db_type, $sql_file);
				time_limit_pause(false);
			}
			$permissions_ids		= array_merge(
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
			$module_data			= ['active' => -1];
			$a->save();
			clean_pcache();
			unset($Cache->functionality);
			clean_classes_cache();
		break;
		case 'update':
			/**
			 * Temporary disable module
			 */
			$active					= $module_data['active'];
			if ($active) {
				$module_data['active']	= 0;
				$Config->save();
				Trigger::instance()->run(
					'admin/System/components/modules/disable',
					[
						'name'	=> $module_name
					]
				);
			}
			$module_dir				= MODULES."/$module_name";
			/**
			 * Backing up some necessary information about current version
			 */
			copy("$module_dir/fs.json",		"$module_dir/fs_old.json");
			copy("$module_dir/meta.json",	"$module_dir/meta_old.json");
			/**
			 * Extracting new versions of files
			 */
			$tmp_file	= TEMP.'/'.$User->get_session().'_module_update.phar';
			$tmp_dir	= "phar://$tmp_file";
			$fs			= file_get_json("$tmp_dir/fs.json");
			$extract	= array_product(
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
				$file	= "$module_dir/$file";
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
				$old_version	= file_get_json("$module_dir/meta_old.json")['version'];
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
									$db_type	= $Core->db_type;
								} else {
									$db_type	= $Config->db[$module_data['db'][$database]]['type'];
								}
								$sql_file	= "$module_dir/meta/update_db/$database/$version/$db_type.sql";
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
				$module_data['active']	= 1;
				$Config->save();
				clean_pcache();
				Trigger::instance()->run(
					'admin/System/components/modules/enable',
					[
						'name'	=> $module_name
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
			$site_mode				= $Config->core['site_mode'];
			if ($site_mode) {
				$Config->core['site_mode']	= 0;
				$Config->save();
			}
			$module_dir				= MODULES.'/System';
			/**
			 * Backing up some necessary information about current version
			 */
			copy(DIR.'/core/fs.json',		DIR.'/core/fs_old.json');
			copy("$module_dir/meta.json",	"$module_dir/meta_old.json");
			/**
			 * Extracting new versions of files
			 */
			$tmp_file	= TEMP.'/'.$User->get_session().'_update_system.phar';
			$tmp_dir	= "phar://$tmp_file";
			$fs			= file_get_json("$tmp_dir/fs.json")['core/fs.json'];
			$fs			= file_get_json("$tmp_dir/fs/$fs");
			$extract	= array_product(
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
				$file	= DIR."/$file";
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
				$old_version	= file_get_json("$module_dir/meta_old.json")['version'];
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
									$db_type	= $Core->db_type;
								} else {
									$db_type	= $Config->db[$module_data['db'][$database]]['type'];
								}
								$sql_file	= "$module_dir/meta/update_db/$database/$version/$db_type.sql";
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
				$Config->core['site_mode']	= 1;
			}
			$a->save();
			clean_pcache();
			clean_classes_cache();
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
			if (Trigger::instance()->run(
				'admin/System/components/modules/default_module/process',
				[
					'name' => $module_name
				]
			)) {
				$Config->core['default_module'] = $module_name;
				$a->save();
			}
		break;
		case 'db':
			if (Trigger::instance()->run(
				'admin/System/components/modules/db/process',
				[
					'name' => $module_name
				]
			)) {
				if (isset($_POST['db']) && is_array($_POST['db']) && count($Config->db) > 1) {
					$module_data['db'] = xap($_POST['db']);
					$a->save();
				}
			}
		break;
		case 'storage':
			if (Trigger::instance()->run(
				'admin/System/components/modules/storage/process',
				[
					'name' => $module_name
				]
			)) {
				if(isset($_POST['storage']) && is_array($_POST['storage']) && count($Config->storage) > 1) {
					$module_data['storage'] = xap($_POST['storage']);
					$a->save();
				}
			}
		break;
		case 'enable':
			$module_data['active'] = 1;
			$a->save();
			clean_pcache();
			Trigger::instance()->run(
				'admin/System/components/modules/enable',
				[
					'name'	=> $module_name
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
			Trigger::instance()->run(
				'admin/System/components/modules/disable',
				[
					'name'	=> $module_name
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
			$ok			= true;
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
