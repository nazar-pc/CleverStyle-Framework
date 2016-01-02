<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config,
	cs\Page;
trait general {
	static function languages_get () {
		Page::instance()->json(
			Config::instance()->core['active_languages']
		);
	}
	static function timezones_get () {
		Page::instance()->json(
			get_timezones_list()
		);
	}
}
