<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin;
use
	cs\Language,
	cs\Page,
	cs\modules\System\admin\Controller\components,
	cs\modules\System\admin\Controller\general,
	cs\modules\System\admin\Controller\users;

class Controller {
	use
		components,
		general,
		users;
	static function index ($route_ids, $route_path) {
		$L    = Language::instance();
		$Page = Page::instance();
		$Page->title($L->{$route_path[0]});
		$Page->title($L->{$route_path[1]});
	}
}
