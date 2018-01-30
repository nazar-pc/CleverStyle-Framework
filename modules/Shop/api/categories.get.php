<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Shop;
use
	cs\ExitException,
	cs\Page,
	cs\Request;

$Request    = Request::instance();
$Page       = Page::instance();
$Categories = Categories::instance();
if (isset($_GET['ids'])) {
	$categories = $Categories->get_for_user(explode(',', $_GET['ids']));
	if (!$categories) {
		throw new ExitException(404);
	} else {
		$Page->json($categories);
	}
} elseif (isset($Request->route_ids[0])) {
	$category = $Categories->get_for_user($Request->route_ids[0]);
	if (!$category) {
		throw new ExitException(404);
	} else {
		$Page->json($category);
	}
} else {
	$Page->json(
		$Categories->get_for_user(
			array_filter(
				$Categories->get_all(),
				function ($category) {
					return $category['visible'];
				}
			)
		)
	);
}
