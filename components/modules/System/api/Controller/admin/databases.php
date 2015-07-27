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
	cs\DB,
	cs\Page;
trait databases {
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
