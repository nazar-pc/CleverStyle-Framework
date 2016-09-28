<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages\api;
use
	cs\ExitException,
	cs\modules\Static_pages\Pages,
	cs\modules\Static_pages\Categories;

class Controller {
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	public static function admin_categories___get ($Request) {
		$id         = $Request->route_ids(0);
		$Categories = Categories::instance();
		if ($id) {
			$data = $Categories->get($id);
			if (!$data) {
				throw new ExitException(404);
			}
			return $data;
		}
		return $Categories->get_all();
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_categories___delete ($Request) {
		if (!Categories::instance()->del($Request->route_ids(0))) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	public static function admin_categories_pages_get ($Request) {
		$category = $Request->route_ids(0);
		if ($category === null) {
			throw new ExitException(400);
		}
		$Pages = Pages::instance();
		return $Pages->get($Pages->get_for_category($category));
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_pages_delete ($Request) {
		if (!Pages::instance()->del($Request->route_ids(0))) {
			throw new ExitException(500);
		}
	}
}
