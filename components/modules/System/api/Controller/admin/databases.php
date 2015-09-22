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
	cs\DB,
	cs\ExitException,
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
			$databases[0],
			[
				'host'    => $Core->db_host,
				'type'    => $Core->db_type,
				'prefix'  => $Core->db_prefix,
				'name'    => $Core->db_name,
				'user'    => '',
				'charset' => $Core->db_charset
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
	 * Test database connection
	 */
	static function admin_databases_test () {
		// TODO explicit arguments when migrated to frontend completely
		if (!DB::instance()->test($_POST)) {
			throw new ExitException(500);
		}
	}
	/**
	 * Test database connection
	 *
	 * @todo drop when migrated to frontend
	 */
	static function admin_databases_test_get () {
		$db = DB::instance();
		if (isset($_GET['mirror_index'])) {
			$result = $db->test([$_GET['index'], $_GET['mirror_index']]);
		} elseif (isset($_GET['index'])) {
			$result = $db->test([$_GET['index']]);
		} else {
			$result = $db->test($_GET['db']);
		}
		Page::instance()->json(
			(int)$result
		);
	}
}
