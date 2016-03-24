<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\Core,
	cs\ExitException,
	cs\Language,
	cs\Page;

trait databases {
	/**
	 * Get array of databases
	 */
	static function admin_databases_get () {
		$Config       = Config::instance();
		$Core         = Core::instance();
		$databases    = $Config->db;
		$databases[0] = array_merge(
			isset($databases[0]) ? $databases[0] : [],
			[
				'host'    => $Core->db_host,
				'type'    => $Core->db_type,
				'prefix'  => $Core->db_prefix,
				'name'    => $Core->db_name,
				'user'    => '',
				'charset' => $Core->db_charset,
				'mirrors' => []
			]
		);
		foreach ($databases as $i => &$db) {
			$db['index'] = $i;
			foreach ($db['mirrors'] as $j => &$mirror) {
				$mirror['index'] = $j;
			}
			unset($j, $mirror);
			$db['mirrors'] = array_values($db['mirrors']);
		}
		unset($i, $db);
		Page::instance()->json(array_values($databases));
	}
	/**
	 * Update database or database mirror settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_patch ($Request) {
		$route_ids = $Request->route_ids;
		if (
			!isset($route_ids[0], $_POST['host'], $_POST['type'], $_POST['prefix'], $_POST['name'], $_POST['user'], $_POST['password'], $_POST['charset']) ||
			!in_array($_POST['type'], static::admin_databases_get_engines())
		) {
			throw new ExitException(400);
		}
		$Config         = Config::instance();
		$databases      = &$Config->db;
		$database_index = $route_ids[0];
		if (!isset($databases[$database_index])) {
			throw new ExitException(404);
		}
		$database = &$databases[$database_index];
		// Maybe, we are changing database mirror
		if (isset($route_ids[1])) {
			if (!isset($database['mirrors'][$route_ids[1]])) {
				throw new ExitException(404);
			}
			$database = &$database['mirrors'][$route_ids[1]];
		} elseif ($database_index == 0) {
			throw new ExitException(400);
		}
		$database['host']     = $_POST['host'];
		$database['type']     = $_POST['type'];
		$database['prefix']   = $_POST['prefix'];
		$database['name']     = $_POST['name'];
		$database['user']     = $_POST['user'];
		$database['password'] = $_POST['password'];
		$database['charset']  = $_POST['charset'];
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Create database or database mirror
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_post ($Request) {
		$route_ids = $Request->route_ids;
		if (
			!isset($_POST['mirror'], $_POST['host'], $_POST['type'], $_POST['prefix'], $_POST['name'], $_POST['user'], $_POST['password'], $_POST['charset']) ||
			!in_array($_POST['type'], static::admin_databases_get_engines())
		) {
			throw new ExitException(400);
		}
		$Config    = Config::instance();
		$databases = &$Config->db;
		// Maybe, we are adding database mirror
		if (isset($route_ids[0])) {
			if (!isset($databases[$route_ids[0]])) {
				throw new ExitException(404);
			}
			$databases = &$databases[$route_ids[0]]['mirrors'];
		}
		$databases[] = [
			'mirror'   => $_POST['mirror'],
			'host'     => $_POST['host'],
			'type'     => $_POST['type'],
			'prefix'   => $_POST['prefix'],
			'name'     => $_POST['name'],
			'user'     => $_POST['user'],
			'password' => $_POST['password'],
			'charset'  => $_POST['charset']
		];
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete database or database mirror
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_delete ($Request) {
		$route_ids = $Request->route_ids;
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		$Config         = Config::instance();
		$databases      = &$Config->db;
		$database_index = $route_ids[0];
		if (!isset($databases[$database_index])) {
			throw new ExitException(404);
		}
		// Maybe, we are deleting database mirror
		if (isset($route_ids[1])) {
			if (!isset($databases[$database_index]['mirrors'][$route_ids[1]])) {
				throw new ExitException(404);
			}
			unset($databases[$database_index]['mirrors'][$route_ids[1]]);
		} elseif ($database_index == 0) {
			throw new ExitException(400);
		} else {
			static::admin_databases_delete_check_usages($database_index);
			unset($databases[$database_index]);
		}
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	protected static function admin_databases_delete_check_usages ($database_index) {
		$Config  = Config::instance();
		$used_by = [];
		foreach ($Config->components['modules'] as $module => $module_data) {
			if (isset($module_data['db']) && is_array($module_data['db'])) {
				foreach ($module_data['db'] as $index) {
					if ($index == $database_index) {
						$used_by[] = $module;
					}
				}
			}
		}
		if ($used_by) {
			throw new ExitException(
				Language::instance()->system_admin_blocks_db_used_by_modules(implode(', ', $used_by)),
				409
			);
		}
	}
	/**
	 * Get array of available database engines
	 */
	static function admin_databases_engines () {
		Page::instance()->json(
			static::admin_databases_get_engines()
		);
	}
	/**
	 * @return string[]
	 */
	protected static function admin_databases_get_engines () {
		return _mb_substr(get_files_list(ENGINES.'/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4);
	}
	/**
	 * Test database connection
	 *
	 * @throws ExitException
	 */
	static function admin_databases_test () {
		$engines = static::admin_databases_get_engines();
		if (
			!isset($_POST['type'], $_POST['name'], $_POST['user'], $_POST['password'], $_POST['host'], $_POST['charset']) ||
			!in_array($_POST['type'], $engines, true)
		) {
			throw new ExitException(400);
		}
		$engine_class = "\\cs\\DB\\$_POST[type]";
		/**
		 * @var \cs\DB\_Abstract $connection
		 */
		$connection = new $engine_class(
			$_POST['name'],
			$_POST['user'],
			$_POST['password'],
			$_POST['host'],
			$_POST['charset']
		);
		if (!$connection->connected()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get database settings
	 */
	static function admin_databases_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'db_balance'        => $Config->core['db_balance'],
				'db_mirror_mode'    => $Config->core['db_mirror_mode'],
				'show_tooltips'     => $Config->core['show_tooltips'],
				'simple_admin_mode' => $Config->core['simple_admin_mode'],
				'applied'           => $Config->cancel_available()
			]
		);
	}
	/**
	 * Apply database settings
	 *
	 * @throws ExitException
	 */
	static function admin_databases_apply_settings () {
		static::admin_databases_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_databases_settings_common () {
		if (!isset(
			$_POST['db_balance'],
			$_POST['db_mirror_mode']
		)
		) {
			throw new ExitException(400);
		}
		$Config                         = Config::instance();
		$Config->core['db_balance']     = (int)(bool)$_POST['db_balance'];
		$Config->core['db_mirror_mode'] = (int)(bool)$_POST['db_mirror_mode'];
	}
	/**
	 * Save database settings
	 *
	 * @throws ExitException
	 */
	static function admin_databases_save_settings () {
		static::admin_databases_settings_common();
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel database settings
	 *
	 * @throws ExitException
	 */
	static function admin_databases_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
