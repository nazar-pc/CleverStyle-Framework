<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Page,
	cs\Route;

$Route      = Route::instance();
$Page       = Page::instance();
$Categories = Categories::instance();
if (isset($_GET['ids'])) {
	$categories = $Categories->get_for_user(explode(',', $_GET['ids']));
	if (!$categories) {
		error_code(404);
	} else {
		$Page->json($categories);
	}
} elseif (isset($Route->ids[0])) {
	$category = $Categories->get_for_user($Route->ids[0]);
	if (!$category) {
		error_code(404);
	} else {
		$Page->json($category);
	}
} else {
	$Page->json(
		$Categories->get_for_user(
			array_filter($Categories->get_all(), function ($category) {
				return $category['visible'];
			})
		)
	);
}
