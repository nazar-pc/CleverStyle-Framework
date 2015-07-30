<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config,
	cs\Mail,
	cs\Page,
	cs\Storage;
trait admin {
	static function admin_email_sending_test_get () {
		if (!isset($_GET['email'])) {
			error_code(400);
			return;
		}
		if (!Mail::instance()->send_to($_GET['email'], 'Email testing on '.get_core_ml_text('name'), 'Test email')) {
			error_code(500);
		}
	}
	static function admin_languages_get () {
		Page::instance()->json(
			Config::instance()->core['active_languages']
		);
	}
	static function admin_storages_test_get () {
		$Storage = Storage::instance();
		if (isset($_GET['index'])) {
			$result = $Storage->test([$_GET['index']]);
		} else {
			$result = $Storage->test($_GET['storage']);
		}
		Page::instance()->json(
			(int)$result
		);
	}
	static function admin_timezones_get () {
		Page::instance()->json(
			get_timezones_list()
		);
	}
}
