<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Cache as System_cache,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Permission,
	cs\Session,
	cs\modules\System\Packages_dependencies,
	cs\modules\System\Packages_manipulation;

trait modules {
	/**
	 * @param \cs\Request $Request
	 *
	 * @return mixed
	 *
	 * @throws ExitException
	 */
	static function admin_modules_get ($Request) {
		if ($Request->route_path(3)) {
			$route_path = $Request->route_path;
			switch ($route_path[3]) {
				/**
				 * Get dependent packages for module
				 */
				case 'dependent_packages':
					return static::get_dependent_packages_for_module($route_path[2]);
				/**
				 * Get dependencies for module (packages, databases, storages)
				 */
				case 'dependencies':
					return static::get_dependencies_for_module($route_path[2]);
				/**
				 * Get dependencies for module during update
				 */
				case 'update_dependencies':
					return static::get_update_dependencies_for_module($route_path[2]);
				/**
				 * Get mapping of named module's databases to indexes of system databases
				 */
				case 'db':
					return static::get_module_databases($route_path[2]);
				/**
				 * Get mapping of named module's storages to indexes of system storages
				 */
				case 'storage':
					return static::get_module_storages($route_path[2]);
				default:
					throw new ExitException(400);
			}
		} elseif ($Request->route_path(2) == 'default') {
			/**
			 * Get current default module
			 */
			return static::get_default_module();
		} else {
			/**
			 * Get array of modules in extended form
			 */
			return static::get_modules_list();
		}
	}
	/**
	 * @param string $module
	 *
	 * @return string[][]
	 *
	 * @throws ExitException
	 */
	protected static function get_dependent_packages_for_module ($module) {
		if (!Config::instance()->module($module)) {
			throw new ExitException(404);
		}
		$meta_file = MODULES."/$module/meta.json";
		return file_exists($meta_file) ? Packages_dependencies::get_dependent_packages(file_get_json($meta_file)) : [];
	}
	/**
	 * @param string $module
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function get_dependencies_for_module ($module) {
		if (!Config::instance()->module($module)) {
			throw new ExitException(404);
		}
		$meta_file = MODULES."/$module/meta.json";
		return file_exists($meta_file) ? Packages_dependencies::get_dependencies(file_get_json($meta_file)) : [];
	}
	/**
	 * @param string $module
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function get_update_dependencies_for_module ($module) {
		if (!Config::instance()->module($module)) {
			throw new ExitException(404);
		}
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		$tmp_dir      = "phar://$tmp_location";
		if (
			!file_exists(MODULES."/$module/meta.json") ||
			!file_exists("$tmp_dir/meta.json")
		) {
			throw new ExitException(400);
		}
		$new_meta = file_get_json("$tmp_dir/meta.json");
		if (!static::is_same_module($new_meta, $module)) {
			throw new ExitException(Language::prefix('system_admin_modules_')->this_is_not_module_installer_file, 400);
		}
		return Packages_dependencies::get_dependencies($new_meta, true);
	}
	/**
	 * @param array  $meta
	 * @param string $module
	 *
	 * @return bool
	 */
	protected static function is_same_module ($meta, $module) {
		return $meta['category'] == 'modules' && $meta['package'] == $module;
	}
	/**
	 * @param string $module
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function get_module_databases ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module]['db'])) {
			throw new ExitException(404);
		}
		return $Config->components['modules'][$module]['db'];
	}
	/**
	 * @param string $module
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function get_module_storages ($module) {
		$Config = Config::instance();
		if (!isset($Config->components['modules'][$module]['storage'])) {
			throw new ExitException(404);
		}
		return $Config->components['modules'][$module]['storage'];
	}
	protected static function get_modules_list () {
		$Config       = Config::instance();
		$modules_list = [];
		foreach ($Config->components['modules'] as $module_name => &$module_data) {
			$module = [
				'active'            => $module_data['active'],
				'name'              => $module_name,
				'has_user_section'  => file_exists_with_extension(MODULES."/$module_name/index", ['php', 'html', 'json']),
				'has_admin_section' => file_exists_with_extension(MODULES."/$module_name/admin/index", ['php', 'json'])
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
		return $modules_list;
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
	/**
	 * @return string
	 */
	protected static function get_default_module () {
		return Config::instance()->core['default_module'];
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_put ($Request) {
		if ($Request->route_path(3)) {
			$module = $Request->route_path[2];
			switch ($Request->route_path[3]) {
				/**
				 * Set mapping of named module's databases to indexes of system databases
				 */
				case 'db':
					static::set_module_databases($Request, $module);
					break;
				/**
				 * Set mapping of named module's storages to indexes of system storages
				 */
				case 'storage':
					static::set_module_storages($Request, $module);
					break;
				default:
					throw new ExitException(400);
			}
		} elseif ($Request->route_path(2) == 'default') {
			/**
			 * Set current default module
			 */
			static::set_default_module($Request->data('module'));
		} else {
			throw new ExitException(400);
		}
	}
	/**
	 * @param \cs\Request $Request
	 * @param string      $module
	 *
	 * @throws ExitException
	 */
	protected static function set_module_databases ($Request, $module) {
		$Config          = Config::instance();
		$database_config = $Request->data('db');
		if (!$database_config || !isset($Config->components['modules'][$module]['db'])) {
			throw new ExitException(404);
		}
		$Config->components['modules'][$module]['db'] = _int($database_config);
		static::admin_modules_save();
	}
	/**
	 * @param \cs\Request $Request
	 * @param string      $module
	 *
	 * @throws ExitException
	 */
	protected static function set_module_storages ($Request, $module) {
		$Config         = Config::instance();
		$storage_config = $Request->data('storage');
		if (!$storage_config || !isset($Config->components['modules'][$module]['storage'])) {
			throw new ExitException(404);
		}
		$Config->components['modules'][$module]['storage'] = _int($storage_config);
		static::admin_modules_save();
	}
	/**
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function set_default_module ($module) {
		$Config      = Config::instance();
		$module_data = $Config->module($module);
		if (!$module_data) {
			throw new ExitException(404);
		}
		if (
			$module == $Config->core['default_module'] ||
			!$module_data->enabled() ||
			!file_exists_with_extension(MODULES."/$module/index", ['php', 'html', 'json'])
		) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/default', ['name' => $module])) {
			throw new ExitException(500);
		}
		$Config->core['default_module'] = $module;
		static::admin_modules_save();
	}
	/**
	 * Enable module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/enable/before
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/enable/after
	 *  ['name' => module_name]
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_enable ($Request) {
		$Config = Config::instance();
		$module = $Request->route_path(2);
		if (!$Config->module($module)->disabled()) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/enable/before', ['name' => $module])) {
			throw new ExitException(500);
		}
		$Config->components['modules'][$module]['active'] = Config\Module_Properties::ENABLED;
		static::admin_modules_save();
		Event::instance()->fire('admin/System/components/modules/enable/after', ['name' => $module]);
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
	 *  admin/System/components/modules/disable/before
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/disable/after
	 *  ['name' => module_name]
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_disable ($Request) {
		$Config = Config::instance();
		$module = $Request->route_path(2);
		if (
			$module == Config::SYSTEM_MODULE ||
			$Config->core['default_module'] == $module ||
			!$Config->module($module)->enabled()
		) {
			throw new ExitException(400);
		}
		static::admin_modules_disable_internal($Config, $module);
	}
	/**
	 * @param Config $Config
	 * @param string $module
	 *
	 * @throws ExitException
	 */
	protected static function admin_modules_disable_internal ($Config, $module) {
		if (!Event::instance()->fire('admin/System/components/modules/disable/before', ['name' => $module])) {
			throw new ExitException(500);
		}
		$Config->components['modules'][$module]['active'] = Config\Module_Properties::DISABLED;
		static::admin_modules_save();
		Event::instance()->fire('admin/System/components/modules/disable/after', ['name' => $module]);
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
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_install ($Request) {
		$Config = Config::instance();
		$module = $Request->route_path(2);
		if (!$Config->module($module)->uninstalled()) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/install/before', ['name' => $module])) {
			throw new ExitException(500);
		}
		$module_data     = &$Config->components['modules'][$module];
		$database_config = $Request->data('db');
		if ($database_config) {
			$module_data['db'] = _int($database_config);
			Packages_manipulation::execute_sql_from_directory(MODULES."/$module/meta/install_db", $module_data['db']);
		}
		$storage_config = $Request->data('storage');
		if ($storage_config) {
			$module_data['storage'] = _int($storage_config);
		}
		$module_data['active'] = Config\Module_Properties::DISABLED;
		static::admin_modules_save();
		Event::instance()->fire('admin/System/components/modules/install/after', ['name' => $module]);
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
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_uninstall ($Request) {
		$Config  = Config::instance();
		$module  = $Request->route_path(2);
		$modules = &$Config->components['modules'];
		/**
		 * Do not allow to uninstall enabled module, it should be explicitly disabled first
		 */
		if (!$Config->module($module)->disabled()) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/uninstall/before', ['name' => $module])) {
			throw new ExitException(500);
		}
		$module_data = &$modules[$module];
		if (isset($module_data['db'])) {
			Packages_manipulation::execute_sql_from_directory(MODULES."/$module/meta/uninstall_db", $module_data['db']);
		}
		static::delete_permissions_for_module($module);
		$modules[$module] = ['active' => Config\Module_Properties::UNINSTALLED];
		static::admin_modules_save();
		Event::instance()->fire('admin/System/components/modules/uninstall/after', ['name' => $module]);
		static::admin_modules_cleanup();
	}
	/**
	 * @param string $module
	 */
	protected static function delete_permissions_for_module ($module) {
		$Permission      = Permission::instance();
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
	}
	/**
	 * Extract uploaded module
	 *
	 * @throws ExitException
	 */
	static function admin_modules_extract () {
		$Config       = Config::instance();
		$L            = Language::prefix('system_admin_modules_');
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
		if (!Packages_manipulation::install_extract(MODULES."/$new_meta[package]", $tmp_location)) {
			throw new ExitException($L->module_files_unpacking_error, 500);
		}
		$Config->components['modules'][$new_meta['package']] = ['active' => Config\Module_Properties::UNINSTALLED];
		ksort($Config->components['modules'], SORT_STRING | SORT_FLAG_CASE);
		static::admin_modules_save();
		static::admin_modules_cleanup();
	}
	/**
	 * Update module (or system if module name is System)
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_update ($Request) {
		$module = $Request->route_path(2);
		if (!Config::instance()->module($module)) {
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
		if ($module == Config::SYSTEM_MODULE) {
			static::update_system($module, $existing_meta, $new_meta, $tmp_location);
		} else {
			static::update_module($module, $existing_meta, $new_meta, $tmp_location, $Request);
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
	 * @param string      $module
	 * @param array       $existing_meta
	 * @param array       $new_meta
	 * @param string      $tmp_location
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function update_module ($module, $existing_meta, $new_meta, $tmp_location, $Request) {
		$Config     = Config::instance();
		$L          = Language::prefix('system_admin_modules_');
		$module_dir = MODULES."/$module";
		$enabled    = $Config->module($module)->enabled();
		// If module is currently enabled - disable it temporary
		if ($enabled) {
			static::admin_modules_disable_internal($Config, $module);
		}
		if (!static::is_same_module($new_meta, $module)) {
			throw new ExitException($L->this_is_not_module_installer_file, 400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/update/before', ['name' => $module])) {
			throw new ExitException(500);
		}
		if (!is_writable($module_dir)) {
			throw new ExitException($L->cant_unpack_module_no_write_permissions, 500);
		}
		if (!Packages_manipulation::update_extract($module_dir, $tmp_location)) {
			throw new ExitException($L->module_files_unpacking_error, 500);
		}
		$module_data = $Config->components['modules'][$module];
		// Run PHP update scripts and SQL queries if any
		Packages_manipulation::update_php_sql($module_dir, $existing_meta['version'], isset($module_data['db']) ? $module_data['db'] : null);
		Event::instance()->fire('admin/System/components/modules/update/after', ['name' => $module]);
		// If module was enabled before update - enable it back
		if ($enabled) {
			static::admin_modules_enable($Request);
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
	 *
	 * @throws ExitException
	 */
	protected static function update_system ($module, $existing_meta, $new_meta, $tmp_location) {
		$Config     = Config::instance();
		$L          = Language::prefix('system_admin_modules_');
		$module_dir = MODULES."/$module";
		/**
		 * Temporary close site
		 */
		$site_mode = $Config->core['site_mode'];
		if ($site_mode) {
			$Config->core['site_mode'] = 0;
			static::admin_modules_save();
		}
		if (!static::is_same_module($new_meta, Config::SYSTEM_MODULE)) {
			throw new ExitException($L->this_is_not_system_installer_file, 400);
		}
		if (!Event::instance()->fire('admin/System/components/modules/update_system/before')) {
			throw new ExitException(500);
		}
		if (!Packages_manipulation::update_extract(DIR, $tmp_location, DIR.'/core', $module_dir)) {
			throw new ExitException($L->system_files_unpacking_error, 500);
		}
		$module_data = $Config->components['modules'][$module];
		// Run PHP update scripts and SQL queries if any
		Packages_manipulation::update_php_sql($module_dir, $existing_meta['version'], isset($module_data['db']) ? $module_data['db'] : null);
		Event::instance()->fire('admin/System/components/modules/update_system/after');
		/**
		 * Restore previous site mode
		 */
		if ($site_mode) {
			$Config->core['site_mode'] = 1;
			static::admin_modules_save();
		}
		static::admin_modules_cleanup();
	}
	/**
	 * Delete module completely
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_modules_delete ($Request) {
		$Config = Config::instance();
		$module = $Request->route_path(2);
		if (!$Config->module($module)->uninstalled()) {
			throw new ExitException(400);
		}
		if (!rmdir_recursive(MODULES."/$module")) {
			throw new ExitException(500);
		}
		unset($Config->components['modules'][$module]);
		static::admin_modules_save();
	}
	/**
	 * Update information about present modules
	 *
	 * @throws ExitException
	 */
	static function admin_modules_update_list () {
		$Config = Config::instance();
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
			foreach ($known_modules as $module) {
				if (!isset($modules_list[$module])) {
					static::delete_permissions_for_module($module);
				}
			}
			unset($module);
		}
		unset($modules_in_fs, $known_modules);
		$modules = array_merge($modules_list, array_intersect_key($modules, $modules_list));
		ksort($modules, SORT_STRING | SORT_FLAG_CASE);
		static::admin_modules_save();
		static::admin_modules_cleanup();
	}
	/**
	 * Save changes
	 *
	 * @throws ExitException
	 */
	protected static function admin_modules_save () {
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
}
