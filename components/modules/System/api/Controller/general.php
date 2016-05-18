<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config;

trait general {
	static function languages_get () {
		return Config::instance()->core['active_languages'];
	}
	static function timezones_get () {
		return get_timezones_list();
	}
}
