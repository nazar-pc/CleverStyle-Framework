<?php
/**
 * @package  Disqus
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Disqus\api;
use
	cs\Config;

class Controller {
	/**
	 * @return array
	 */
	public static function index_get_settings () {
		return [
			'shortname' => Config::instance()->module('Disqus')->shortname
		];
	}
}
