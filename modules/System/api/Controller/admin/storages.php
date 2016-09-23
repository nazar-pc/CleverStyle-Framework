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

trait storages {
	/**
	 * Get array of storages
	 */
	public static function admin_storages_get () {
		$Config      = Config::instance();
		$Core        = Core::instance();
		$storages    = $Config->storage;
		$storages[0] = [
			'host'   => $Core->storage_host,
			'driver' => $Core->storage_driver,
			'user'   => '',
			'url'    => $Core->storage_url ?: url_by_source(PUBLIC_STORAGE)
		];
		foreach ($storages as $i => &$storage) {
			$storage['index'] = $i;
		}
		return array_values($storages);
	}
	/**
	 * Update storage settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_storages_patch ($Request) {
		$storage_index = $Request->route_ids(0);
		$data          = $Request->data('url', 'host', 'driver', 'user', 'password');
		if (
			!$storage_index ||
			!$data ||
			!strlen($data['host']) ||
			!in_array($data['driver'], static::admin_storages_get_drivers())
		) {
			throw new ExitException(400);
		}
		$Config   = Config::instance();
		$storages = &$Config->storage;
		if (!isset($storages[$storage_index])) {
			throw new ExitException(404);
		}
		$storage = &$storages[$storage_index];
		$storage = $data + $storage;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Create storage
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_storages_post ($Request) {
		$data = $Request->data('url', 'host', 'driver', 'user', 'password');
		if (
			!$data ||
			!strlen($data['host']) ||
			!in_array($data['driver'], static::admin_storages_get_drivers())
		) {
			throw new ExitException(400);
		}
		$Config            = Config::instance();
		$Config->storage[] = $data;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete storage
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_storages_delete ($Request) {
		$storage_index = $Request->route_ids(0);
		if (!$storage_index) {
			throw new ExitException(400);
		}
		$Config   = Config::instance();
		$storages = &$Config->storage;
		if (!isset($storages[$storage_index])) {
			throw new ExitException(404);
		}
		static::admin_storages_delete_check_usages($storage_index);
		unset($storages[$storage_index]);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	protected static function admin_storages_delete_check_usages ($storage_index) {
		$Config  = Config::instance();
		$used_by = [];
		foreach ($Config->components['modules'] as $module => $module_data) {
			if (isset($module_data['storage']) && is_array($module_data['storage'])) {
				foreach ($module_data['storage'] as $index) {
					if ($index == $storage_index) {
						$used_by[] = $module;
					}
				}
			}
		}
		if ($used_by) {
			throw new ExitException(
				Language::instance()->storage_used_by_modules.': '.implode(', ', $used_by),
				409
			);
		}
	}
	/**
	 * Get array of available storage drivers
	 */
	public static function admin_storages_drivers () {
		return static::admin_storages_get_drivers();
	}
	/**
	 * @return string[]
	 */
	protected static function admin_storages_get_drivers () {
		return _mb_substr(get_files_list(DIR.'/core/drivers/Storage', '/^[^_].*?\.php$/i', 'f'), 0, -4);
	}
	/**
	 * Test storage connection
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_storages_test ($Request) {
		$data    = $Request->data('url', 'host', 'driver', 'user', 'password');
		$drivers = static::admin_storages_get_drivers();
		if (!$data || !in_array($data['driver'], $drivers, true)) {
			throw new ExitException(400);
		}
		$driver_class = "\\cs\\Storage\\$data[driver]";
		/**
		 * @var \cs\Storage\_Abstract $connection
		 */
		$connection = new $driver_class($data['url'], $data['host'], $data['user'], $data['password']);
		if (!$connection->connected()) {
			throw new ExitException(500);
		}
	}
}
