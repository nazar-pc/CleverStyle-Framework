<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\cli\Controller;
use
	cs\modules\System\api\Controller as Api_controller;
use function
	cli\line;

trait optimization {
	/**
	 * Clean cache
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	static function optimization_clean_cache ($Request) {
		Api_controller::admin_optimization_clean_cache($Request);
		line('%gOK%n');
	}
	/**
	 * Clean public cache (CSS/JS/HTML)
	 *
	 * @throws \cs\ExitException
	 */
	static function optimization_clean_pcache () {
		Api_controller::admin_optimization_clean_pcache();
		line('%gOK%n');
	}
}
