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
		if (isset($route_path[2]) && $route_path[2] == 'default') {
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
		$Cache   = System_cache::instance();
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
		clean_pcache();
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
		$Cache   = System_cache::instance();
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
		clean_pcache();
		unset(
			$Cache->functionality,
			$Cache->languages
		);
		clean_classes_cache();
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
		$Cache      = System_cache::instance();
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
			$Permission->get(null, "$module/admin"),
			$Permission->get(null, "$module/api")
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
		clean_pcache();
		unset(
			$Cache->functionality,
			$Cache->languages
		);
		clean_classes_cache();
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
	 * Update module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/update/before
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/update/after
	 *  ['name' => module_name]
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
		$Config  = Config::instance();
		$L       = Language::instance();
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
		$module_data   = $Config->components['modules'][$module];
		$active        = $module_data['active'] == 1;
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
}
