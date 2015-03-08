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
	cs\Index,
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
	static function index () {
		$L     = Language::instance();
		$Index = Index::instance();
		$Page  = Page::instance();
		if (file_exists(__DIR__."/{$Index->route_path[0]}/save.php")) {
			include __DIR__."/{$Index->route_path[0]}/save.php";
		} else {
			include __DIR__.'/save.php';
		}
		$Page->title($L->{$Index->route_path[0]});
		$Page->title($L->{$Index->route_path[1]});
	}
}
