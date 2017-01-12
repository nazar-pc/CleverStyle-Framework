<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages\admin;
use
	h,
	cs\Language\Prefix,
	cs\Page,
	cs\modules\Static_pages\Categories;

class Controller {
	/**
	 * @return string
	 */
	public static function browse_categories () {
		return h::cs_static_pages_admin_categories_list();
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return string
	 */
	public static function browse_pages ($Request) {
		$L        = new Prefix('static_pages_');
		$category = $Request->route_ids(0);
		Page::instance()->title(
			$category ? Categories::instance()->get($category)['title'] : $L->root_category
		);
		return h::cs_static_pages_admin_pages_list(
			[
				'category' => $category
			]
		);
	}
}
