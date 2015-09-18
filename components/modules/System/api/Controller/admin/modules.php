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
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Page,
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
	 * Disable module
	 *
	 * Provides next events:
	 *  admin/System/components/modules/disable
	 *  ['name'    => module_name]
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
		$Cache   = Cache::instance();
		$Config  = Config::instance();
		$modules = &$Config->components['modules'];
		if (
			!isset($modules[$module]) ||
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
}
