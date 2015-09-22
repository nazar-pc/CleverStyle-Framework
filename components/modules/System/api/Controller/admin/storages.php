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
	cs\Config,
	cs\Core,
	cs\ExitException,
	cs\Page,
	cs\Storage;
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
			'user'       => $Core->storage_user,
			'password'   => $Core->storage_password,
			'url'        => $Core->storage_url ?: url_by_source(PUBLIC_STORAGE)
		];
		foreach ($storages as $i => &$storage) {
			$storage['index'] = $i;
		}
		Page::instance()->json(array_values($storages));
	}
	/**
	 * Test storage connection
	 */
	static function admin_storages_test () {
		if (!Storage::instance()->test($_POST)) {
			throw new ExitException(500);
		}
	}
}
