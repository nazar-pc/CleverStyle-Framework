<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin;
use
	cs\Page;

class Controller {
	static function index ($route_ids, $route_path) {
		switch (@$route_path[2]) {
			case 'phpinfo':
				interface_off();
				Page::instance()->Content = ob_wrapper(
					function () {
						phpinfo();
					}
				);
				return;
			case 'readme.html':
				interface_off();
				Page::instance()->Content = file_get_contents(DIR.'/readme.html');
				return;
		}
		$Page = Page::instance();
		$Page->title('%1$s');
		$Page->title('%2$s');
	}
}
