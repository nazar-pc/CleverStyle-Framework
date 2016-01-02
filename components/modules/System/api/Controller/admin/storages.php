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
trait storages {
	/**
	 * Get array of storages
	 */
	static function admin_storages_get () {
		$Config      = Config::instance();
		$Core        = Core::instance();
		$storages    = $Config->storage;
		$storages[0] = [
			'host'       => $Core->storage_host,
			'connection' => $Core->storage_type,
			'user'       => '',
			'url'        => $Core->storage_url ?: url_by_source(PUBLIC_STORAGE)
		];
		foreach ($storages as $i => &$storage) {
			$storage['index'] = $i;
		}
		Page::instance()->json(array_values($storages));
	}
	/**
	 * Update storage settings
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_storages_patch ($route_ids) {
		if (
			!isset($route_ids[0], $_POST['url'], $_POST['host'], $_POST['connection'], $_POST['user'], $_POST['password']) ||
			!strlen($_POST['host']) ||
			!in_array($_POST['connection'], static::admin_storages_get_engines())
		) {
			throw new ExitException(400);
		}
		$Config        = Config::instance();
		$storages      = &$Config->storage;
		$storage_index = $route_ids[0];
		if (!isset($storages[$storage_index])) {
			throw new ExitException(404);
		}
		if ($storage_index == 0) {
			throw new ExitException(400);
		}
		$storage               = &$storages[$storage_index];
		$storage['url']        = $_POST['url'];
		$storage['host']       = $_POST['host'];
		$storage['connection'] = $_POST['connection'];
		$storage['user']       = $_POST['user'];
		$storage['password']   = $_POST['password'];
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Create storage
	 *
	 * @throws ExitException
	 */
	static function admin_storages_post () {
		if (
			!isset($_POST['url'], $_POST['host'], $_POST['connection'], $_POST['user'], $_POST['password']) ||
			!strlen($_POST['host']) ||
			!in_array($_POST['connection'], static::admin_storages_get_engines())
		) {
			throw new ExitException(400);
		}
		$Config            = Config::instance();
		$Config->storage[] = [
			'url'        => $_POST['url'],
			'host'       => $_POST['host'],
			'connection' => $_POST['connection'],
			'user'       => $_POST['user'],
			'password'   => $_POST['password']
		];
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete storage
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_storages_delete ($route_ids) {
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		$Config        = Config::instance();
		$storages      = &$Config->storage;
		$storage_index = $route_ids[0];
		if (!isset($storages[$storage_index])) {
			throw new ExitException(404);
		}
		if ($storage_index == 0) {
			throw new ExitException(400);
		} else {
			static::admin_storages_delete_check_usages($storage_index);
			unset($storages[$storage_index]);
		}
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
	 * Get array of available storage engines
	 */
	static function admin_storages_engines () {
		Page::instance()->json(
			static::admin_storages_get_engines()
		);
	}
	/**
	 * @return string[]
	 */
	protected static function admin_storages_get_engines () {
		return _mb_substr(get_files_list(ENGINES.'/Storage', '/^[^_].*?\.php$/i', 'f'), 0, -4);
	}
	/**
	 * Test storage connection
	 *
	 * @throws ExitException
	 */
	static function admin_storages_test () {
		$engines = static::admin_storages_get_engines();
		if (
			!isset($_POST['connection'], $_POST['url'], $_POST['host'], $_POST['user'], $_POST['password']) ||
			!in_array($_POST['connection'], $engines, true)
		) {
			throw new ExitException(400);
		}
		$connection_class = "\\cs\\Storage\\$_POST[connection]";
		/**
		 * @var \cs\Storage\_Abstract $connection
		 */
		$connection = new $connection_class(
			$_POST['url'],
			$_POST['host'],
			$_POST['user'],
			$_POST['password']
		);
		if (!$connection->connected()) {
			throw new ExitException(500);
		}
	}
}
