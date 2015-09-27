<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Cache as System_cache,
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Permission,
	cs\Session,
	cs\modules\System\Packages_manipulation;
trait modules {
	/**
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_get ($route_ids, $route_path) {
		if (isset($route_path[3])) {
			switch ($route_path[3]) {
				/**
				 * Get dependent packages for module
				 */
				case 'dependent_packages':
					static::get_dependent_packages_for_module($route_path[2]);
					break;
				/**
				 * Get dependencies for module (packages, databases, storages)
				 */
				case 'dependencies':
					static::get_dependencies_for_module($route_path[2]);
					break;
				/**
				 * Get dependencies for module during update
				 */
				case 'update_dependencies':
					static::get_update_dependencies_for_module($route_path[2]);
					break;
				/**
				 * Get mapping of named module's databases to indexes of system databases
				 */
				case 'db':
					static::get_module_databases($route_path[2]);
					break;
				/**
				 * Get mapping of named module's storages to indexes of system storages
				 */
				case 'storage':
					static::get_module_storages($route_path[2]);
					break;
				default:
					throw new ExitException(400);
			}
		} elseif (isset($route_path[2]) && $route_path[2] == 'default') {
			/**
			 * Get current default module
			 */
			static::get_default_module();
		} else {
			/**
			 * Get array of modules in extended form
			 */
			static::get_modules_list();
		}
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function get_dependent_packages_for_module ($module) {
		if (!isset(Config::instance()->components['modules'][$module])) {
			throw new ExitException(404);
		}
		$meta_file = MODULES."/$module/meta.json";
		Page::instance()->json(
			file_exists($meta_file) ? Packages_manipulation::get_dependent_packages(file_get_json($meta_file)) : []
		);
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function get_dependencies_for_module ($module) {
		if (!isset(Config::instance()->components['modules'][$module])) {
			throw new ExitException(404);
		}
		$meta_file = MODULES."/$module/meta.json";
		Page::instance()->json(
			file_exists($meta_file) ? Packages_manipulation::get_dependencies(file_get_json($meta_file)) : []
		);
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function get_update_dependencies_for_module ($module) {
		if (!isset(Config::instance()->components['modules'][$module])) {
			throw new ExitException(404);
		}
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		if (!file_exists($tmp_location) || !file_exists(MODULES."/$module/meta.json")) {
			throw new ExitException(400);
		}
		$tmp_dir = "phar://$tmp_location";
		if (!file_exists("$tmp_dir/meta.json")) {
			throw new ExitException(400);
		}
		$existing_meta = file_get_json(MODULES."/$module/meta.json");
		$new_meta      = file_get_json("$tmp_dir/meta.json");
		if (
			$existing_meta['package'] !== $new_meta['package'] ||
			$existing_meta['category'] !== $new_meta['category']
		) {
			throw new ExitException(Language::instance()->this_is_not_module_installer_file, 400);
		}
		Page::instance()->json(
			Packages_manipulation::get_dependencies(file_get_json($new_meta))
		);
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function get_module_databases ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module]['db'])) {
			throw new ExitException(404);
		}
		Page::instance()->json(
			$Config->components['modules'][$module]['db']
		);
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function get_module_storages ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module]['storage'])) {
			throw new ExitException(404);
		}
		Page::instance()->json(
			$Config->components['modules'][$module]['storage']
		);
	}
	protected static function get_modules_list () {
		$Config       = Config::instance();
		$modules_list = [];
		foreach ($Config->components['modules'] as $module_name => &$module_data) {
			$module = [
				'active'                => $module_data['active'],
				'name'                  => $module_name,
				'is_default'            => $module_name == $Config->core['default_module'],
				'can_be_set_as_default' =>
					$module_data['active'] == 1 &&
					$module_name != $Config->core['default_module'] &&
					file_exists_with_extension(MODULES."/$module_name/index", ['php', 'html', 'json']),
				'db_settings'           => !$Config->core['simple_admin_mode'] && @$module_data['db'] && count($Config->db) > 1,
				'storage_settings'      => !$Config->core['simple_admin_mode'] && @$module_data['storage'] && count($Config->storage) > 1,
				'administration'        =>
					$module_data['active'] != -1 &&
					file_exists_with_extension(MODULES."/$module_name/admin/index", ['php', 'json'])
			];
			/**
			 * Check if API available
			 */
			static::check_module_feature_availability($module, 'readme', 'api');
			/**
			 * Check if readme available
			 */
			static::check_module_feature_availability($module, 'readme');
			/**
			 * Check if license available
			 */
			static::check_module_feature_availability($module, 'license');
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$module['meta'] = file_get_json(MODULES."/$module_name/meta.json");
			}
			$modules_list[] = $module;
		}
		unset($module_name, $module_data, $module);
		Page::instance()->json($modules_list);
	}
	/**
	 * @param array  $module
	 * @param string $feature
	 * @param string $dir
	 */
	protected static function check_module_feature_availability (&$module, $feature, $dir = '') {
		/**
		 * Check if feature available
		 */
		$file = file_exists_with_extension(MODULES."/$module[name]/$dir/$feature", ['txt', 'html']);
		if ($file) {
			$module[$dir ?: $feature] = [
				'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
				'content' => file_get_contents($file)
			];
		} elseif ($dir && is_dir(MODULES."/$module[name]/$dir")) {
			$module[$dir ?: $feature] = [];
		}
	}
	protected static function get_default_module () {
		Page::instance()->json(
			Config::instance()->core['default_module']
		);
	}
	/**
	 * Set current default module
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_put ($route_ids, $route_path) {
		if (isset($route_path[3])) {
			switch ($route_path[3]) {
				/**
				 * Set mapping of named module's databases to indexes of system databases
				 */
				case 'db':
					static::set_module_databases($route_path[2]);
					break;
				/**
				 * Set mapping of named module's storages to indexes of system storages
				 */
				case 'storage':
					static::set_module_storages($route_path[2]);
					break;
				default:
					throw new ExitException(400);
			}
		} elseif (isset($route_path[2]) && $route_path[2] == 'default') {
			if (!isset($_POST['module'])) {
				throw new ExitException(400);
			}
			static::set_default_module($_POST['module']);
		} else {
			throw new ExitException(400);
		}
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function set_module_databases ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module]['db'], $_POST['db'])) {
			throw new ExitException(404);
		}
		$Config->components['modules'][$module]['db'] = _int($_POST['db']);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function set_module_storages ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module]['storage'], $_POST['storage'])) {
			throw new ExitException(404);
		}
		$Config->components['modules'][$module]['storage'] = _int($_POST['storage']);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function set_default_module ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module])) {
			throw new ExitException(404);
		}
		if (
			$module == $Config->core['default_module'] ||
			$Config->components['modules'][$module]['active'] != 1 ||
			!(
				file_exists(MODULES."/$module/index.php") ||
				file_exists(MODULES."/$module/index.html") ||
				file_exists(MODULES."/$module/index.json")
			)
		) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/modules/default',
			[
				'name' => $module
			]
		)
		) {
			throw new ExitException(500);
		}
		$Config->core['default_module'] = $module;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Enable module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/enable
	 *  ['name' => module_name]
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_enable ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$module  = $route_path[2];
		$Config  = Config::instance();
		$modules = &$Config->components['modules'];
		if (
			!isset($modules[$module]) ||
			$modules[$module]['active'] != 0
		) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/modules/enable',
			[
				'name' => $module
			]
		)
		) {
			throw new ExitException(500);
		}
		$modules[$module]['active'] = 1;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		static::admin_modules_cleanup();
	}
	protected static function admin_modules_cleanup () {
		clean_pcache();
		$Cache = System_cache::instance();
		unset(
			$Cache->functionality,
			$Cache->languages
		);
		clean_classes_cache();
	}
	/**
	 * Disable module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/disable
	 *  ['name' => module_name]
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_disable ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$module  = $route_path[2];
		$Config  = Config::instance();
		$modules = &$Config->components['modules'];
		if (
			$module == 'System' ||
			!isset($modules[$module]) ||
			$Config->core['default_module'] === $module ||
			$modules[$module]['active'] != 1
		) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/modules/disable',
			[
				'name' => $module
			]
		)
		) {
			throw new ExitException(500);
		}
		$modules[$module]['active'] = 0;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		static::admin_modules_cleanup();
	}
	/**
	 * Install module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/install/before
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/install/after
	 *  ['name' => module_name]
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_install ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$module  = $route_path[2];
		$Config  = Config::instance();
		$Core    = Core::instance();
		$db      = DB::instance();
		$modules = &$Config->components['modules'];
		if (
			!isset($modules[$module]) ||
			$modules[$module]['active'] != -1
		) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/modules/install/before',
			[
				'name' => $module
			]
		)
		) {
			throw new ExitException(500);
		}
		$module_data = &$modules[$module];
		if (isset($_POST['db'])) {
			$module_data['db'] = $_POST['db'];
			time_limit_pause();
			foreach ($module_data['db'] as $db_name => $index) {
				$db_type  = $index == 0 ? $Core->db_type : $Config->db[$index]['type'];
				$sql_file = MODULES."/$module/meta/install_db/$db_name/$db_type.sql";
				if (file_exists($sql_file)) {
					$db->$index()->q(
						explode(';', file_get_contents($sql_file))
					);
				}
			}
			unset($db_name, $index, $db_type, $sql_file);
			time_limit_pause(false);
		}
		if (isset($_POST['storage'])) {
			$module_data['storage'] = $_POST['storage'];
		}
		$module_data['active'] = 0;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		Event::instance()->fire(
			'admin/System/components/modules/install/after',
			[
				'name' => $module
			]
		);
		static::admin_modules_cleanup();
	}
	/**
	 * Uninstall module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/uninstall/before
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/uninstall/after
	 *  ['name' => module_name]
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_uninstall ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$module     = $route_path[2];
		$Config     = Config::instance();
		$Core       = Core::instance();
		$db         = DB::instance();
		$Permission = Permission::instance();
		$modules    = &$Config->components['modules'];
		/**
		 * Do not allow to uninstall enabled module, it should be explicitly disabled first
		 */
		if (
			!isset($modules[$module]) ||
			$modules[$module]['active'] != 0
		) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/modules/uninstall/before',
			[
				'name' => $module
			]
		)
		) {
			throw new ExitException(500);
		}
		if (isset($module_data['db'])) {
			time_limit_pause();
			foreach ($module_data['db'] as $db_name => $index) {
				$db_type  = $index == 0 ? $Core->db_type : $Config->db[$index]['type'];
				$sql_file = MODULES."/$module/meta/uninstall_db/$db_name/$db_type.sql";
				if (file_exists($sql_file)) {
					$db->$index()->q(
						explode(';', file_get_contents($sql_file))
					);
				}
			}
			unset($db_name, $db_type, $sql_file);
			time_limit_pause(false);
		}
		$permissions_ids = array_merge(
			$Permission->get(null, $module),
			$Permission->get(null, "admin/$module"),
			$Permission->get(null, "api/$module")
		);
		if (!empty($permissions_ids)) {
			$Permission->del(
				array_column($permissions_ids, 'id')
			);
		}
		$modules[$module] = ['active' => -1];
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		Event::instance()->fire(
			'admin/System/components/modules/uninstall/after',
			[
				'name' => $module
			]
		);
		static::admin_modules_cleanup();
	}
	/**
	 * Extract uploaded module
	 *
	 * @throws ExitException
	 */
	static function admin_modules_extract () {
		$Config       = Config::instance();
		$L            = Language::instance();
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		$tmp_dir      = "phar://$tmp_location";
		if (
			!file_exists($tmp_location) ||
			!file_exists("$tmp_dir/meta.json")
		) {
			throw new ExitException(400);
		}
		$new_meta = file_get_json("$tmp_dir/meta.json");
		if ($new_meta['category'] !== 'modules') {
			throw new ExitException($L->this_is_not_module_installer_file, 400);
		}
		$module_dir = MODULES."/$new_meta[package]";
		if (
			!mkdir($module_dir, 0770) ||
			!Packages_manipulation::install_extract($module_dir, $tmp_location)
		) {
			throw new ExitException($L->module_files_unpacking_error, 500);
		}
		$Config->components['modules'][$new_meta['package']] = ['active' => -1];
		ksort($Config->components['modules'], SORT_STRING | SORT_FLAG_CASE);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Update module (or system if module name is System)
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_update ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$module  = $route_path[2];
		$modules = get_files_list(MODULES, false, 'd');
		if (!in_array($module, $modules, true)) {
			throw new ExitException(404);
		}
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		$tmp_dir      = "phar://$tmp_location";
		$module_dir   = MODULES."/$module";
		if (
			!file_exists($tmp_location) ||
			!file_exists("$module_dir/meta.json") ||
			!file_exists("$tmp_dir/meta.json")
		) {
			throw new ExitException(400);
		}
		$existing_meta = file_get_json("$module_dir/meta.json");
		$new_meta      = file_get_json("$tmp_dir/meta.json");
		if ($module === 'System') {
			static::update_module($module, $existing_meta, $new_meta, $tmp_location, $route_ids, $route_path);
		} else {
			static::update_system($module, $existing_meta, $new_meta, $tmp_location, $tmp_dir);
		}
		static::admin_modules_cleanup();
	}
	/**
	 * Provides next events:
	 *  admin/System/components/modules/update/before
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/update/after
	 *  ['name' => module_name]
	 *
	 * @param string   $module
	 * @param array    $existing_meta
	 * @param array    $new_meta
	 * @param string   $tmp_location
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	protected static function update_module ($module, $existing_meta, $new_meta, $tmp_location, $route_ids, $route_path) {
		$Config      = Config::instance();
		$L           = Language::instance();
		$module_dir  = MODULES."/$module";
		$module_data = $Config->components['modules'][$module];
		$active      = $module_data['active'] == 1;
		// If module is currently enabled - disable it temporary
		if ($active) {
			static::admin_modules_disable($route_ids, $route_path);
		}
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		if (
			$new_meta['package'] !== $module ||
			$new_meta['category'] !== 'modules'
		) {
			throw new ExitException($L->this_is_not_module_installer_file, 400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/modules/update/before',
			[
				'name' => $module
			]
		)
		) {
			throw new ExitException(500);
		}
		if (!is_writable($module_dir)) {
			throw new ExitException($L->cant_unpack_module_no_write_permissions, 500);
		}
		if (!Packages_manipulation::update_extract($module_dir, $tmp_location)) {
			throw new ExitException($L->module_files_unpacking_error, 500);
		}
		// Run PHP update scripts and SQL queries if any
		Packages_manipulation::update_php_sql($module_dir, $existing_meta['version'], isset($module_data['db']) ? $module_data['db'] : null);
		Event::instance()->fire(
			'admin/System/components/modules/update/after',
			[
				'name' => $module
			]
		);
		// If module was enabled before update - enable it back
		if ($active) {
			static::admin_modules_enable($route_ids, $route_path);
		}
	}
	/**
	 * Provides next events:
	 *  admin/System/components/modules/update_system/before
	 *
	 *  admin/System/components/modules/update_system/after
	 *
	 * @param string $module
	 * @param array  $existing_meta
	 * @param array  $new_meta
	 * @param string $tmp_location
	 * @param string $tmp_dir
	 *
	 * @throws ExitException
	 */
	protected static function update_system ($module, $existing_meta, $new_meta, $tmp_location, $tmp_dir) {
		$Config      = Config::instance();
		$L           = Language::instance();
		$module_dir  = MODULES."/$module";
		$module_data = $Config->components['modules'][$module];
		/**
		 * Temporary close site
		 */
		$site_mode = $Config->core['site_mode'];
		if ($site_mode) {
			$Config->core['site_mode'] = 0;
			if (!$Config->save()) {
				throw new ExitException(500);
			}
		}
		if (
			$new_meta['package'] !== 'System' ||
			$new_meta['category'] !== 'modules' ||
			!file_exists("$tmp_dir/modules.json") ||
			!file_exists("$tmp_dir/plugins.json") ||
			!file_exists("$tmp_dir/themes.json")
		) {
			throw new ExitException($L->this_is_not_system_installer_file, 400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/update_system/before')) {
			throw new ExitException(500);
		}
		if (!Packages_manipulation::update_extract(DIR, $tmp_location, DIR.'/core', $module_dir)) {
			throw new ExitException($L->system_files_unpacking_error, 500);
		}
		// Run PHP update scripts and SQL queries if any
		Packages_manipulation::update_php_sql($module_dir, $existing_meta['version'], $module_data['db']);
		Event::instance()->fire('admin/System/components/modules/update_system/after');
		/**
		 * Restore previous site mode
		 */
		if ($site_mode) {
			$Config->core['site_mode'] = 1;
			if (!$Config->save()) {
				throw new ExitException(500);
			}
		}
	}
	/**
	 * Delete module completely
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_modules_delete ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$module_name = $route_path[2];
		$Config      = Config::instance();
		if (!isset($Config->components['modules'][$module_name])) {
			throw new ExitException(404);
		}
		if (
			$module_name == 'System' ||
			$Config->components['modules'][$module_name]['active'] != '-1'
		) {
			throw new ExitException(400);
		}
		if (!rmdir_recursive(MODULES."/$module_name")) {
			throw new ExitException(500);
		}
		unset($Config->components['modules'][$module_name]);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Update information about present modules
	 *
	 * @throws ExitException
	 */
	static function admin_modules_update_list () {
		$Config     = Config::instance();
		$Permission = Permission::instance();
		/**
		 * List of currently presented modules in file system
		 */
		$modules_in_fs = get_files_list(MODULES, false, 'd');
		$modules_list  = array_fill_keys(
			$modules_in_fs,
			[
				'active'  => -1,
				'db'      => [],
				'storage' => []
			]
		);
		/**
		 * Already known modules
		 */
		$modules       = &$Config->components['modules'];
		$known_modules = array_keys($modules);
		if ($modules_in_fs != $known_modules) {
			/**
			 * Delete permissions of modules that are mot present anymore
			 */
			$permissions_ids = [];
			foreach ($known_modules as $module) {
				if (!isset($modules_list[$module])) {
					/** @noinspection SlowArrayOperationsInLoopInspection */
					$permissions_ids = array_merge(
						$permissions_ids,
						(array)$Permission->get(null, $module),
						(array)$Permission->get(null, "admin/$module"),
						(array)$Permission->get(null, "api/$module")
					);
				}
			}
			unset($known_modules, $module);
			if ($permissions_ids) {
				$Permission->del(
					array_column($permissions_ids, 'id')
				);
			}
			unset($permissions_ids);
		}
		unset($modules_in_fs, $known_modules);
		$modules = array_merge($modules_list, array_intersect_key($modules, $modules_list));
		ksort($modules, SORT_STRING | SORT_FLAG_CASE);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
}
