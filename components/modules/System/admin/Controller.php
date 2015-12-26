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
	cs\modules\System\admin\Controller\users,
	cs\modules\System\admin\Controller\users_save,
	cs\modules\System\admin\Controller\layout_elements;

class Controller {
	use
		components,
		general,
		users,
		users_save,
		layout_elements;
	static function index (
		/** @noinspection PhpUnusedParameterInspection */
		$route_ids,
		$route_path
	) {
		$L           = Language::instance();
		$Page        = Page::instance();
		$save_method = "$route_path[0]_$route_path[1]_save";
		if (method_exists(__CLASS__, $save_method)) {
			self::$save_method();
		}
		$Page->title($L->{$route_path[0]});
		$Page->title($L->{$route_path[1]});
	}
}
