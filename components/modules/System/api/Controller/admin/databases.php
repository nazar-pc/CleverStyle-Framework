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
	cs\Config,
	cs\Core,
	cs\ExitException,
	cs\Language;

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
		return array_values($databases);
	}
	/**
	 * Update database or database mirror settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_patch ($Request) {
		$data = $Request->data('host', 'type', 'prefix', 'name', 'user', 'password', 'charset');
		if (!$data || !in_array($data['type'], static::admin_databases_get_engines())) {
			throw new ExitException(400);
		}
		$Config         = Config::instance();
		$database_index = $Request->route_ids(0);
		$databases      = &$Config->db;
		if (!isset($databases[$database_index])) {
			throw new ExitException(404);
		}
		$database     = &$databases[$database_index];
		$mirror_index = $Request->route_ids(1);
		// Maybe, we are changing database mirror
		if ($mirror_index !== null) {
			if (!isset($database['mirrors'][$mirror_index])) {
				throw new ExitException(404);
			}
			$database = &$database['mirrors'][$mirror_index];
		} elseif ($database_index == 0) {
			throw new ExitException(400);
		}
		$database = $data + $database;
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
		$data = $Request->data('mirror', 'host', 'type', 'prefix', 'name', 'user', 'password', 'charset');
		if (!$data || !in_array($data['type'], static::admin_databases_get_engines())) {
			throw new ExitException(400);
		}
		$Config         = Config::instance();
		$database_index = $Request->route_ids(0);
		$databases      = &$Config->db;
		// Maybe, we are adding database mirror
		if ($database_index !== null) {
			if (!isset($databases[$Request->route_ids[0]])) {
				throw new ExitException(404);
			}
			$databases = &$databases[$Request->route_ids[0]]['mirrors'];
		}
		$databases[] = $data;
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
		return static::admin_databases_get_engines();
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
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_test ($Request) {
		$data    = $Request->data('type', 'name', 'user', 'password', 'host', 'charset');
		$engines = static::admin_databases_get_engines();
		if (!$data || !in_array($data['type'], $engines, true)) {
			throw new ExitException(400);
		}
		$engine_class = "\\cs\\DB\\$data[type]";
		/**
		 * @var \cs\DB\_Abstract $connection
		 */
		$connection = new $engine_class($data['name'], $data['user'], $data['password'], $data['host'], $data['charset']);
		if (!$connection->connected()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get database settings
	 */
	static function admin_databases_get_settings () {
		$Config = Config::instance();
		return [
			'db_balance'        => $Config->core['db_balance'],
			'db_mirror_mode'    => $Config->core['db_mirror_mode'],
			'applied'           => $Config->cancel_available()
		];
	}
	/**
	 * Apply database settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_apply_settings ($Request) {
		static::admin_databases_settings_common($Request);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_databases_settings_common ($Request) {
		$data = $Request->data('db_balance', 'db_mirror_mode');
		if (!$data) {
			throw new ExitException(400);
		}
		$Config                         = Config::instance();
		$Config->core['db_balance']     = (int)(bool)$data['db_balance'];
		$Config->core['db_mirror_mode'] = (int)(bool)$data['db_mirror_mode'];
	}
	/**
	 * Save database settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_databases_save_settings ($Request) {
		static::admin_databases_settings_common($Request);
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
		Config::instance()->cancel();
	}
}
