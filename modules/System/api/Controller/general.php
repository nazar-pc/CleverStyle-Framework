<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config;

trait general {
	/**
	 * @return string[]
	 */
	public static function languages_get () {
		return Config::instance()->core['active_languages'];
	}
	public static function timezones_get () {
		return get_timezones_list();
	}
}
