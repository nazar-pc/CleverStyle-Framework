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
	cs\modules\Static_pages\Categories;

class Controller {
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	public static function admin_categories_get ($Request) {
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
}
