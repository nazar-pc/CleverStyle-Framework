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
	cs\ExitException,
	cs\Mail,
	cs\Page,
	cs\Storage;
trait admin {
	static function admin_email_sending_test_get () {
		if (!isset($_GET['email'])) {
			throw new ExitException(400);
		}
		if (!Mail::instance()->send_to($_GET['email'], 'Email testing on '.get_core_ml_text('name'), 'Test email')) {
			throw new ExitException(500);
		}
	}
	static function admin_languages_get () {
		Page::instance()->json(
			Config::instance()->core['active_languages']
		);
	}
	static function admin_timezones_get () {
		Page::instance()->json(
			get_timezones_list()
		);
	}
}
